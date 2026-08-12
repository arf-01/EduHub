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

    onMount(async () => {
        // Load quiz and questions from IndexedDB
        quiz = await db.quizzes.get(quizId) || null;
        questions = await db.questions.where('quizId').equals(quizId).toArray();
        
        if (!quiz || questions.length === 0) {
            console.error("Quiz data not found in local DB.");
            return;
        }

        // Check if there is an existing state for crash recovery
        const savedState = await db.quizState.where('quizId').equals(quizId).first();
        if (savedState && savedState.studentId === studentId && savedState.remainingTime > 0) {
            currentQuestionIndex = savedState.currentQuestionId;
            remainingTime = savedState.remainingTime;
        } else {
            // New session or stale state reset
            currentQuestionIndex = 0;
            remainingTime = questions[0]?.duration || 60;
        }

        // Load the saved answer for the current question if it exists
        await loadAnswerForCurrentQuestion();

        // Start timer
        startTimer();
    });

    onDestroy(() => {
        clearInterval(timerInterval);
    });

    function startTimer() {
        timerInterval = window.setInterval(async () => {
            if (remainingTime > 0) {
                remainingTime -= 1;
                
                // Auto-save state every 5 seconds
                if (remainingTime % 5 === 0) {
                    await saveState();
                }
            } else {
                // Time's up for this question, auto-advance or submit
                if (currentQuestionIndex < questions.length - 1) {
                    await nextQuestion();
                } else {
                    clearInterval(timerInterval);
                    await forceSubmit();
                }
            }
        }, 1000);
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
    }

    async function nextQuestion() {
        if (currentQuestionIndex < questions.length - 1) {
            selectedOption = null;
            currentQuestionIndex += 1;
            remainingTime = questions[currentQuestionIndex]?.duration || 60; // Reset timer for the new question
            await loadAnswerForCurrentQuestion();
            await saveState();
        }
    }


    async function forceSubmit() {
        if (isSubmitting) return;
        isSubmitting = true;
        
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
    <div class="text-center py-10">
        <p class="text-gray-500">Preparing your quiz...</p>
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

            {#if currentQuestion.image}
                <div class="mb-8 rounded-xl overflow-hidden border-2 border-gray-200 bg-white shadow-sm p-4 text-center">
                    <img src={currentQuestion.imageData || `/storage/${currentQuestion.image}`} alt="Question figure" class="max-w-full h-auto object-contain max-h-80 mx-auto rounded-lg shadow-sm" />
                </div>
            {/if}

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

        <!-- Footer Actions -->
        {#if !isOnline}
            <div class="bg-yellow-50 border-b border-yellow-200 p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-sm font-medium text-yellow-800">You are offline. Quiz cannot be submitted until your connection is restored.</p>
            </div>
        {/if}
        <div class="bg-gray-50 border-t border-gray-200 p-4 sm:px-6 flex justify-between shrink-0">
            <div></div> <!-- Empty div to maintain flex spacing since Previous button is gone -->

            {#if currentQuestionIndex === questions.length - 1}
                <button 
                    onclick={forceSubmit}
                    disabled={isSubmitting}
                    class="px-6 py-2.5 rounded-lg font-bold text-white bg-green-600 hover:bg-green-700 shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors disabled:opacity-70 flex items-center"
                >
                    {#if isSubmitting}
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Submitting...
                    {:else}
                        Submit Quiz
                    {/if}
                </button>
            {:else}
                <button 
                    onclick={nextQuestion}
                    class="px-5 py-2.5 rounded-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
                >
                    Next Question
                </button>
            {/if}
        </div>
    </div>
{/if}
