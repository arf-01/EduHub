<script lang="ts">
    import { onMount } from 'svelte';
    import Login from './Login.svelte';
    import RoomLobby from './RoomLobby.svelte';
    import Quiz from './Quiz.svelte';
    import { db } from './db';
    import { submitQuiz, startQuiz } from './api';

    let loading = $state(true);
    let initError = $state('');
    let isOnline = $state(navigator.onLine);
    
    // State management
    let currentRoomName = $state<string | null>(null);
    let studentId = $state<string | null>(null);
    let activeQuizId = $state<number | null>(null);
    let quizScore = $state<number | null>(null);
    let quizTotal = $state<number | null>(null);
    let hasPendingSubmission = $state(false);

    function shuffleArray<T>(array: T[]): T[] {
        const arr = [...array];
        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }
        return arr;
    }

    onMount(async () => {
        try {
            // Setup network listeners
            window.addEventListener('online', handleOnline);
            window.addEventListener('offline', handleOffline);

            // Check if there is an unsynced submission
            const pending = await db.pendingSubmissions.where('synced').equals(0).toArray();
            if (pending.length > 0) {
                hasPendingSubmission = true;
                activeQuizId = pending[0].quizId;
                studentId = pending[0].studentId;
            } else {
                // Check for active crash recovery session
                const savedState = await db.quizState.toArray();
                if (savedState.length > 0) {
                    const state = savedState[0];
                    const qCount = await db.questions.where('quizId').equals(state.quizId).count();
                    if (qCount > 0) {
                        activeQuizId = state.quizId;
                        studentId = state.studentId;
                    } else {
                        await db.quizState.clear();
                    }
                }
            }

            // Initial sync attempt
            await syncPendingSubmissions();
        } catch (err: any) {
            console.error("Initialization error:", err);
            initError = err.message || "Failed to initialize database.";
        } finally {
            loading = false;
        }
    });

    async function handleOnline() {
        isOnline = true;
        await syncPendingSubmissions();
    }

    function handleOffline() {
        isOnline = false;
    }

    async function syncPendingSubmissions() {
        const pending = await db.pendingSubmissions.where('synced').equals(0).toArray();
        for (const submission of pending) {
            try {
                const response = await submitQuiz(submission.quizId, submission.studentId, submission.answers);
                
                await db.pendingSubmissions.update(submission.id!, { synced: 1 });
                await db.answers.clear();
                await db.questions.where('quizId').equals(submission.quizId).delete();
                
                isOnline = true;
                
                if (submission.quizId === activeQuizId && submission.studentId === studentId) {
                    quizScore = response.score;
                    quizTotal = response.total;
                    hasPendingSubmission = false;
                }
            } catch (error: any) {
                isOnline = false;
                console.warn('Sync waiting for connection.');
            }
        }
    }

    function handleRoomJoin(roomName: string, sId: string) {
        currentRoomName = roomName;
        studentId = sId;
        activeQuizId = null;
        quizScore = null;
        quizTotal = null;
        hasPendingSubmission = false;
    }

    async function handleStartQuiz(quizId: number) {
        if (!studentId) return;

        const data = await startQuiz(quizId, studentId);
        const shuffledQuestions = shuffleArray(data.questions || []);

        const questionsToInsert = shuffledQuestions.map((q: any) => ({
            id: q.id,
            quizId: data.quiz.id,
            text: q.text,
            image: null,
            imageData: null,
            option1: q.option1,
            option2: q.option2,
            option3: q.option3,
            option4: q.option4,
            duration: Number(q.duration) || 60
        }));

        await db.transaction('rw', db.quizzes, db.questions, async () => {
            await db.quizzes.put({
                id: data.quiz.id,
                title: data.quiz.title,
                duration: data.quiz.duration,
                start_datetime: data.quiz.start_datetime
            });

            await db.questions.where('quizId').equals(data.quiz.id).delete();
            await db.questions.bulkAdd(questionsToInsert);
        });

        activeQuizId = quizId;
        quizScore = null;
        quizTotal = null;
        hasPendingSubmission = false;
    }

    async function handleLeaveRoom() {
        try {
            await db.quizState.clear();
            await db.answers.clear();
            if (activeQuizId) {
                await db.questions.where('quizId').equals(activeQuizId).delete();
            }
        } catch (e) {
            console.warn('IndexedDB cleanup failed:', e);
        }
        currentRoomName = null;
        studentId = null;
        activeQuizId = null;
        quizScore = null;
        quizTotal = null;
        hasPendingSubmission = false;
    }

    function handleOfflineSubmit() {
        hasPendingSubmission = true;
    }

    function handleComplete(score: number, total: number) {
        quizScore = score;
        quizTotal = total;
    }

    async function resetApp() {
        try {
            await db.quizState.clear();
            await db.answers.clear();
        } catch (e) {
            console.error("Error clearing local database state:", e);
        }
        currentRoomName = null;
        activeQuizId = null;
        studentId = null;
        quizScore = null;
        quizTotal = null;
        hasPendingSubmission = false;
    }
