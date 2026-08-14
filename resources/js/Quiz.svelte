<script lang="ts">
    import { onMount, onDestroy } from 'svelte';
    import { db, type Question, type Quiz, type Answer } from './db';
    import { submitQuiz } from './api';
    import { parseContentWithCode, highlightCodeSyntax, formatInlineCode } from './CodeHighlighter';

    let { quizId, studentId, isOnline, onComplete, onOfflineSubmit } = $props<{ 
        quizId: number, 
        studentId: string,
        isOnline: boolean,
        onComplete: (score: number, total: number) => void,
        onOfflineSubmit: () => void
    }>();

    let quiz = $state<Quiz | null>(null);
    let questions = $state<Question[]>([]);
    let currentQuestionIndex = $state(0);
    let selectedOption = $state<number | null>(null);
    let remainingTime = $state(0);
    let isSubmitting = $state(false);

    let timerInterval: number;
    let lastTickTime = Date.now();

    onMount(async () => {
        // Load quiz and questions from IndexedDB
        quiz = await db.quizzes.get(quizId) || null;
        questions = await db.questions.where('quizId').equals(quizId).toArray();
        
        if (!quiz || questions.length === 0) {
            console.error("Quiz data not found in local DB.");
            return;
        }

        // Restore state with wall-clock time deduction
        const savedState = await db.quizState.where('quizId').equals(quizId).first();
        if (savedState && savedState.studentId === studentId && savedState.remainingTime > 0) {
            const savedTimestamp = savedState.lastSaved ? new Date(savedState.lastSaved).getTime() : Date.now();
            const elapsedSeconds = Math.max(0, Math.floor((Date.now() - savedTimestamp) / 1000));

            let qIndex = savedState.currentQuestionId;
            let rem = savedState.remainingTime - elapsedSeconds;

            // If time ran out for the question while away, advance across questions
            while (rem <= 0 && qIndex < questions.length - 1) {
                qIndex += 1;
                const nextQDuration = questions[qIndex]?.duration || 60;
                rem += nextQDuration;
            }

            if (rem <= 0 && qIndex >= questions.length - 1) {
                // Entire remaining quiz time expired while offline/away
                currentQuestionIndex = questions.length - 1;
                remainingTime = 0;
                await loadAnswerForCurrentQuestion();
                await forceSubmit();
                return;
            } else {
                currentQuestionIndex = Math.min(qIndex, questions.length - 1);
                remainingTime = Math.max(1, rem);
            }
        } else {
            // New session or stale state reset
            currentQuestionIndex = 0;
            remainingTime = questions[0]?.duration || 60;
        }

        // Load the saved answer for the current question if it exists
        await loadAnswerForCurrentQuestion();
        await saveState();

        // Listen for tab visibility changes (e.g. phone lock / tab switch)
        document.addEventListener('visibilitychange', handleVisibilityChange);

        // Start timer
        lastTickTime = Date.now();
        startTimer();
    });

    onDestroy(() => {
        clearInterval(timerInterval);
        document.removeEventListener('visibilitychange', handleVisibilityChange);
    });

    function handleVisibilityChange() {
        if (document.visibilityState === 'visible') {
            const now = Date.now();
            const elapsed = Math.max(0, Math.floor((now - lastTickTime) / 1000));
            lastTickTime = now;

            if (elapsed > 0) {
                remainingTime = Math.max(0, remainingTime - elapsed);
                if (remainingTime <= 0) {
                    handleTimeExpiry();
                } else {
                    saveState();
                }
            }
        } else {
            lastTickTime = Date.now();
            saveState();
        }
    }

    function startTimer() {
        clearInterval(timerInterval);
        lastTickTime = Date.now();

        timerInterval = window.setInterval(async () => {
            const now = Date.now();
            const deltaSeconds = Math.max(1, Math.floor((now - lastTickTime) / 1000));
            lastTickTime = now;

            if (remainingTime > 0) {
                remainingTime = Math.max(0, remainingTime - deltaSeconds);
                
                // Save state to IndexedDB every tick
                await saveState();

                if (remainingTime <= 0) {
                    await handleTimeExpiry();
                }
            }
        }, 1000);
    }

    async function handleTimeExpiry() {
        if (currentQuestionIndex < questions.length - 1) {
            await nextQuestion();
        } else {
            clearInterval(timerInterval);
            await forceSubmit();
        }
    }

    async function saveState() {
        if (!quiz) return;
        const state = await db.quizState.where('quizId').equals(quizId).first();
        const newState = {
            studentId,
            quizId,
            currentQuestionId: currentQuestionIndex,
            remainingTime,
            lastSaved: new Date().toISOString()
        };
        
        if (state && state.id) {
            await db.quizState.update(state.id, newState);
        } else {
            await db.quizState.add(newState);
        }
    }

    async function loadAnswerForCurrentQuestion() {
        if (questions.length === 0) return;
        const currentQ = questions[currentQuestionIndex];
        const savedAnswer = await db.answers.get(currentQ.id);
        selectedOption = savedAnswer ? savedAnswer.selectedOption : null;
    }

    async function handleOptionSelect(optionNum: number) {
        selectedOption = optionNum;
        const currentQ = questions[currentQuestionIndex];
        
        // Auto-save answer to Dexie
        await db.answers.put({
            questionId: currentQ.id,
            selectedOption: optionNum,
            answeredAt: new Date().toISOString()
        });

        await saveState();
    }

    async function nextQuestion() {
        if (currentQuestionIndex < questions.length - 1) {
            selectedOption = null;
            currentQuestionIndex += 1;
            remainingTime = questions[currentQuestionIndex]?.duration || 60; // Reset timer for the new question
            lastTickTime = Date.now();
            await loadAnswerForCurrentQuestion();
            await saveState();
        }
    }

    async function forceSubmit() {
        if (isSubmitting) return;
        isSubmitting = true;
        clearInterval(timerInterval);
        
        try {
            // 1. Gather all answers from Dexie
            const allSavedAnswers = await db.answers.toArray();
            
            // Format for API
            const payloadAnswers = allSavedAnswers.map(a => ({
                questionId: a.questionId,
                selectedOption: a.selectedOption
            }));

            let submittedOnline = false;

            if (isOnline && navigator.onLine) {
                try {
                    const response = await submitQuiz(quizId, studentId, payloadAnswers);
                    await db.quizState.where('quizId').equals(quizId).delete();
                    await db.answers.clear();
                    onComplete(response.score, response.total);
                    submittedOnline = true;
                } catch (netError: any) {
                    console.warn("Online submission failed due to network error. Queueing offline...", netError);
                }
            }

            if (!submittedOnline) {
                // Offline queue
                await db.pendingSubmissions.add({
                    studentId,
                    quizId,
                    answers: payloadAnswers,
                    createdAt: new Date().toISOString(),
                    synced: 0
                });
                
                // Clean up local state so they can't re-enter
                await db.quizState.where('quizId').equals(quizId).delete();
                
                onOfflineSubmit();
            }
        } catch (error: any) {
            console.error("Submission failed", error);
        } finally {
            isSubmitting = false;
        }
    }

    function formatTime(seconds: number) {
        const m = Math.floor(seconds / 60).toString().padStart(2, '0');
        const s = (seconds % 60).toString().padStart(2, '0');
        return `${m}:${s}`;
    }

    let currentQuestion = $derived(questions[currentQuestionIndex]);
    let progressPercentage = $derived(questions.length > 0 ? ((currentQuestionIndex + 1) / questions.length) * 100 : 0);
