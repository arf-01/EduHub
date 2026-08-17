<script lang="ts">
    import { db } from './db';
    import { joinQuiz } from './api';
    
    let { onJoin } = $props<{ onJoin: (roomName: string, studentId: string) => void }>();

    let roomName = $state('');
    let studentId = $state('');
    let loading = $state(false);
    let error = $state('');

    async function handleJoin(e: Event) {
        e.preventDefault();
        loading = true;
        error = '';

        try {
            // Check network status
            if (!navigator.onLine) {
                error = 'You must be online to join a room for the first time.';
                loading = false;
                return;
            }

            const trimmedRoom = roomName.trim();
            const trimmedStudent = studentId.trim();

            if (!trimmedRoom || !trimmedStudent) {
                error = 'Please provide both Room Name and Student ID.';
                loading = false;
                return;
            }

            // Verify room exists on server
            const data = await joinQuiz(trimmedRoom, trimmedStudent);
            
            // Trigger transition to Lobby in App.svelte
            onJoin(data.room_name, data.student_id);
            
        } catch (err: any) {
            error = err.message || 'An error occurred. Please check the room name and try again.';
        } finally {
            loading = false;
        }
    }
</script>

<div class="relative w-full max-w-md edu-animate-scale-in">
    <div class="edu-card p-8 sm:p-10 shadow-2xl shadow-black/50 text-center">
        <!-- Logo / Icon -->
        <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 mb-5 edu-animate-float">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>

        <div class="mb-6">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Join Quiz Room</h2>
            <p class="text-sm mt-1" style="color:var(--edu-text2);">Enter your details to access your exams</p>
        </div>

        {#if error}
            <div class="mb-5 px-4 py-3 rounded-xl border border-rose-500/30 bg-rose-500/10 text-rose-300 text-xs sm:text-sm font-medium flex items-center gap-2 text-left">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{error}</span>
            </div>
        {/if}

        <form onsubmit={handleJoin} class="space-y-5 text-left">
            <div>
                <label for="roomName" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--edu-text2);">
                    Room Name
                </label>
                <input 
                    type="text" 
                    id="roomName" 
                    bind:value={roomName}
                    required
                    class="edu-input" 
                    placeholder="e.g. CS101-FINAL"
                />
            </div>

            <div>
                <label for="studentId" class="block text-xs font-semibold uppercase tracking-wider mb-2" style="color:var(--edu-text2);">
                    Student ID (Roll Number)
                </label>
                <input 
                    type="text" 
                    id="studentId" 
                    bind:value={studentId}
                    required
                    class="edu-input" 
                    placeholder="e.g. 2107051"
                />
            </div>

            <button 
                type="submit" 
                disabled={loading}
                class="edu-btn-primary w-full text-base py-3 mt-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {#if loading}
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Joining Room...</span>
                {:else}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    <span>Enter Room</span>
                {/if}
            </button>
        </form>

        <p class="mt-6 text-xs" style="color:var(--edu-muted);">
            Offline-resilient exam portal · Automatic sync upon reconnection
        </p>
    </div>
</div>