</script>

<main class="min-h-screen flex flex-col font-sans relative overflow-hidden" style="background:var(--edu-bg, #080b14); color:var(--edu-text, #e2e8f0);">

    <!-- Background glowing blobs -->
    <div class="edu-blob edu-animate-blob w-[500px] h-[500px] -top-40 -left-40 bg-indigo-700 pointer-events-none" style="opacity:.15;"></div>
    <div class="edu-blob edu-animate-blob w-[400px] h-[400px] -bottom-32 -right-32 bg-violet-600 pointer-events-none" style="opacity:.12; animation-delay:-3s;"></div>

    <!-- EduHub Navigation Header -->
    <header class="edu-nav sticky top-0 z-50 shrink-0">
        <div class="max-w-7xl mx-auto px-5 sm:px-8 py-3.5 flex items-center justify-between gap-4">
            
            <!-- Logo / Home Link -->
            <button 
                type="button" 
                onclick={resetApp} 
                class="flex items-center gap-2.5 group cursor-pointer"
            >
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition-transform">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="text-lg font-extrabold tracking-tight" style="background: linear-gradient(135deg, #a5b4fc 0%, #818cf8 50%, #c4b5fd 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                    EduHub
                </span>
                <span class="text-xs px-2 py-0.5 rounded-full font-mono font-bold bg-indigo-950/80 text-indigo-300 border border-indigo-700/40 hidden sm:inline-block">
                    Student Portal
                </span>
            </button>

            <!-- Navigation Actions & Connectivity Badge -->
            <div class="flex items-center gap-3">
                <a href="/" class="text-xs font-semibold text-slate-400 hover:text-slate-200 transition hidden sm:inline-flex items-center gap-1">
                    <span>🏠</span> Home
                </a>

                <span class="w-px h-4 bg-slate-800 hidden sm:block"></span>

                {#if isOnline}
                    <span class="edu-badge bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block edu-animate-live"></span>
                        Online
                    </span>
                {:else}
                    <span class="edu-badge bg-amber-500/15 text-amber-300 border border-amber-500/30">
                        <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Offline (Local Cache)
                    </span>
                {/if}
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <div class="flex-grow p-4 md:p-8 flex items-center justify-center relative z-10">
        {#if loading}
            <div class="edu-card p-10 flex flex-col items-center max-w-sm text-center">
                <div class="h-10 w-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p class="text-white font-bold">Initializing Portal...</p>
                <p class="text-xs mt-1 text-slate-400">Loading offline exam engine</p>
            </div>
        {:else if initError}
            <div class="edu-card p-8 sm:p-10 w-full max-w-md text-center shadow-2xl space-y-4">
                <div class="w-16 h-16 bg-rose-500/15 text-rose-400 rounded-2xl border border-rose-500/30 flex items-center justify-center mx-auto text-2xl">
                    ⚠️
                </div>
                <h2 class="text-xl font-bold text-white">Initialization Failed</h2>
                <p class="text-rose-400 text-xs">{initError}</p>
                <button type="button" onclick={() => window.location.reload()} class="edu-btn-primary w-full text-sm">Retry</button>
            </div>
        {:else if hasPendingSubmission}
            <!-- Pending Sync View -->
            <div class="edu-card p-8 sm:p-10 w-full max-w-md text-center shadow-2xl space-y-5 edu-animate-scale-in">
                <div class="w-16 h-16 bg-amber-500/15 text-amber-400 rounded-2xl border border-amber-500/30 flex items-center justify-center mx-auto text-2xl edu-animate-float">
                    💾
                </div>
                <div class="space-y-1.5">
                    <h2 class="text-2xl font-extrabold text-white">Exam Saved Offline</h2>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Your answers are encrypted and saved locally. They will automatically upload and grade as soon as network is detected.
                    </p>
                </div>
                
                {#if isOnline}
                    <div class="bg-indigo-950/60 border border-indigo-700/50 text-indigo-200 rounded-xl p-4 text-xs font-semibold flex flex-col items-center justify-center gap-3">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></div>
                            Syncing answers with server...
                        </div>
                        <button type="button" onclick={syncPendingSubmissions} class="edu-btn-primary text-xs py-1.5 px-4 shadow-none">Force Sync Now</button>
                    </div>
                {:else}
                    <div class="bg-amber-950/40 border border-amber-800/50 text-amber-300 rounded-xl p-3.5 text-xs font-semibold flex items-center justify-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></div>
                        Waiting for active connection...
                    </div>
                {/if}
            </div>
        {:else if quizScore !== null}
            <!-- Results View -->
            <div class="edu-card p-8 sm:p-12 w-full max-w-lg text-center shadow-2xl shadow-black/60 edu-animate-scale-in space-y-6">
                <!-- Celebration Icon -->
                <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-br from-amber-400/20 to-yellow-500/20 border border-amber-400/30 flex items-center justify-center text-4xl edu-animate-float shadow-lg shadow-amber-500/15">
                    🏆
                </div>

                <div class="space-y-1">
                    <h2 class="text-3xl font-extrabold text-white">Exam Complete!</h2>
                    <p class="text-sm" style="color:var(--edu-text2);">Your submission has been evaluated successfully.</p>
                </div>
                
                <!-- Score Callout Card -->
                <div class="inline-flex flex-col items-center px-10 py-5 rounded-2xl border w-full" style="background:var(--edu-card2); border-color:var(--edu-border2);">
                    <p class="text-xs font-bold uppercase tracking-widest mb-1 text-slate-400">Total Score</p>
                    <div class="text-5xl font-black text-white font-mono">
                        {quizScore} <span class="text-2xl text-slate-500 font-normal">/ {quizTotal}</span>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="space-y-3 pt-2">
                    <!-- Detailed Analysis Button (when online or studentId present) -->
                    {#if studentId}
                        <a 
                            href={activeQuizId ? `/quiz/${activeQuizId}/analysis/${studentId}` : `/student/results/${studentId}`}
                            class="edu-btn-primary w-full py-3 text-sm font-bold shadow-lg shadow-indigo-600/30 justify-center"
                        >
                            📊 View Detailed Quiz Analysis
                        </a>
                    {/if}

                    {#if currentRoomName && studentId}
                        <button 
                            type="button"
                            onclick={() => { quizScore = null; quizTotal = null; activeQuizId = null; }}
                            class="edu-btn-ghost w-full py-2.5 text-xs font-semibold justify-center"
                        >
                            ← Back to Room Lobby
                        </button>
                    {:else}
                        <button 
                            type="button"
                            onclick={resetApp}
                            class="edu-btn-ghost w-full py-2.5 text-xs font-semibold justify-center"
                        >
                            Return to Student Home
                        </button>
                    {/if}
                </div>
            </div>
        {:else if activeQuizId && studentId}
            <!-- Quiz Taking View -->
            <Quiz quizId={activeQuizId} studentId={studentId} isOnline={isOnline} onComplete={handleComplete} onOfflineSubmit={handleOfflineSubmit} />
        {:else if currentRoomName && studentId}
            <!-- Room Lobby View -->
            <RoomLobby 
                roomName={currentRoomName} 
                studentId={studentId} 
                isOnline={isOnline} 
                onStartQuiz={handleStartQuiz} 
                onLeaveRoom={handleLeaveRoom} 
            />
        {:else}
            <!-- Login View -->
            <Login onJoin={handleRoomJoin} />
        {/if}
    </div>
</main>