</script>

{#if !quiz || questions.length === 0}
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md text-center space-y-5 border border-gray-100">
        <div class="w-14 h-14 mx-auto rounded-full bg-amber-50 text-amber-500 border border-amber-200 flex items-center justify-center text-2xl shadow-inner">
            ⚡
        </div>
        <div class="space-y-1.5">
            <h3 class="text-lg font-bold text-gray-900">Session Expired or Empty</h3>
            <p class="text-xs text-gray-500">No cached questions found for this quiz session.</p>
        </div>
        <button 
            type="button" 
            onclick={async () => {
                try {
                    await db.quizState.clear();
                    await db.answers.clear();
                } catch (e) {}
                window.location.reload();
            }}
            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-500 active:scale-98 text-white font-bold rounded-xl text-sm transition shadow-lg shadow-indigo-600/30 cursor-pointer"
        >
            Enter Room Name
        </button>
    </div>
{:else}
    <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl overflow-hidden flex flex-col h-[80vh] max-h-[800px]">
        <!-- Quiz Header -->
        <div class="bg-indigo-600 border-b border-indigo-700 p-5 sm:px-8 flex justify-between items-center shrink-0 rounded-t-xl text-white">
            <div>
                <h2 class="text-xl font-extrabold">{quiz.title}</h2>
                <p class="text-indigo-200 text-sm mt-1 font-medium tracking-wide uppercase">Question {currentQuestionIndex + 1} of {questions.length}</p>
            </div>
            {#if currentQuestion}
            <div class="flex items-center gap-2 bg-indigo-800/50 px-4 py-2 rounded-lg shadow-inner border border-indigo-500/30">
                <svg class="w-5 h-5 {remainingTime < 60 ? 'text-red-400 animate-pulse' : 'text-indigo-200'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-mono font-bold text-xl tracking-wider {remainingTime < 60 ? 'text-red-400' : 'text-white'}">
                    {formatTime(remainingTime)}
                </span>
            </div>
            {/if}
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-gray-100 h-2 shrink-0">
            <div class="bg-green-500 h-2 transition-all duration-500 ease-out" style="width: {progressPercentage}%"></div>
        </div>

        <!-- Question Body -->
        <div class="p-6 sm:p-10 flex-grow overflow-y-auto bg-gray-50/50">
            <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-200/80 mb-8 space-y-4">
                {#if currentQuestion.text}
                    {#each parseContentWithCode(currentQuestion.text) as block}
                        {#if block.type === 'code'}
                            {@const highlighted = highlightCodeSyntax(block.content, block.language)}
                            <!-- VS Code-Style Dark Code Editor Box -->
                            <div class="rounded-xl overflow-hidden shadow-lg border border-slate-700 bg-[#0d1117] font-mono text-sm my-4">
                                <!-- Code Editor Header -->
                                <div class="bg-[#161b22] border-b border-slate-700/80 px-4 py-2 flex justify-between items-center text-xs text-slate-400 select-none">
                                    <div class="flex items-center gap-2">
                                        <div class="flex gap-1.5">
                                            <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                            <div class="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                                            <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                                        </div>
                                        <span class="ml-2 uppercase font-bold text-indigo-400 tracking-wider text-[11px] bg-indigo-950/60 px-2 py-0.5 rounded border border-indigo-700/40">
                                            {block.language || 'code'}
                                        </span>
                                    </div>
                                </div>
                                <!-- Code Content with Line Numbers -->
                                <div class="p-4 overflow-x-auto flex text-slate-200 text-sm leading-relaxed">
                                    <div class="select-none text-slate-600 text-right pr-4 border-r border-slate-800 font-mono flex flex-col">
                                        {#each Array(highlighted.lineCount) as _, idx}
                                            <span>{idx + 1}</span>
                                        {/each}
                                    </div>
                                    <pre class="pl-4 font-mono overflow-x-auto text-slate-200 whitespace-pre"><code>{@html highlighted.html}</code></pre>
                                </div>
                            </div>
                        {:else}
                            <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 leading-snug">
                                {@html formatInlineCode(block.content)}
                            </h3>
                        {/if}
                    {/each}
                {/if}
            </div>



            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Option 1 -->
                <button 
                    class="w-full text-left p-4 sm:p-5 rounded-xl border-2 transition-all duration-200 flex items-center justify-between {selectedOption === 1 ? 'border-indigo-600 bg-indigo-50/80 shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'}"
                    onclick={() => handleOptionSelect(1)}
                >
                    <span class="text-gray-800 font-medium text-base sm:text-lg pr-4">{@html formatInlineCode(currentQuestion.option1)}</span>
                    <div class="w-5 h-5 rounded-full border-2 shrink-0 flex items-center justify-center transition-colors {selectedOption === 1 ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300'}">
                        {#if selectedOption === 1}
                            <div class="w-2 h-2 rounded-full bg-white"></div>
                        {/if}
                    </div>
                </button>
                
                <!-- Option 2 -->
                <button 
                    class="w-full text-left p-4 sm:p-5 rounded-xl border-2 transition-all duration-200 flex items-center justify-between {selectedOption === 2 ? 'border-indigo-600 bg-indigo-50/80 shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'}"
                    onclick={() => handleOptionSelect(2)}
                >
                    <span class="text-gray-800 font-medium text-base sm:text-lg pr-4">{@html formatInlineCode(currentQuestion.option2)}</span>
                    <div class="w-5 h-5 rounded-full border-2 shrink-0 flex items-center justify-center transition-colors {selectedOption === 2 ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300'}">
                        {#if selectedOption === 2}
                            <div class="w-2 h-2 rounded-full bg-white"></div>
                        {/if}
                    </div>
                </button>

                <!-- Option 3 -->
                <button 
                    class="w-full text-left p-4 sm:p-5 rounded-xl border-2 transition-all duration-200 flex items-center justify-between {selectedOption === 3 ? 'border-indigo-600 bg-indigo-50/80 shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'}"
                    onclick={() => handleOptionSelect(3)}
                >
                    <span class="text-gray-800 font-medium text-base sm:text-lg pr-4">{@html formatInlineCode(currentQuestion.option3)}</span>
                    <div class="w-5 h-5 rounded-full border-2 shrink-0 flex items-center justify-center transition-colors {selectedOption === 3 ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300'}">
                        {#if selectedOption === 3}
                            <div class="w-2 h-2 rounded-full bg-white"></div>
                        {/if}
                    </div>
                </button>

                <!-- Option 4 -->
                <button 
                    class="w-full text-left p-4 sm:p-5 rounded-xl border-2 transition-all duration-200 flex items-center justify-between {selectedOption === 4 ? 'border-indigo-600 bg-indigo-50/80 shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'}"
                    onclick={() => handleOptionSelect(4)}
                >
                    <span class="text-gray-800 font-medium text-base sm:text-lg pr-4">{@html formatInlineCode(currentQuestion.option4)}</span>
                    <div class="w-5 h-5 rounded-full border-2 shrink-0 flex items-center justify-center transition-colors {selectedOption === 4 ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300'}">
                        {#if selectedOption === 4}
                            <div class="w-2 h-2 rounded-full bg-white"></div>
                        {/if}
                    </div>
                </button>
            </div>
        </div>

        <!-- Sticky Bottom Action Bar -->
        {#if !isOnline}
            <div class="bg-amber-50 border-t border-amber-200 px-4 py-2.5 flex items-center justify-between text-xs text-amber-800 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span class="font-semibold">Offline Mode Active:</span>
                    <span>Answers are saved locally and will auto-sync when online.</span>
                </div>
                <span class="font-mono text-[11px] bg-amber-200/60 px-2 py-0.5 rounded font-bold">Local Cache</span>
            </div>
        {/if}

        <div class="bg-white border-t border-gray-200 px-5 py-4 sm:px-8 flex flex-wrap items-center justify-between gap-3 shrink-0 shadow-lg z-10">
            <!-- Answer Status Feedback -->
            <div class="flex items-center gap-2">
                {#if selectedOption}
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-300 rounded-full text-xs font-semibold flex items-center gap-1.5 shadow-sm">
                        <span class="text-emerald-500 font-bold">✓</span> Choice {String.fromCharCode(64 + selectedOption)} Selected
                    </span>
                {:else}
                    <span class="px-3 py-1 bg-slate-100 text-slate-500 border border-slate-300 rounded-full text-xs font-medium flex items-center gap-1">
                        <span>ℹ</span> Tap an option to select your answer
                    </span>
                {/if}
            </div>

            <!-- Action Button: Next or Submit -->
            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                {#if currentQuestionIndex === questions.length - 1}
                    <button 
                        type="button"
                        onclick={forceSubmit}
                        disabled={isSubmitting}
                        class="w-full sm:w-auto px-8 py-3 rounded-xl font-extrabold text-base text-white bg-emerald-600 hover:bg-emerald-500 active:scale-98 shadow-lg shadow-emerald-700/30 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer border border-emerald-500"
                    >
                        {#if isSubmitting}
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Submitting Exam...</span>
                        {:else}
                            <span>🚀 Submit Exam</span>
                        {/if}
                    </button>
                {:else}
                    <button 
                        type="button"
                        onclick={nextQuestion}
                        class="w-full sm:w-auto px-7 py-3 rounded-xl font-bold text-base text-white bg-indigo-600 hover:bg-indigo-500 active:scale-98 shadow-lg shadow-indigo-700/30 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer border border-indigo-500"
                    >
                        <span>Next Question</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                {/if}
            </div>
        </div>
    </div>
{/if}
