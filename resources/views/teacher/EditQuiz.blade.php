@extends('layout')

@section('title', 'Quiz Details')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-7xl mx-auto py-10 px-4 text-white">
    {{-- Quiz Info --}}
    <div class="bg-[#1E293B] shadow-xl rounded-xl p-6 mb-10">
        <div class="flex justify-between items-center">
            <h3 class="text-3xl font-semibold text-white">{{ $quiz->title }}</h3>
            <span class="text-sm text-gray-400">Created: {{ $quiz->created_at->format('d M, Y H:i') }}</span>
        </div>
    </div>

    {{-- Questions List --}}
    <div class="bg-[#0F172A] shadow-xl rounded-xl mb-10">
        <div class="border-b border-blue-700 px-6 py-4">
            <h5 class="text-xl font-semibold text-white">📋 Questions</h5>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-blue-800 text-white">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Question</th>
                        <th class="px-6 py-3">Options</th>
                        <th class="px-6 py-3 text-center" colspan="2">Actions</th>
                    </tr>
                </thead>
                <tbody id="questions-body" class="bg-[#1E293B] text-gray-300 divide-y divide-blue-800">
                    @forelse ($quiz->questions as $question)
                        <tr class="hover:bg-[#334155] transition">
                            <td class="px-6 py-4">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 max-w-md">
                                @if ($question->text) 
                                    @if (str_contains($question->text, '```'))
                                        <pre class="bg-slate-900 text-emerald-400 p-2 rounded text-xs font-mono max-h-32 overflow-y-auto whitespace-pre-wrap">{{ $question->text }}</pre>
                                    @else
                                        {{ $question->text }}
                                    @endif
                                @elseif ($question->image)
                                    <img src="{{ asset('storage/' . $question->image) }}" class="w-48 h-auto rounded shadow">
                                @else
                                    <em class="text-gray-400">No content</em>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach([$question->option1,$question->option2,$question->option3,$question->option4] as $i=>$opt)
                                        <li class="@if($i+1==$question->right_option) text-blue-400 font-semibold @endif">
                                            {{ $opt }}
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button class="text-red-400 hover:text-red-300 transition delete-btn" data-id="{{ $question->id }}">Delete</button>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('questions.edit', ['id' => $question->id]) }}" method="GET">
                                    @csrf
                                    <button type="submit" class="text-blue-400 hover:underline">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr id="no-questions">
                            <td colspan="5" class="px-6 py-4 text-center text-gray-400">No questions found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Question --}}
    <div class="bg-[#0F172A] shadow-xl rounded-xl mb-10">
        <div class="border-b border-blue-700 px-6 py-4">
            <h5 class="text-xl font-semibold text-white">➕ Add Question</h5>
        </div>
        <div class="p-6">
            <form id="ajx" action="{{ route('questions.add') }}" method="POST" enctype="multipart/form-data">


                @csrf
                <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-300 font-medium mb-1">Question Type</label>
                        <select id="question_type" name="question_type" class="w-full rounded-md bg-blue-900 text-white border border-blue-600 px-3 py-2" onchange="toggleQuestionInput()" required>
                            <option value="text">Standard Text</option>
                            <option value="code">Code Snippet (VS Code style)</option>
                            <option value="image">Image / Diagram</option>
                        </select>
                    </div>

                    <div id="image-input" class="hidden">
                        <label class="block text-gray-300 font-medium mb-1">Attach Image (Optional)</label>
                        <input type="file" name="question_image" class="w-full rounded-md bg-blue-900 text-white border border-blue-600 px-3 py-1.5" accept="image/*">
                    </div>

                    <div class="md:col-span-2" id="text-input">
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-gray-300 font-medium">Question Text & Code Snippet</label>
                            <div class="flex items-center gap-2">
                                <select id="code_lang" class="text-xs bg-slate-800 text-indigo-300 border border-slate-600 rounded px-2 py-1">
                                    <option value="cpp">C++</option>
                                    <option value="c">C</option>
                                    <option value="python">Python</option>
                                    <option value="java">Java</option>
                                    <option value="javascript">JavaScript</option>
                                    <option value="sql">SQL</option>
                                    <option value="html">HTML</option>
                                </select>
                                <button type="button" onclick="insertCodeBlock()" class="text-xs bg-indigo-600 hover:bg-indigo-500 text-white px-2 py-1 rounded font-mono">💻 Insert Code Box</button>
                                <button type="button" onclick="insertInlineCode()" class="text-xs bg-slate-700 hover:bg-slate-600 text-indigo-200 px-2 py-1 rounded font-mono">`code`</button>
                            </div>
                        </div>
                        <textarea id="question_text" name="question_text" rows="5" class="w-full rounded-md bg-slate-900 text-indigo-100 border border-slate-700 font-mono text-sm p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="Enter your question prompt here. You can paste code directly into the code box or use the 'Insert Code Box' button above!"></textarea>
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-gray-300 font-medium">Multiple-Choice Options</label>
                            <span class="text-xs text-indigo-300">Click <code class="bg-slate-800 text-amber-300 px-1 py-0.5 rounded border border-slate-600 font-mono">&lt;/&gt; Code</code> to format an option as code!</span>
                        </div>
                        @for ($i = 1; $i <= 4; $i++)
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 text-indigo-300 font-bold flex items-center justify-center text-sm shrink-0">{{ chr(64 + $i) }}</span>
                                <input type="text" id="option_{{ $i }}" name="options[{{ $i }}]" class="flex-grow rounded-md bg-slate-900 text-white border border-slate-700 font-mono text-sm px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none" placeholder="Option {{ chr(64 + $i) }} (e.g. {{ $i === 1 ? 'int x = 10;' : 'Option ' . $i }})" required>
                                <button type="button" onclick="makeOptionCode({{ $i }})" class="shrink-0 bg-slate-800 hover:bg-slate-700 border border-slate-600 text-amber-300 hover:text-amber-200 text-xs px-3 py-2 rounded-md font-mono font-bold transition flex items-center gap-1">
                                    <span>&lt;/&gt;</span> Code
                                </button>
                            </div>
                        @endfor
                    </div>

                    <div>
                        <label class="block text-gray-300 font-medium mb-1">Correct Option</label>
                        <select name="correct_option" class="w-full rounded-md bg-blue-900 text-white border border-blue-600 px-3 py-2" required>
                            <option disabled selected>Select</option>
                            @for ($i = 1; $i <= 4; $i++)
                                <option value="{{ $i }}">Option {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-300 font-medium mb-1">Duration (sec)</label>
                        <input type="number" name="duration" class="w-full rounded-md bg-blue-900 text-white border border-blue-600 px-3 py-2" min="1" placeholder="e.g., 30" value="60">
                    </div>

                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2.5 rounded-md font-semibold shadow transition-all flex items-center gap-2">
                            <span>Add Question</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Schedule Quiz --}}
    <div class="bg-[#0F172A] shadow-xl rounded-xl">
        <div class="border-b border-blue-700 px-6 py-4">
            <h5 class="text-xl font-semibold text-white">⏱ Schedule Quiz</h5>
        </div>
        <div class="p-6">
            <form action="{{ route('quiz.schedule', $quiz->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                @csrf
                <div>
                    <label for="start_datetime" class="block text-gray-300 font-medium mb-1">Start Date & Time</label>
                    <input type="text" id="start_datetime" name="start_datetime" class="w-full rounded-md bg-blue-900 text-white border border-blue-600" required>
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-md shadow font-semibold">Schedule</button>
                </div>
            </form>

            <form action="{{ route('quiz.startnow', $quiz->id) }}" method="POST" class="text-right">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-6 py-2 rounded-md shadow font-semibold">Start Now</button>
            </form>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script>
    function toggleQuestionInput() {
        const type = document.getElementById('question_type').value;
        const textInput = document.getElementById('text-input');
        const imageInput = document.getElementById('image-input');
        const questionText = document.getElementById('question_text');
        
        if (type === 'code') {
            textInput.classList.remove('hidden');
            imageInput.classList.add('hidden');
            if (!questionText.value.includes('```')) {
                const lang = document.getElementById('code_lang').value || 'cpp';
                questionText.value = "What is the output of the following code?\n\n```" + lang + "\n// Paste your raw code snippet here directly from VS Code / Code::Blocks\n\n```";
            }
        } else if (type === 'image') {
            textInput.classList.remove('hidden');
            imageInput.classList.remove('hidden');
        } else {
            textInput.classList.remove('hidden');
            imageInput.classList.add('hidden');
        }
    }

    function insertCodeBlock() {
        const txtArea = document.getElementById('question_text');
        const lang = document.getElementById('code_lang').value || 'cpp';
        const start = txtArea.selectionStart;
        const end = txtArea.selectionEnd;
        const selectedText = txtArea.value.substring(start, end) || "// Paste your raw code here";
        const replacement = "\n```" + lang + "\n" + selectedText + "\n```\n";
        txtArea.value = txtArea.value.substring(0, start) + replacement + txtArea.value.substring(end);
        txtArea.focus();
    }

    function insertInlineCode() {
        const txtArea = document.getElementById('question_text');
        const start = txtArea.selectionStart;
        const end = txtArea.selectionEnd;
        const selectedText = txtArea.value.substring(start, end) || "code";
        const replacement = "`" + selectedText + "`";
        txtArea.value = txtArea.value.substring(0, start) + replacement + txtArea.value.substring(end);
        txtArea.focus();
    }

    function makeOptionCode(optNum) {
        const input = document.getElementById('option_' + optNum);
        if (!input) return;
        let val = input.value.trim();
        if (!val) {
            input.value = '`code`';
        } else if (val.startsWith('`') && val.endsWith('`') && val.length >= 2) {
            // Toggle off backticks if clicked again
            input.value = val.slice(1, -1);
        } else {
            input.value = '`' + val + '`';
        }
        input.focus();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        document.getElementById('ajx').onsubmit = async function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            try {
                const res = await fetch(this.action, {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const q = await res.json();
                if (!res.ok) {
                    const errMsgs = q.errors ? Object.values(q.errors).flat().join('\n') : (q.message || 'Failed to add question.');
                    return alert('Validation Error:\n' + errMsgs);
                }

                const tbody = document.getElementById('questions-body');
                document.getElementById('no-questions')?.remove();

                const idx = tbody.querySelectorAll('tr').length + 1;
                
                let content = '<em class="text-gray-400">No content</em>';
                if (q.text) {
                    if (q.text.includes('```')) {
                        const cleanCode = q.text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        content = `<pre class="bg-slate-900 text-emerald-400 p-2 rounded text-xs font-mono max-h-32 overflow-y-auto whitespace-pre-wrap">${cleanCode}</pre>`;
                    } else {
                        content = q.text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    }
                } else if (q.image) {
                    content = `<img src="/storage/${q.image}" class="w-48 h-auto rounded shadow">`;
                }

                const options = [q.option1, q.option2, q.option3, q.option4]
                    .map((opt, i) => `<li class="${i+1==q.right_option?'text-blue-400 font-semibold':''}">${opt}</li>`).join('');

                const row = document.createElement('tr');
                row.className = "hover:bg-[#334155] transition";
                row.innerHTML = `
                    <td class="px-6 py-4">${idx}</td>
                    <td class="px-6 py-4 max-w-md">${content}</td>
                    <td class="px-6 py-4"><ul class="list-disc pl-5 space-y-1">${options}</ul></td>
                    <td class="px-6 py-4 text-center"><button class="text-red-400 hover:text-red-300 transition delete-btn" data-id="${q.id}">Delete</button></td>
                    <td class="px-6 py-4 text-center">
                        <form action="/questions/${q.id}/edit" method="GET">
                            @csrf
                            <button type="submit" class="text-blue-400 hover:underline">Update</button>
                        </form>
                    </td>
                `;
                tbody.appendChild(row);
                this.reset();
                toggleQuestionInput();
            } catch (err) {
                console.error("Submit error:", err);
                alert("An error occurred while saving the question.");
            }
        };

        document.addEventListener('click', async (e) => {
            if (!e.target.classList.contains('delete-btn')) return;
            if (!confirm('Are you sure?')) return;
            const id = e.target.dataset.id;
            const res = await fetch(`/questions/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                }
            });
            if (res.ok) {
                e.target.closest('tr').remove();
            } else {
                alert('Delete failed.');
            }
        });

        flatpickr("#start_datetime", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            theme: "material_blue"
        });
    });
</script>
@endsection