<script lang="ts">
    import { onMount } from 'svelte';
    import Login from './Login.svelte';
    import Quiz from './Quiz.svelte';
    import { db } from './db';
    import { submitQuiz } from './api';

    let loading = $state(true);
    let initError = $state('');
    let isOnline = $state(navigator.onLine);
    
    // State management
    let activeQuizId = $state<number | null>(null);
    let studentId = $state<string | null>(null);
    let quizScore = $state<number | null>(null);
    let quizTotal = $state<number | null>(null);
    let hasPendingSubmission = $state(false);

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
                        // Stale orphan state without questions; clean it up
                        await db.quizState.clear();
                    }
                }
            }

            // Initial sync attempt
            await syncPendingSubmissions();
        } catch (err: any) {
            console.error("Initialization error:", err);
            initError = err.message || "Failed to initialize Dexie database.";
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
                // Mark as synced
                await db.pendingSubmissions.update(submission.id!, { synced: 1 });
                
                // Success! Set online status
                isOnline = true;
                
                // If this is the active user's pending submission, show them the score
                if (submission.quizId === activeQuizId && submission.studentId === studentId) {
                    quizScore = response.score;
                    quizTotal = response.total;
                    hasPendingSubmission = false;
                }
            } catch (error: any) {
                isOnline = false; // Force UI badge to Offline whenever network fails
                console.warn('Sync waiting for stable connection.');
            }
        }
    }

    function handleJoin(quizId: number, sId: string) {
        activeQuizId = quizId;
        studentId = sId;
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
        activeQuizId = null;
        studentId = null;
        quizScore = null;
        quizTotal = null;
        hasPendingSubmission = false;
    }
</script>

<main class="min-h-screen bg-gray-50 flex flex-col font-sans">
    <header class="bg-indigo-600 text-white p-4 shadow-md flex justify-between items-center shrink-0">
        <div class="flex items-center gap-2 cursor-pointer" onclick={resetApp}>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            <h1 class="text-xl font-bold tracking-tight">Quiz Portal</h1>
        </div>
        
        <div class="flex items-center gap-2">
            {#if isOnline}
                <span class="px-3 py-1 bg-green-500/20 text-green-100 border border-green-500/50 rounded-full text-xs font-semibold flex items-center gap-1.5 shadow-sm">
                    <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                    Online
                </span>
            {:else}
                <span class="px-3 py-1 bg-yellow-500/20 text-yellow-100 border border-yellow-500/50 rounded-full text-xs font-semibold flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Offline
                </span>
            {/if}
        </div>
    </header>

    <div class="flex-grow p-4 md:p-8 flex items-center justify-center">
        {#if loading}
            <div class="animate-pulse flex flex-col items-center">
                <div class="h-10 w-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mb-4 shadow-md"></div>
                <p class="text-indigo-900 font-medium">Initializing application...</p>
            </div>
        {:else if initError}
            <div class="bg-white rounded-xl shadow-xl p-10 w-full max-w-md text-center transform transition-all border border-red-200">
                <div class="w-20 h-20 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Initialization Failed</h2>
                <p class="text-red-600 font-medium">{initError}</p>
                <button onclick={() => window.location.reload()} class="mt-6 px-4 py-2 bg-indigo-600 text-white rounded-md">Retry</button>
            </div>
        {:else if hasPendingSubmission}
            <!-- Pending Sync View -->
            <div class="bg-white rounded-xl shadow-xl p-10 w-full max-w-md text-center transform transition-all border border-yellow-100">
                <div class="w-20 h-20 bg-yellow-100 text-yellow-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <svg class="w-10 h-10 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3 3L22 4"></path></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Quiz Saved Offline</h2>
                <p class="text-gray-600 mb-6 font-medium">Your answers are saved locally on this device. They will upload and grade automatically as soon as your internet connection is restored.</p>
                
                {#if isOnline}
                    <div class="bg-indigo-50 border border-indigo-200 text-indigo-800 rounded-lg p-4 text-sm font-semibold flex flex-col items-center justify-center gap-3">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                            Syncing with server...
                        </div>
                        <button onclick={syncPendingSubmissions} class="px-4 py-1.5 bg-indigo-600 text-white rounded-md text-xs hover:bg-indigo-700 transition-colors">Force Sync</button>
                    </div>
                {:else}
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4 text-sm font-semibold flex items-center justify-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-yellow-500 animate-pulse"></div>
                        Waiting for connection...
                    </div>
                {/if}
            </div>
        {:else if quizScore !== null}
            <!-- Results View -->
            <div class="bg-white rounded-xl shadow-xl p-10 w-full max-w-md text-center transform transition-all">
                <div class="w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Quiz Complete!</h2>
                <p class="text-gray-500 mb-8 text-lg">Your submission has been graded.</p>
                
                <div class="bg-indigo-50 rounded-lg p-6 mb-8 border border-indigo-100">
                    <p class="text-sm text-indigo-800 font-semibold uppercase tracking-wider mb-1">Your Score</p>
                    <div class="text-5xl font-black text-indigo-600">
                        {quizScore} <span class="text-2xl text-indigo-400">/ {quizTotal}</span>
                    </div>
                </div>
                
                <button 
                    onclick={resetApp}
                    class="w-full py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Return to Home
                </button>
            </div>
        {:else if activeQuizId && studentId}
            <!-- Quiz Taking View -->
            <Quiz quizId={activeQuizId} studentId={studentId} isOnline={isOnline} onComplete={handleComplete} onOfflineSubmit={handleOfflineSubmit} />
        {:else}
            <!-- Login View -->
            <Login onJoin={handleJoin} />
        {/if}
    </div>
</main>
