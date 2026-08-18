@props([
    'quiz',
    'question' => null,
    'isEdit' => false
])

@php
    $questionId = $question ? $question->id : null;
    $initialText = old('question_text', $question ? $question->text : '');
    $initialDuration = old('duration', $question ? $question->duration : 60);
    $initialRightOption = old('correct_option', $question ? $question->right_option : null);
    $initialOpt1 = old('options.1', old('option1', $question ? $question->option1 : ''));
    $initialOpt2 = old('options.2', old('option2', $question ? $question->option2 : ''));
    $initialOpt3 = old('options.3', old('option3', $question ? $question->option3 : ''));
    $initialOpt4 = old('options.4', old('option4', $question ? $question->option4 : ''));
@endphp

<div id="question-studio-root" 
     class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden transition-all duration-300"
     data-quiz-id="{{ $quiz->id }}"
     data-is-edit="{{ $isEdit ? 'true' : 'false' }}"
     data-question-id="{{ $questionId ?? '' }}">

    <!-- Studio Header -->
    <div class="bg-slate-950/80 border-b border-slate-800 px-4 sm:px-6 py-3.5 flex flex-wrap items-center justify-between gap-3 select-none">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 font-bold text-sm">
                {{ $isEdit ? '✏️' : '⚡' }}
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-bold text-white tracking-tight flex items-center gap-2">
                    {{ $isEdit ? 'Edit Question #' . $questionId : 'Question Studio' }}
                    <span class="text-xs px-2 py-0.5 rounded-full font-mono font-medium {{ $isEdit ? 'bg-amber-950/60 text-amber-300 border border-amber-800/50' : 'bg-indigo-950/60 text-indigo-300 border border-indigo-800/50' }}">
                        {{ $isEdit ? 'Editing Mode' : 'Draft' }}
                    </span>
                </h3>
                <p class="text-xs text-slate-400 hidden sm:block">Content-first editor with live student preview & multi-language code support</p>
            </div>
        </div>

        <!-- Status & Mode Switcher -->
        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Dynamic Status Badge -->
            <div id="studio-status-badge" class="px-2.5 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 bg-amber-950/40 text-amber-300 border border-amber-800/40">
                <div class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></div>
                <span id="studio-status-text">Drafting...</span>
            </div>

            <!-- Mobile View Switcher Tabs -->
            <div class="flex lg:hidden bg-slate-900 border border-slate-700/80 rounded-lg p-0.5 text-xs font-medium">
                <button type="button" id="tab-btn-edit" class="px-3 py-1.5 rounded-md bg-indigo-600 text-white font-semibold shadow-sm transition">
                    ✍️ Edit
                </button>
                <button type="button" id="tab-btn-preview" class="px-3 py-1.5 rounded-md text-slate-400 hover:text-slate-200 transition">
                    👁️ Preview
                </button>
            </div>

            <!-- Autosave Draft Indicator -->
            <span id="draft-indicator" class="text-[11px] text-slate-400 hidden md:inline-flex items-center gap-1 font-mono">
                <span class="text-emerald-400">●</span> Draft protected
            </span>
        </div>
    </div>

    <!-- Main Studio Body (Split Screen on Desktop, Tabbed on Mobile) -->
    <form id="studio-form" 
          action="{{ $isEdit ? route('questions.update', $questionId) : route('questions.add') }}" 
          method="POST" 
          enctype="multipart/form-data" 
          class="p-0 m-0">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif
        <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
        <input type="hidden" id="studio-correct-option" name="correct_option" value="{{ $initialRightOption }}">

        <div class="grid grid-cols-1 lg:grid-cols-12 min-h-[580px] divide-y lg:divide-y-0 lg:divide-x divide-slate-800">
            
            <!-- LEFT COLUMN: EDITOR STUDIO (7 cols on desktop) -->
            <div id="studio-editor-panel" class="lg:col-span-7 p-4 sm:p-6 space-y-6 overflow-y-auto">
                
                <!-- 1. Question Prompt Card -->
                <div class="bg-slate-950/60 border border-slate-800/90 rounded-xl p-4 sm:p-5 space-y-3 shadow-inner">
                    <div class="flex items-center justify-between">
                        <label for="studio-prompt-input" class="text-xs sm:text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                            <span>1. Question Prompt</span>
                            <span class="text-rose-400">*</span>
                        </label>
                        
                        <!-- Presets Dropdown & Formatting Bar -->
                        <div class="flex items-center gap-1.5">
                            <button type="button" id="btn-format-bold" title="Bold (**text**)" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded text-xs font-bold transition">B</button>
                            <button type="button" id="btn-format-italic" title="Italic (*text*)" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded text-xs italic transition">I</button>
                            <button type="button" id="btn-format-inline-code" title="Inline code (`code`)" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-amber-300 rounded text-xs font-mono transition">&lt;/&gt;</button>
                            
                            <!-- Preset Starters Menu -->
                            <div class="relative inline-block text-left">
                                <button type="button" id="btn-presets-toggle" class="px-2.5 py-1 bg-indigo-950/70 hover:bg-indigo-900 border border-indigo-800/60 text-indigo-300 rounded text-xs font-semibold flex items-center gap-1 transition">
                                    <span>⚡ Starters</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div id="presets-dropdown" class="hidden absolute right-0 mt-1 w-64 bg-slate-900 border border-slate-700 rounded-xl shadow-xl z-30 py-1 text-xs divide-y divide-slate-800">
                                    <button type="button" class="preset-item w-full text-left px-3 py-2 text-slate-300 hover:bg-slate-800 transition" data-text="What is the output of the following code?">What is the output of the following code?</button>
                                    <button type="button" class="preset-item w-full text-left px-3 py-2 text-slate-300 hover:bg-slate-800 transition" data-text="Identify the error / bug in this code snippet:">Identify the error / bug in this code snippet:</button>
                                    <button type="button" class="preset-item w-full text-left px-3 py-2 text-slate-300 hover:bg-slate-800 transition" data-text="What is the time complexity of the algorithm below?">What is the time complexity of the algorithm below?</button>
                                    <button type="button" class="preset-item w-full text-left px-3 py-2 text-slate-300 hover:bg-slate-800 transition" data-text="Which of the following statements is TRUE?">Which of the following statements is TRUE?</button>
                                    <button type="button" class="preset-item w-full text-left px-3 py-2 text-slate-300 hover:bg-slate-800 transition" data-text="Fill in the missing blank (___) to complete the code:">Fill in the missing blank (___) to complete the code:</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prompt Textarea -->
                    <textarea 
                        id="studio-prompt-input" 
                        name="question_text" 
                        rows="3" 
                        placeholder="Type your question prompt here... (e.g., What will be printed when this function executes?)" 
                        class="w-full bg-slate-900 text-slate-100 placeholder-slate-500 border border-slate-700/80 rounded-xl p-3.5 text-sm sm:text-base focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition leading-relaxed resize-y"
                    >{{ $initialText }}</textarea>

                    <!-- Progressive Disclosure Action Bar -->
                    <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" id="btn-toggle-code" class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition border border-slate-700 bg-slate-800/80 text-slate-300 hover:text-white hover:bg-slate-700">
                                <span>💻</span>
                                <span id="toggle-code-label">+ Code Snippet</span>
                            </button>
                        </div>

                        <button type="button" id="btn-clear-prompt" class="text-xs text-slate-500 hover:text-rose-400 transition">
                            Clear
                        </button>
                    </div>
                </div>

                <!-- 2. Dedicated Structured Code Editor Block (Progressively Disclosed) -->
                <div id="studio-code-block" class="hidden bg-[#0d1117] border border-slate-700/80 rounded-xl overflow-hidden shadow-lg transition-all duration-300">
                    <!-- Code Block Header / Language Bar -->
                    <div class="bg-[#161b22] border-b border-slate-700/80 px-4 py-2.5 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-2">
                            <div class="flex gap-1.5">
                                <div class="w-2.5 h-2.5 rounded-full bg-red-500/80"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-yellow-500/80"></div>
                                <div class="w-2.5 h-2.5 rounded-full bg-green-500/80"></div>
                            </div>
                            <span class="font-bold text-indigo-400 uppercase tracking-wider text-[11px] ml-1">Language:</span>
                            <select id="studio-code-lang" class="bg-slate-800 text-indigo-200 border border-slate-700 rounded-md px-2.5 py-1 text-xs font-mono font-semibold focus:ring-1 focus:ring-indigo-500 focus:outline-none cursor-pointer">
                                <option value="cpp" selected>C++</option>
                                <option value="c">C</option>
                                <option value="python">Python</option>
                                <option value="java">Java</option>
                                <option value="javascript">JavaScript</option>
                                <option value="typescript">TypeScript</option>
                                <option value="sql">SQL</option>
                                <option value="html">HTML / XML</option>
                                <option value="css">CSS</option>
                                <option value="php">PHP</option>
                                <option value="go">Go</option>
                                <option value="rust">Rust</option>
                                <option value="bash">Bash / Shell</option>
                                <option value="json">JSON</option>
                            </select>
                        </div>

                        <!-- Language Quick Chips & Actions -->
                        <div class="flex items-center gap-1.5">
                            <button type="button" id="btn-insert-template" title="Insert standard template for this language" class="px-2 py-0.5 bg-slate-800 hover:bg-slate-700 text-indigo-300 rounded text-[11px] font-mono transition">
                                📋 Template
                            </button>
                            <button type="button" id="btn-remove-code-block" title="Remove code block" class="px-2 py-0.5 bg-rose-950/50 hover:bg-rose-900 text-rose-300 border border-rose-800/40 rounded text-[11px] transition">
                                ✕ Remove
                            </button>
                        </div>
                    </div>

                    <!-- Code Textarea with Line Numbers Gutter -->
                    <div class="flex text-slate-100 font-mono text-xs sm:text-sm bg-[#0d1117] min-h-[140px]">
                        <div id="code-line-numbers" class="select-none bg-[#090d13] text-slate-600 text-right py-3 px-3 border-r border-slate-800/80 leading-6 font-mono w-10 shrink-0">
                            1
                        </div>
                        <textarea 
                            id="studio-code-input" 
                            rows="6" 
                            placeholder="// Paste or write your raw code here&#10;// Tab key indents automatically" 
                            class="w-full bg-transparent text-emerald-300 placeholder-slate-600 p-3 font-mono text-xs sm:text-sm leading-6 border-0 focus:ring-0 focus:outline-none resize-y whitespace-pre"
                            spellcheck="false"
                        ></textarea>
                    </div>
                </div>

                <!-- 4. Multiple Choice Answers (A, B, C, D) -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-xs sm:text-sm font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                            <span>2. Multiple-Choice Answers</span>
                            <span class="text-rose-400">*</span>
                        </label>
                        <span class="text-xs text-slate-400 flex items-center gap-1">
                            <span class="text-indigo-400">💡 Click card</span> to mark as correct answer
                        </span>
                    </div>

                    <!-- 4 Option Cards Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="options-container">
                        
                        <!-- Option A -->
                        <div class="option-card relative rounded-xl border p-3.5 transition-all duration-200 cursor-pointer select-none bg-slate-950/70 border-slate-800 hover:border-slate-700"
                             data-opt-num="1">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-md bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 font-black text-xs flex items-center justify-center shrink-0">A</span>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Option A</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" class="opt-code-toggle text-[11px] px-1.5 py-0.5 bg-slate-800 hover:bg-slate-700 text-amber-300 rounded font-mono border border-slate-700" title="Format option as code">&lt;/&gt;</button>
                                    <div class="opt-radio-indicator w-5 h-5 rounded-full border-2 border-slate-700 flex items-center justify-center transition-colors">
                                        <div class="opt-radio-dot w-2.5 h-2.5 rounded-full bg-transparent"></div>
                                    </div>
                                </div>
                            </div>
                            <input 
                                type="text" 
                                id="studio-opt-1" 
                                name="options[1]" 
                                value="{{ $initialOpt1 }}" 
                                placeholder="Answer choice A..." 
                                required 
                                class="w-full bg-slate-900 text-white placeholder-slate-500 border border-slate-700/80 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition font-sans opt-text-input"
                            >
                            <div class="opt-correct-badge hidden text-[11px] font-bold text-emerald-400 mt-1.5 flex items-center gap-1">
                                <span>✓ Correct Answer</span>
                            </div>
                        </div>

                        <!-- Option B -->
                        <div class="option-card relative rounded-xl border p-3.5 transition-all duration-200 cursor-pointer select-none bg-slate-950/70 border-slate-800 hover:border-slate-700"
                             data-opt-num="2">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-md bg-blue-500/20 border border-blue-500/40 text-blue-300 font-black text-xs flex items-center justify-center shrink-0">B</span>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Option B</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" class="opt-code-toggle text-[11px] px-1.5 py-0.5 bg-slate-800 hover:bg-slate-700 text-amber-300 rounded font-mono border border-slate-700" title="Format option as code">&lt;/&gt;</button>
                                    <div class="opt-radio-indicator w-5 h-5 rounded-full border-2 border-slate-700 flex items-center justify-center transition-colors">
                                        <div class="opt-radio-dot w-2.5 h-2.5 rounded-full bg-transparent"></div>
                                    </div>
                                </div>
                            </div>
                            <input 
                                type="text" 
                                id="studio-opt-2" 
                                name="options[2]" 
                                value="{{ $initialOpt2 }}" 
                                placeholder="Answer choice B..." 
                                required 
                                class="w-full bg-slate-900 text-white placeholder-slate-500 border border-slate-700/80 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition font-sans opt-text-input"
                            >
                            <div class="opt-correct-badge hidden text-[11px] font-bold text-emerald-400 mt-1.5 flex items-center gap-1">
                                <span>✓ Correct Answer</span>
                            </div>
                        </div>

                        <!-- Option C -->
                        <div class="option-card relative rounded-xl border p-3.5 transition-all duration-200 cursor-pointer select-none bg-slate-950/70 border-slate-800 hover:border-slate-700"
                             data-opt-num="3">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-md bg-purple-500/20 border border-purple-500/40 text-purple-300 font-black text-xs flex items-center justify-center shrink-0">C</span>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Option C</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" class="opt-code-toggle text-[11px] px-1.5 py-0.5 bg-slate-800 hover:bg-slate-700 text-amber-300 rounded font-mono border border-slate-700" title="Format option as code">&lt;/&gt;</button>
                                    <div class="opt-radio-indicator w-5 h-5 rounded-full border-2 border-slate-700 flex items-center justify-center transition-colors">
                                        <div class="opt-radio-dot w-2.5 h-2.5 rounded-full bg-transparent"></div>
                                    </div>
                                </div>
                            </div>
                            <input 
                                type="text" 
                                id="studio-opt-3" 
                                name="options[3]" 
                                value="{{ $initialOpt3 }}" 
                                placeholder="Answer choice C..." 
                                required 
                                class="w-full bg-slate-900 text-white placeholder-slate-500 border border-slate-700/80 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition font-sans opt-text-input"
                            >
                            <div class="opt-correct-badge hidden text-[11px] font-bold text-emerald-400 mt-1.5 flex items-center gap-1">
                                <span>✓ Correct Answer</span>
                            </div>
                        </div>

                        <!-- Option D -->
                        <div class="option-card relative rounded-xl border p-3.5 transition-all duration-200 cursor-pointer select-none bg-slate-950/70 border-slate-800 hover:border-slate-700"
                             data-opt-num="4">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-md bg-amber-500/20 border border-amber-500/40 text-amber-300 font-black text-xs flex items-center justify-center shrink-0">D</span>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Option D</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" class="opt-code-toggle text-[11px] px-1.5 py-0.5 bg-slate-800 hover:bg-slate-700 text-amber-300 rounded font-mono border border-slate-700" title="Format option as code">&lt;/&gt;</button>
                                    <div class="opt-radio-indicator w-5 h-5 rounded-full border-2 border-slate-700 flex items-center justify-center transition-colors">
                                        <div class="opt-radio-dot w-2.5 h-2.5 rounded-full bg-transparent"></div>
                                    </div>
                                </div>
                            </div>
                            <input 
                                type="text" 
                                id="studio-opt-4" 
                                name="options[4]" 
                                value="{{ $initialOpt4 }}" 
                                placeholder="Answer choice D..." 
                                required 
                                class="w-full bg-slate-900 text-white placeholder-slate-500 border border-slate-700/80 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:outline-none transition font-sans opt-text-input"
                            >
                            <div class="opt-correct-badge hidden text-[11px] font-bold text-emerald-400 mt-1.5 flex items-center gap-1">
                                <span>✓ Correct Answer</span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 5. Settings: Duration Chips & Presets -->
                <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-4 flex flex-wrap items-center justify-between gap-4">
                    <div class="space-y-1">
                        <label for="studio-duration-input" class="text-xs font-bold text-slate-200 uppercase tracking-wider block">
                            3. Time Limit per Question
                        </label>
                        <p class="text-xs text-slate-400">Time students get to answer this question</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1 bg-slate-900 border border-slate-700 rounded-lg p-1">
                            <button type="button" class="duration-chip px-2.5 py-1 rounded text-xs font-semibold text-slate-300 hover:text-white transition" data-sec="15">15s</button>
                            <button type="button" class="duration-chip px-2.5 py-1 rounded text-xs font-semibold text-slate-300 hover:text-white transition" data-sec="30">30s</button>
                            <button type="button" class="duration-chip px-2.5 py-1 rounded text-xs font-semibold text-slate-300 hover:text-white transition" data-sec="45">45s</button>
                            <button type="button" class="duration-chip px-2.5 py-1 rounded text-xs font-semibold text-slate-300 hover:text-white transition" data-sec="60">60s</button>
                            <button type="button" class="duration-chip px-2.5 py-1 rounded text-xs font-semibold text-slate-300 hover:text-white transition" data-sec="90">90s</button>
                            <button type="button" class="duration-chip px-2.5 py-1 rounded text-xs font-semibold text-slate-300 hover:text-white transition" data-sec="120">120s</button>
                        </div>
                        <div class="flex items-center gap-1">
                            <input 
                                type="number" 
                                id="studio-duration-input" 
                                name="duration" 
                                min="5" 
                                max="3600" 
                                value="{{ $initialDuration }}" 
                                class="w-16 bg-slate-900 text-white border border-slate-700 rounded-lg px-2 py-1.5 text-xs font-mono font-bold text-center focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                            >
                            <span class="text-xs text-slate-400 font-mono">sec</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: LIVE STUDENT PREVIEW (5 cols on desktop) -->
            <div id="studio-preview-panel" class="hidden lg:block lg:col-span-5 bg-slate-950/40 p-4 sm:p-6 flex flex-col justify-between overflow-y-auto">
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">👁️ Student Live Preview</span>
                            <span class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded font-mono">Real-time</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-1 bg-indigo-950/60 border border-indigo-800/40 text-indigo-300 px-2.5 py-1 rounded-md text-xs font-mono font-bold">
                                <span>⏱️</span>
                                <span id="preview-timer-display">{{ $initialDuration }}s</span>
                            </div>
                        </div>
                    </div>

                    <!-- Live Question Box Rendering -->
                    <div class="bg-white rounded-2xl p-5 sm:p-6 text-slate-900 shadow-xl border border-slate-200/80 space-y-4 text-left">
                        <!-- Header / Quiz Title preview -->
                        <div class="flex justify-between items-center border-b border-slate-100 pb-2.5">
                            <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">{{ $quiz->title }}</span>
                            <span class="text-[11px] text-slate-400 font-medium">Question Preview</span>
                        </div>

                        <!-- Prompt Text rendered -->
                        <div id="preview-prompt-text" class="text-base sm:text-lg font-semibold text-slate-800 leading-snug break-words">
                            <span class="text-slate-400 italic font-normal">Start typing your question prompt on the left...</span>
                        </div>

                        <!-- Code Box rendered -->
                        <div id="preview-code-container" class="hidden"></div>

                        <!-- Options Rendered -->
                        <div class="space-y-2 pt-2">
                            <div id="preview-opt-1" class="p-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 text-sm font-medium flex items-center justify-between transition-colors">
                                <span class="flex items-center gap-2">
                                    <span class="font-bold text-slate-400">1.</span>
                                    <span class="opt-label text-slate-400 italic">Option A</span>
                                </span>
                                <div class="w-4 h-4 rounded-full border border-slate-300"></div>
                            </div>
                            <div id="preview-opt-2" class="p-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 text-sm font-medium flex items-center justify-between transition-colors">
                                <span class="flex items-center gap-2">
                                    <span class="font-bold text-slate-400">2.</span>
                                    <span class="opt-label text-slate-400 italic">Option B</span>
                                </span>
                                <div class="w-4 h-4 rounded-full border border-slate-300"></div>
                            </div>
                            <div id="preview-opt-3" class="p-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 text-sm font-medium flex items-center justify-between transition-colors">
                                <span class="flex items-center gap-2">
                                    <span class="font-bold text-slate-400">3.</span>
                                    <span class="opt-label text-slate-400 italic">Option C</span>
                                </span>
                                <div class="w-4 h-4 rounded-full border border-slate-300"></div>
                            </div>
                            <div id="preview-opt-4" class="p-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 text-sm font-medium flex items-center justify-between transition-colors">
                                <span class="flex items-center gap-2">
                                    <span class="font-bold text-slate-400">4.</span>
                                    <span class="opt-label text-slate-400 italic">Option D</span>
                                </span>
                                <div class="w-4 h-4 rounded-full border border-slate-300"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keyboard shortcuts note on desktop -->
                <div class="pt-6 border-t border-slate-800 text-[11px] text-slate-500 flex justify-between items-center">
                    <span>Hotkeys: <kbd class="px-1 bg-slate-800 rounded border border-slate-700 text-slate-400">Ctrl+Enter</kbd> Save · <kbd class="px-1 bg-slate-800 rounded border border-slate-700 text-slate-400">Ctrl+K</kbd> Code</span>
                    <span class="text-emerald-400">SaaS Studio v2.0</span>
                </div>
            </div>

        </div>

        <!-- Studio Sticky Bottom Action Bar -->
        <div class="bg-slate-950 border-t border-slate-800 px-4 sm:px-6 py-3.5 flex flex-wrap items-center justify-between gap-3 sticky bottom-0 z-20">
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <span id="validation-hint" class="flex items-center gap-1 text-amber-400">
                    <span>⚠</span> Fill question prompt & select correct answer
                </span>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                @if ($isEdit)
                    <a href="{{ route('quiz.details', $quiz->id) }}" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white bg-slate-800/80 hover:bg-slate-700 transition">
                        Cancel
                    </a>
                @else
                    <button type="button" id="btn-discard-draft" class="px-3 py-2 rounded-xl text-xs font-medium text-slate-400 hover:text-rose-400 transition hidden sm:inline-block">
                        Discard Draft
                    </button>
                @endif

                <button 
                    type="submit" 
                    id="btn-save-question" 
                    class="flex-grow sm:flex-grow-0 px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-900/30 transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                >
                    <span id="btn-save-spinner" class="hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span id="btn-save-text">{{ $isEdit ? 'Update Question' : 'Save Question' }}</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Studio Client-Side Controller Script -->
