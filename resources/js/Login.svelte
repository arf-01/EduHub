<script lang="ts">
    import { db } from './db';
    import { joinQuiz } from './api';
    
    let { onJoin } = $props<{ onJoin: (quizId: number, studentId: string) => void }>();

    let roomName = $state('');
    let studentId = $state('');
    let loading = $state(false);
    let error = $state('');

    function shuffleArray<T>(array: T[]): T[] {
        const arr = [...array];
        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }
        return arr;
    }

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

            // Fetch quiz data from API
            const data = await joinQuiz(roomName, studentId);

            // Shuffle questions to prevent side-by-side cheating
            const shuffledQuestions = shuffleArray(data.questions);
            
            // Pre-fetch images and convert them to Base64 in parallel for absolute offline compatibility
            const questionsToInsert = await Promise.all(
                shuffledQuestions.map(async (q: any) => {
                    let base64Data: string | null = null;
                    if (q.image) {
                        try {
                            const res = await fetch(`/storage/${q.image}`);
                            if (res.ok) {
                                const blob = await res.blob();
                                base64Data = await new Promise<string>((resolve, reject) => {
                                    const reader = new FileReader();
                                    reader.onloadend = () => resolve(reader.result as string);
                                    reader.onerror = reject;
                                    reader.readAsDataURL(blob);
                                });
                            }
                        } catch (imgErr) {
                            console.error(`Failed to pre-cache image /storage/${q.image}:`, imgErr);
                        }
                    }
                    return {
                        id: q.id,
                        quizId: data.quiz.id,
                        text: q.text,
                        image: q.image,
                        imageData: base64Data,
                        option1: q.option1,
                        option2: q.option2,
                        option3: q.option3,
                        option4: q.option4
                    };
                })
            );
            
            // Store data in Dexie
            await db.transaction('rw', db.quizzes, db.questions, async () => {
                // Clear old quiz if necessary, or just put new one
                await db.quizzes.put({
                    id: data.quiz.id,
                    title: data.quiz.title,
                    duration: data.quiz.duration,
                    start_datetime: data.quiz.start_datetime
                });

                // Clear old questions for this quiz and add new ones
                await db.questions.where('quizId').equals(data.quiz.id).delete();
                await db.questions.bulkAdd(questionsToInsert);
            });

            // Trigger the callback to switch view in App.svelte
            onJoin(data.quiz.id, data.student_id);
            
        } catch (err: any) {
            error = err.message || 'An error occurred. Please check the room name and try again.';
        } finally {
            loading = false;
        }
    }
</script>

<div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md text-center">
    <div class="mb-6">
        <h2 class="text-3xl font-extrabold text-gray-900">Quiz Access</h2>
        <p class="text-gray-500 mt-2">Enter the room name provided by your teacher.</p>
    </div>

    {#if error}
        <div class="bg-red-50 text-red-700 p-3 rounded-md text-sm mb-4 border border-red-200">
            {error}
        </div>
    {/if}

    <form onsubmit={handleJoin} class="space-y-4 text-left">
        <div>
            <label for="roomName" class="block text-sm font-medium text-gray-700">Room Name</label>
            <input 
                type="text" 
                id="roomName" 
                bind:value={roomName}
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                placeholder="e.g. Science101"
            />
        </div>

        <div>
            <label for="studentId" class="block text-sm font-medium text-gray-700">Student ID (Roll Number)</label>
            <input 
                type="text" 
                id="studentId" 
                bind:value={studentId}
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                placeholder="e.g. 2107051"
            />
        </div>

        <button 
            type="submit" 
            disabled={loading}
            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
            {#if loading}
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Joining...
            {:else}
                Join Room
            {/if}
        </button>
    </form>
</div>
