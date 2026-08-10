<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $quiz->title }} - Quiz</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    <style>
        body { background: linear-gradient(to bottom right, #0f0f1a, #1a1a2e); color: #e0e0e0; font-family: 'Segoe UI', sans-serif; }
        .btn { background: linear-gradient(to right, #6e57e0, #8f57ea); color: white; font-weight: 600; padding: 0.75rem 1.5rem; border-radius: 0.5rem; transition: all 0.3s ease; }
        .btn:hover { background: linear-gradient(to right, #7c64f1, #9d67f4); transform: scale(1.05); }
        .hide { display: none; }
        .quiz-container { max-width: 700px; margin: auto; }
        .options button { display: block; width: 100%; margin-top: 0.75rem; padding: 0.75rem; border-radius: 0.5rem; background-color: #27293d; color: #d1d5db; transition: all 0.3s ease; }
        .options button:hover { background-color: #3b3f5c; color: #fff; }
        #feedback { text-align: center; font-size: 1.125rem; margin-top: 1rem; }
        .timer { position: fixed; top: 1rem; right: 1rem; background: #4f46e5; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600; z-index: 50; }
        .spinner {
    border: 4px solid rgba(255, 255, 255, 0.2);
    border-top: 4px solid #4f46e5; /* Indigo color */
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 20px auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

    </style>
</head>
<body class="px-4 py-10">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-extrabold text-white mb-6">{{ $quiz->title }}</h1>
        <p class="text-gray-400 mt-2">Try to answer all the questions within the time limit</p>
    </div>

    <main class="quiz-container space-y-10">
        <div id="quiz-start">
            <div id="start-screen" class="text-center space-y-4">
                <h2 class="text-3xl font-bold text-white">Ready to begin?</h2>
                <button id="start" class="btn">Start Quiz</button>
            </div>
        </div>

        <div id="questions" class="hide space-y-6">
            <h2 id="question-words" class="text-2xl font-semibold text-indigo-300 mb-4"></h2>
            <div id="options" class="options"></div>
        </div>

        <div id="quiz-end" class="hide text-center space-y-4">
            <h2 class="text-3xl font-bold text-green-400">All Done!</h2>
            <form id="quiz-form" action="{{ route('result.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
                <input type="hidden" name="student_id" value="{{ session('student_id') }}">
                <div id="answers-container" class="space-y-2"></div>
                <button type="submit" class="btn">Submit</button>
            </form>
        </div>

        <div id="feedback" class="hide"></div>
    </main>

    <div class="timer">Time Left: <span id="timer">0</span></div>

    <script>
        const questions = @json($questions);
        const startBtn = document.getElementById('start');
        const form = document.getElementById('quiz-form');
        const questionsEl = document.getElementById('questions');
        const optionsEl = document.getElementById('options');
        const questionWordsEl = document.getElementById('question-words');
        const answersContainer = document.getElementById('answers-container');
        const quizEndEl = document.getElementById('quiz-end');
        const feedbackEl = document.getElementById('feedback');
        const timerEl = document.getElementById('timer');

        let quizStarted = false;
        let currentQuestionIndex = 0;
        let timerId;
        let selectedAnswers = [];

        // --- Shuffle ---
        function shuffle(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
        }

        // --- Timer ---
        function startTimer(duration) {
            clearInterval(timerId);
            let timeLeft = isNaN(duration) ? 30 : duration;
            timerEl.textContent = timeLeft;
            timerId = setInterval(() => {
                timeLeft--;
                timerEl.textContent = timeLeft;
                if (timeLeft <= 0) {
                    clearInterval(timerId);
                    showFeedback("Time's up!", "red");
                    questionClick(0);
                }
            }, 1000);
        }

        function showFeedback(msg, color = "white") {
            feedbackEl.textContent = msg;
            feedbackEl.style.color = color;
            feedbackEl.classList.remove("hide");
            setTimeout(() => feedbackEl.classList.add("hide"), 1500);
        }

        // --- Display Question ---
        function getQuestion() {
    const question = questions[currentQuestionIndex];
    questionWordsEl.innerHTML = "";
    optionsEl.innerHTML = "";

    if (!question) return;

    // Helper function to render options
    function renderOptions() {
        for (let i = 1; i <= 4; i++) {
            const btn = document.createElement("button");
            btn.textContent = `${i}. ${question["option"+i]}`;
            btn.className = "btn m-2";
            btn.onclick = () => questionClick(i);
            optionsEl.appendChild(btn);
        }
        startTimer(parseInt(question.duration));
    }

    
    if (question.image) {
       
        const spinner = document.createElement("div");
        spinner.className = "spinner";
        questionWordsEl.appendChild(spinner);

        const img = document.createElement("img");
        img.src = question.image;
        img.alt = "Question Image";
        img.style.maxWidth = "400px";
        img.style.marginBottom = "10px";
        img.style.display = "none"; 

        img.onload = () => {
           
            spinner.remove();
            img.style.display = "block";
            questionWordsEl.appendChild(img);
            renderOptions();
        };

        img.onerror = () => {
            spinner.remove();
            questionWordsEl.innerHTML = "<p class='text-red-400'>Failed to load image.</p>";
            if (question.text) {
                const p = document.createElement("p");
                p.textContent = question.text;
                questionWordsEl.appendChild(p);
            }
            renderOptions();
        };
    } else {
       
        if (question.text) {
            const p = document.createElement("p");
            p.textContent = question.text;
            questionWordsEl.appendChild(p);
        }
        renderOptions();
    }
}

        function questionClick(selectedOption) {
            Array.from(optionsEl.children).forEach(btn => btn.disabled = true);
            const question = questions[currentQuestionIndex];

            selectedAnswers.push({
                question_id: question.id,
                selected_option: selectedOption
            });

            currentQuestionIndex++;
            if (currentQuestionIndex < questions.length) {
                getQuestion();
            } else {
                endQuiz();
            }
        }

        function endQuiz() {
            clearInterval(timerId);
            questionsEl.classList.add("hide");
            quizEndEl.classList.remove("hide");
            //quizStarted = false;
            window.onbeforeunload = null;

            selectedAnswers.forEach((ans, index) => {
                answersContainer.innerHTML += `
                    <input type="hidden" name="answers[${index}][question_id]" value="${ans.question_id}" />
                    <input type="hidden" name="answers[${index}][selected_option]" value="${ans.selected_option}" />
                `;
            });
        }

        
        startBtn.addEventListener('click', () => {
            quizStarted = true;
            window.onbeforeunload = () => "Are you sure you want to leave? Your progress will be lost.";
            shuffle(questions);
            document.getElementById("start-screen").classList.add("hide");
            questionsEl.classList.remove("hide");
            getQuestion();
        });

        form.addEventListener('submit', () => {
            quizStarted = false;
            window.onbeforeunload = null;
        });

        document.addEventListener("visibilitychange", () => {
            if (!quizStarted) return;
            fetch('/report-tab-switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    student_id: {{ session('student_id') }},
                    quiz_id: {{ $quiz->id }},
                    state: document.hidden ? 'hidden' : 'visible',
                    time: new Date().toISOString()
                })
            });
        });
    </script>
</body>
</html>
