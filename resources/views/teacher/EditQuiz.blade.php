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
                            <td class="px-6 py-4">
                                @if ($question->text) 
                                    {{ $question->text }}
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
                        <select id="question_type" name="question_type" class="w-full rounded-md bg-blue-900 text-white border border-blue-600" onchange="toggleQuestionInput()" required>
                            <option value="text">Text</option>
                            <option value="image">Image</option>
                        </select>
                    </div>

                    <div id="text-input">
                        <label class="block text-gray-300 font-medium mb-1">Question Text</label>
                        <input type="text" name="question_text" class="w-full rounded-md bg-blue-900 text-white border border-blue-600" placeholder="Enter question text">
                    </div>

                    <div id="image-input" class="hidden">
                        <label class="block text-gray-300 font-medium mb-1">Question Image</label>
                        <input type="file" name="question_image" class="w-full rounded-md bg-blue-900 text-white border border-blue-600" accept="image/*">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-300 font-medium mb-1">Options</label>
                        @for ($i = 1; $i <= 4; $i++)
                            <input type="text" name="options[{{ $i }}]" class="w-full rounded-md bg-blue-900 text-white border border-blue-600 mb-2" placeholder="Option {{ $i }}" required>
                        @endfor
                    </div>

                    <div>
                        <label class="block text-gray-300 font-medium mb-1">Correct Option</label>
                        <select name="correct_option" class="w-full rounded-md bg-blue-900 text-white border border-blue-600" required>
                            <option disabled selected>Select</option>
                            @for ($i = 1; $i <= 4; $i++)
                                <option value="{{ $i }}">Option {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-300 font-medium mb-1">Duration (sec)</label>
                        <input type="number" name="duration" class="w-full rounded-md bg-blue-900 text-white border border-blue-600" min="1" placeholder="e.g., 30">
                    </div>

                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-md font-semibold shadow transition-all">Add</button>
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
        document.getElementById('text-input').classList.toggle('hidden', type !== 'text');
        document.getElementById('image-input').classList.toggle('hidden', type !== 'image');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        document.getElementById('ajx').onsubmit = async function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const res = await fetch(this.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                body: formData
            });

            const q = await res.json();
            if (!res.ok) return alert('Failed to add question.');

            const tbody = document.getElementById('questions-body');
            document.getElementById('no-questions')?.remove();

            const idx = tbody.querySelectorAll('tr').length + 1;
            const content = q.text ? q.text : q.image ? `<img src="/storage/${q.image}" class="w-48 h-auto rounded shadow">` : '<em class="text-gray-400">No content</em>';
            const options = [q.option1, q.option2, q.option3, q.option4]
                .map((opt, i) => `<li class="${i+1==q.right_option?'text-blue-400 font-semibold':''}">${opt}</li>`).join('');

            const row = document.createElement('tr');
            row.className = "hover:bg-[#334155] transition";
            row.innerHTML = `
                <td class="px-6 py-4">${idx}</td>
                <td class="px-6 py-4">${content}</td>
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