<script>
(function() {
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.getElementById('question-studio-root');
        if (!root) return;

        const quizId = root.dataset.quizId;
        const isEdit = root.dataset.isEdit === 'true';
        const questionId = root.dataset.questionId;
        const draftKey = `quiz_studio_draft_${quizId}`;

        // DOM elements
        const form = document.getElementById('studio-form');
        const promptInput = document.getElementById('studio-prompt-input');
        const codeBlock = document.getElementById('studio-code-block');
        const codeInput = document.getElementById('studio-code-input');
        const codeLangSelect = document.getElementById('studio-code-lang');
        const codeLineNumbers = document.getElementById('code-line-numbers');
        const btnToggleCode = document.getElementById('btn-toggle-code');
        const toggleCodeLabel = document.getElementById('toggle-code-label');
        const btnRemoveCodeBlock = document.getElementById('btn-remove-code-block');
        const btnInsertTemplate = document.getElementById('btn-insert-template');

        const optionCards = document.querySelectorAll('.option-card');
        const correctOptionInput = document.getElementById('studio-correct-option');
        const durationInput = document.getElementById('studio-duration-input');
        const durationChips = document.querySelectorAll('.duration-chip');

        const statusBadge = document.getElementById('studio-status-badge');
        const statusText = document.getElementById('studio-status-text');
        const validationHint = document.getElementById('validation-hint');
        const btnSave = document.getElementById('btn-save-question');
        const btnSaveSpinner = document.getElementById('btn-save-spinner');
        const btnSaveText = document.getElementById('btn-save-text');
        const btnDiscardDraft = document.getElementById('btn-discard-draft');

        // Presets & Formatting
        const btnPresetsToggle = document.getElementById('btn-presets-toggle');
        const presetsDropdown = document.getElementById('presets-dropdown');
        const presetItems = document.querySelectorAll('.preset-item');
        const btnFormatBold = document.getElementById('btn-format-bold');
        const btnFormatItalic = document.getElementById('btn-format-italic');
        const btnFormatInlineCode = document.getElementById('btn-format-inline-code');
        const btnClearPrompt = document.getElementById('btn-clear-prompt');

        // Tab Switcher (Mobile)
        const tabBtnEdit = document.getElementById('tab-btn-edit');
        const tabBtnPreview = document.getElementById('tab-btn-preview');
        const editorPanel = document.getElementById('studio-editor-panel');
        const previewPanel = document.getElementById('studio-preview-panel');

        // Preview elements
        const previewPromptText = document.getElementById('preview-prompt-text');
        const previewCodeContainer = document.getElementById('preview-code-container');
        const previewTimerDisplay = document.getElementById('preview-timer-display');

        let isCodeOpen = false;
        let currentSelectedCorrect = parseInt(correctOptionInput.value) || null;

        // --- Standard Templates per language ---
        const languageTemplates = {
            cpp: '#include <iostream>\nusing namespace std;\n\nint main() {\n    int x = 10;\n    cout << x * 2 << endl;\n    return 0;\n}',
            c: '#include <stdio.h>\n\nint main() {\n    int a = 5;\n    printf("%d\\n", a * 3);\n    return 0;\n}',
            python: 'def calculate(n):\n    result = [i ** 2 for i in range(n)]\n    return sum(result)\n\nprint(calculate(4))',
            java: 'public class Main {\n    public static void main(String[] args) {\n        String msg = "Hello";\n        System.out.println(msg.length());\n    }\n}',
            javascript: 'const numbers = [1, 2, 3, 4];\nconst doubled = numbers.map(n => n * 2);\nconsole.log(doubled);',
            typescript: 'interface User {\n    id: number;\n    name: string;\n}\n\nconst user: User = { id: 1, name: "Alice" };\nconsole.log(user.name);',
            sql: 'SELECT department, COUNT(*) AS total_employees\nFROM employees\nWHERE salary > 50000\nGROUP BY department\nHAVING COUNT(*) >= 2;',
            php: '// PHP code\n' + '$' + 'items = ["apple", "banana", "cherry"];\necho count(' + '$' + 'items);',
            rust: 'fn main() {\n    let x: i32 = 42;\n    println!("Answer: {}", x);\n}'
        };

        // --- 1. Initial Content Parsing (detect if existing question has code block) ---
        function initializeContent() {
            const raw = promptInput.value || '';
            const codeBlockRegex = /```([a-zA-Z0-9_+#-]*)\r?\n([\s\S]*?)```/;
            const match = raw.match(codeBlockRegex);

            if (match) {
                const lang = match[1].trim() || 'cpp';
                const code = match[2];
                // Extract prompt text without code block
                const cleanedPrompt = raw.replace(codeBlockRegex, '').trim();
                promptInput.value = cleanedPrompt;
                codeInput.value = code;
                codeLangSelect.value = lang.toLowerCase();
                setCodeOpen(true);
            }

            if (currentSelectedCorrect) {
                setCorrectOption(currentSelectedCorrect);
            }

            // Restore Local Draft if Add mode and draft exists
            if (!isEdit && !promptInput.value.trim()) {
                try {
                    const saved = localStorage.getItem(draftKey);
                    if (saved) {
                        const d = JSON.parse(saved);
                        if (d.prompt || d.code || d.opt1) {
                            promptInput.value = d.prompt || '';
                            if (d.code) {
                                codeInput.value = d.code;
                                codeLangSelect.value = d.lang || 'cpp';
                                setCodeOpen(true);
                            }
                            if (d.opt1) document.getElementById('studio-opt-1').value = d.opt1;
                            if (d.opt2) document.getElementById('studio-opt-2').value = d.opt2;
                            if (d.opt3) document.getElementById('studio-opt-3').value = d.opt3;
                            if (d.opt4) document.getElementById('studio-opt-4').value = d.opt4;
                            if (d.correct) setCorrectOption(parseInt(d.correct));
                            if (d.duration) durationInput.value = d.duration;
                        }
                    }
                } catch (e) {
                    console.warn("Draft restore failed:", e);
                }
            }

            updateCodeLineNumbers();
            updateDurationChips();
            updatePreview();
            validateStudio();
        }

        // --- 2. Code Block Controls ---
        function setCodeOpen(open) {
            isCodeOpen = open;
            if (open) {
                codeBlock.classList.remove('hidden');
                btnToggleCode.classList.add('bg-indigo-600/30', 'border-indigo-500/50', 'text-indigo-300');
                toggleCodeLabel.textContent = 'Code Active';
                if (!codeInput.value.trim()) {
                    codeInput.value = languageTemplates[codeLangSelect.value] || '// Write code here';
                }
                updateCodeLineNumbers();
            } else {
                codeBlock.classList.add('hidden');
                btnToggleCode.classList.remove('bg-indigo-600/30', 'border-indigo-500/50', 'text-indigo-300');
                toggleCodeLabel.textContent = '+ Code Snippet';
                codeInput.value = '';
            }
            updatePreview();
            validateStudio();
            saveDraft();
        }

        btnToggleCode.addEventListener('click', () => setCodeOpen(!isCodeOpen));
        btnRemoveCodeBlock.addEventListener('click', () => setCodeOpen(false));

        codeLangSelect.addEventListener('change', () => {
            updatePreview();
            saveDraft();
        });

        btnInsertTemplate.addEventListener('click', () => {
            const lang = codeLangSelect.value;
            if (languageTemplates[lang]) {
                codeInput.value = languageTemplates[lang];
                updateCodeLineNumbers();
                updatePreview();
                saveDraft();
            }
        });

        // Tab & Indentation support in Code Textarea
        codeInput.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = codeInput.selectionStart;
                const end = codeInput.selectionEnd;
                codeInput.value = codeInput.value.substring(0, start) + '    ' + codeInput.value.substring(end);
                codeInput.selectionStart = codeInput.selectionEnd = start + 4;
                updateCodeLineNumbers();
                updatePreview();
            }
        });

        function updateCodeLineNumbers() {
            const lines = (codeInput.value || '').split('\n').length;
            let nums = '';
            for (let i = 1; i <= Math.max(1, lines); i++) {
                nums += `<div>${i}</div>`;
            }
            codeLineNumbers.innerHTML = nums;
        }

        codeInput.addEventListener('input', () => {
            updateCodeLineNumbers();
            updatePreview();
            saveDraft();
        });

        // --- 4. Options & Correct Answer Selection ---
        function setCorrectOption(optNum) {
            currentSelectedCorrect = optNum;
            correctOptionInput.value = optNum;

            optionCards.forEach(card => {
                const num = parseInt(card.dataset.optNum);
                const dot = card.querySelector('.opt-radio-dot');
                const badge = card.querySelector('.opt-correct-badge');
                const ring = card.querySelector('.opt-radio-indicator');

                if (num === optNum) {
                    card.classList.add('ring-2', 'ring-emerald-500/80', 'bg-emerald-950/20', 'border-emerald-500/60');
                    card.classList.remove('bg-slate-950/70', 'border-slate-800');
                    ring.classList.add('border-emerald-400', 'bg-emerald-500/30');
                    dot.classList.add('bg-emerald-400');
                    badge.classList.remove('hidden');
                } else {
                    card.classList.remove('ring-2', 'ring-emerald-500/80', 'bg-emerald-950/20', 'border-emerald-500/60');
                    card.classList.add('bg-slate-950/70', 'border-slate-800');
                    ring.classList.remove('border-emerald-400', 'bg-emerald-500/30');
                    dot.classList.remove('bg-emerald-400');
                    badge.classList.add('hidden');
                }
            });

            updatePreview();
            validateStudio();
            saveDraft();
        }

        optionCards.forEach(card => {
            card.addEventListener('click', (e) => {
                if (e.target.classList.contains('opt-code-toggle')) return;
                const optNum = parseInt(card.dataset.optNum);
                setCorrectOption(optNum);
            });

            // Inline code button on option
            const toggleCodeBtn = card.querySelector('.opt-code-toggle');
            const input = card.querySelector('.opt-text-input');
            toggleCodeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                let val = input.value.trim();
                if (!val) {
                    input.value = '`code`';
                } else if (val.startsWith('`') && val.endsWith('`') && val.length >= 2) {
                    input.value = val.slice(1, -1);
                } else {
                    input.value = '`' + val + '`';
                }
                input.focus();
                updatePreview();
                saveDraft();
            });

            input.addEventListener('input', () => {
                updatePreview();
                validateStudio();
                saveDraft();
            });
        });

        // --- 5. Presets & Formatting Helpers ---
        btnPresetsToggle.addEventListener('click', () => {
            presetsDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', (e) => {
            if (!btnPresetsToggle.contains(e.target) && !presetsDropdown.contains(e.target)) {
                presetsDropdown.classList.add('hidden');
            }
        });

        presetItems.forEach(item => {
            item.addEventListener('click', () => {
                const text = item.dataset.text;
                promptInput.value = text;
                presetsDropdown.classList.add('hidden');
                promptInput.focus();
                updatePreview();
                validateStudio();
                saveDraft();
            });
        });

        function wrapPromptSelection(prefix, suffix) {
            const start = promptInput.selectionStart;
            const end = promptInput.selectionEnd;
            const sel = promptInput.value.substring(start, end) || 'text';
            promptInput.value = promptInput.value.substring(0, start) + prefix + sel + suffix + promptInput.value.substring(end);
            promptInput.focus();
            updatePreview();
            saveDraft();
        }

        btnFormatBold.addEventListener('click', () => wrapPromptSelection('**', '**'));
        btnFormatItalic.addEventListener('click', () => wrapPromptSelection('*', '*'));
        btnFormatInlineCode.addEventListener('click', () => wrapPromptSelection('`', '`'));
        btnClearPrompt.addEventListener('click', () => {
            promptInput.value = '';
            promptInput.focus();
            updatePreview();
            validateStudio();
            saveDraft();
        });

        promptInput.addEventListener('input', () => {
            updatePreview();
            validateStudio();
            saveDraft();
        });

        // --- 6. Duration Controls ---
        durationChips.forEach(chip => {
            chip.addEventListener('click', () => {
                durationInput.value = chip.dataset.sec;
                updateDurationChips();
                updatePreview();
                saveDraft();
            });
        });

        durationInput.addEventListener('input', () => {
            updateDurationChips();
            updatePreview();
            saveDraft();
        });

        function updateDurationChips() {
            const current = parseInt(durationInput.value);
            durationChips.forEach(c => {
                if (parseInt(c.dataset.sec) === current) {
                    c.classList.add('bg-indigo-600', 'text-white');
                    c.classList.remove('text-slate-300');
                } else {
                    c.classList.remove('bg-indigo-600', 'text-white');
                    c.classList.add('text-slate-300');
                }
            });
            previewTimerDisplay.textContent = (current || 60) + 's';
        }

        // --- 7. Tab Switching on Mobile ---
        tabBtnEdit.addEventListener('click', () => {
            tabBtnEdit.classList.add('bg-indigo-600', 'text-white');
            tabBtnEdit.classList.remove('text-slate-400');
            tabBtnPreview.classList.remove('bg-indigo-600', 'text-white');
            tabBtnPreview.classList.add('text-slate-400');
            editorPanel.classList.remove('hidden');
            previewPanel.classList.add('hidden');
        });

        tabBtnPreview.addEventListener('click', () => {
            tabBtnPreview.classList.add('bg-indigo-600', 'text-white');
            tabBtnPreview.classList.remove('text-slate-400');
            tabBtnEdit.classList.remove('bg-indigo-600', 'text-white');
            tabBtnEdit.classList.add('text-slate-400');
            editorPanel.classList.add('hidden');
            previewPanel.classList.remove('hidden');
            updatePreview();
        });

        // --- 8. Real-Time Student Preview Renderer ---
        function updatePreview() {
            const highlighter = window.QuizHighlighter || {
                formatInlineCode: (t) => t,
                renderCodeBoxHtml: (c, l) => `<pre class="bg-slate-900 text-emerald-400 p-3 rounded font-mono text-xs">${c}</pre>`,
                escapeHtml: (s) => s
            };

            // 1. Prompt Text
            const promptVal = promptInput.value.trim();
            if (promptVal) {
                previewPromptText.innerHTML = highlighter.formatInlineCode(highlighter.escapeHtml(promptVal));
            } else {
                previewPromptText.innerHTML = '<span class="text-slate-400 italic font-normal">Start typing your question prompt on the left...</span>';
            }

            // 2. Code Box
            if (isCodeOpen && codeInput.value.trim()) {
                previewCodeContainer.innerHTML = highlighter.renderCodeBoxHtml(codeInput.value, codeLangSelect.value);
                previewCodeContainer.classList.remove('hidden');
            } else {
                previewCodeContainer.innerHTML = '';
                previewCodeContainer.classList.add('hidden');
            }

            // 4. Options
            for (let i = 1; i <= 4; i++) {
                const optInput = document.getElementById(`studio-opt-${i}`);
                const previewOpt = document.getElementById(`preview-opt-${i}`);
                const optLabel = previewOpt.querySelector('.opt-label');
                const val = (optInput.value || '').trim();

                if (val) {
                    optLabel.innerHTML = highlighter.formatInlineCode(highlighter.escapeHtml(val));
                    optLabel.classList.remove('text-slate-400', 'italic');
                    optLabel.classList.add('text-slate-800');
                } else {
                    optLabel.textContent = `Option ${String.fromCharCode(64 + i)}`;
                    optLabel.classList.add('text-slate-400', 'italic');
                    optLabel.classList.remove('text-slate-800');
                }

                // Highlight correct answer in preview
                if (currentSelectedCorrect === i) {
                    previewOpt.classList.add('border-indigo-600', 'bg-indigo-50/80', 'text-indigo-900', 'font-bold');
                    previewOpt.classList.remove('border-slate-200', 'bg-slate-50');
                    previewOpt.querySelector('.w-4').classList.add('border-indigo-600', 'bg-indigo-600');
                } else {
                    previewOpt.classList.remove('border-indigo-600', 'bg-indigo-50/80', 'text-indigo-900', 'font-bold');
                    previewOpt.classList.add('border-slate-200', 'bg-slate-50');
                    previewOpt.querySelector('.w-4').classList.remove('border-indigo-600', 'bg-indigo-600');
                }
            }
        }

        // --- 9. Validation & Status Badge ---
        function validateStudio() {
            const hasPrompt = promptInput.value.trim().length > 0;
            const opt1 = document.getElementById('studio-opt-1').value.trim().length > 0;
            const opt2 = document.getElementById('studio-opt-2').value.trim().length > 0;
            const opt3 = document.getElementById('studio-opt-3').value.trim().length > 0;
            const opt4 = document.getElementById('studio-opt-4').value.trim().length > 0;
            const hasAllOptions = opt1 && opt2 && opt3 && opt4;
            const hasCorrectOption = !!currentSelectedCorrect;

            let ready = hasPrompt && hasAllOptions && hasCorrectOption;

            if (ready) {
                statusBadge.className = "px-2.5 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 bg-emerald-950/60 text-emerald-300 border border-emerald-700/50 shadow-sm";
                statusText.textContent = "● Ready to Save";
                validationHint.className = "flex items-center gap-1 text-emerald-400";
                validationHint.innerHTML = "<span>✓</span> All fields ready for submission";
                btnSave.disabled = false;
            } else {
                statusBadge.className = "px-2.5 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5 bg-amber-950/50 text-amber-300 border border-amber-800/40";
                
                let missing = [];
                if (!hasPrompt) missing.push("prompt");
                if (!hasAllOptions) missing.push("4 options");
                if (!hasCorrectOption) missing.push("correct answer");

                statusText.textContent = "Drafting...";
                validationHint.className = "flex items-center gap-1 text-amber-400";
                validationHint.innerHTML = `<span>⚠</span> Missing: ${missing.join(', ')}`;
                btnSave.disabled = false;
            }
        }

        // --- 10. Autosave Draft to LocalStorage ---
        function saveDraft() {
            if (isEdit) return; // do not overwrite draft during editing
            try {
                const draft = {
                    prompt: promptInput.value,
                    code: isCodeOpen ? codeInput.value : '',
                    lang: codeLangSelect.value,
                    opt1: document.getElementById('studio-opt-1').value,
                    opt2: document.getElementById('studio-opt-2').value,
                    opt3: document.getElementById('studio-opt-3').value,
                    opt4: document.getElementById('studio-opt-4').value,
                    correct: currentSelectedCorrect,
                    duration: durationInput.value,
                    updatedAt: new Date().toISOString()
                };
                localStorage.setItem(draftKey, JSON.stringify(draft));
            } catch (e) {
                console.warn("Draft save failed:", e);
            }
        }

        if (btnDiscardDraft) {
            btnDiscardDraft.addEventListener('click', () => {
                if (confirm('Discard your current in-progress draft?')) {
                    localStorage.removeItem(draftKey);
                    promptInput.value = '';
                    codeInput.value = '';
                    setCodeOpen(false);
                    document.getElementById('studio-opt-1').value = '';
                    document.getElementById('studio-opt-2').value = '';
                    document.getElementById('studio-opt-3').value = '';
                    document.getElementById('studio-opt-4').value = '';
                    setCorrectOption(null);
                    durationInput.value = '60';
                    updateDurationChips();
                    updatePreview();
                    validateStudio();
                }
            });
        }

        // --- 11. Form Submission & AJAX Handling ---
        form.onsubmit = async function(e) {
            e.preventDefault();

            // Construct composite question text containing markdown code block if code block is active
            let fullText = promptInput.value.trim();
            if (isCodeOpen && codeInput.value.trim()) {
                const lang = codeLangSelect.value || 'cpp';
                fullText = `${fullText}\n\n\`\`\`${lang}\n${codeInput.value.trim()}\n\`\`\``;
            }

            if (!fullText) {
                alert('Please enter a question prompt.');
                promptInput.focus();
                return;
            }

            if (!currentSelectedCorrect) {
                alert('Please click on one of the 4 answer choices to mark it as the correct option.');
                return;
            }

            // Create submission FormData
            const formData = new FormData(this);
            formData.set('question_text', fullText);
            formData.set('correct_option', currentSelectedCorrect);

            btnSave.disabled = true;
            btnSaveSpinner.classList.remove('hidden');
            btnSaveText.textContent = isEdit ? 'Updating...' : 'Saving...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();

                if (!response.ok) {
                    const errorMsg = result.errors 
                        ? Object.values(result.errors).flat().join('\n') 
                        : (result.message || 'Validation failed. Please check inputs.');
                    alert('Submission Error:\n' + errorMsg);
                    return;
                }

                // Success!
                if (!isEdit) {
                    // Clear local draft
                    localStorage.removeItem(draftKey);

                    // If parent page has question list, dispatch custom event or append
                    window.dispatchEvent(new CustomEvent('question-added', { detail: result }));

                    // Reset studio for next question
                    promptInput.value = '';
                    codeInput.value = '';
                    setCodeOpen(false);
                    document.getElementById('studio-opt-1').value = '';
                    document.getElementById('studio-opt-2').value = '';
                    document.getElementById('studio-opt-3').value = '';
                    document.getElementById('studio-opt-4').value = '';
                    setCorrectOption(null);
                    durationInput.value = '60';
                    updateDurationChips();
                    updatePreview();
                    validateStudio();

                    if (window.showToast) {
                        window.showToast('Question added successfully!', 'success');
                    }
                } else {
                    if (window.showToast) {
                        window.showToast('Question updated successfully!', 'success');
                    }
                    setTimeout(() => {
                        window.location.href = `/quiz/${quizId}/details`;
                    }, 500);
                }
            } catch (err) {
                console.error("Studio submission error:", err);
                alert("An unexpected error occurred while saving the question.");
            } finally {
                btnSave.disabled = false;
                btnSaveSpinner.classList.add('hidden');
                btnSaveText.textContent = isEdit ? 'Update Question' : 'Save Question';
            }
        };

        // --- 12. Desktop Keyboard Shortcuts ---
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                form.requestSubmit();
            } else if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                setCodeOpen(!isCodeOpen);
                if (isCodeOpen) codeInput.focus();
            }
        });

        // Initialize!
        initializeContent();
    });
})();
</script>
