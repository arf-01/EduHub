{{--
    EduHub Shared Navigation Component
    ===================================
    Props:
      $role    : 'teacher' | 'student' | null  (default: null)
      $quizTitle: optional string to show as breadcrumb context
--}}

@props([
    'role'      => null,
    'quizTitle' => null,
])

<nav class="edu-nav">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 py-3.5 flex items-center justify-between gap-4">

        {{-- Logo --}}
        <a href="/" class="flex items-center gap-2.5 shrink-0 group" id="nav-logo">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition-transform">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <span class="text-lg font-extrabold tracking-tight" style="background: linear-gradient(135deg, #a5b4fc 0%, #818cf8 50%, #c4b5fd 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                EduHub
            </span>
        </a>

        {{-- Breadcrumb context (optional) --}}
        @if($quizTitle)
            <div class="hidden md:flex items-center gap-2 text-sm text-slate-400 truncate">
                <span class="text-slate-600">/</span>
                <span class="truncate max-w-xs text-slate-300 font-medium">{{ $quizTitle }}</span>
            </div>
        @endif

        {{-- Nav links + actions --}}
        <div class="flex items-center gap-1 sm:gap-2">

            @if($role === 'teacher')
                <a href="{{ route('quiz.list') }}" id="nav-my-quizzes"
                   class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    My Quizzes
                </a>

                <a href="{{ route('teacher.view') }}" id="nav-add-quiz"
                   class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Quiz
                </a>

                {{-- Divider --}}
                <span class="hidden sm:block w-px h-5 bg-slate-700 mx-1"></span>

                <a href="{{ route('logout') }}" id="nav-logout"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition-all border border-transparent hover:border-rose-500/30">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="hidden sm:inline">Logout</span>
                </a>

            @elseif($role === 'student')
                <a href="/" id="nav-home"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="hidden sm:inline">Home</span>
                </a>

            @else
                {{-- Default (landing / auth pages) --}}
                <a href="/" id="nav-home-default"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-white/5 transition-all">
                    Home
                </a>
            @endif

        </div>
    </div>
</nav>
