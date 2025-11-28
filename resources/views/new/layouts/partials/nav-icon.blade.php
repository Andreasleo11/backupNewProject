@switch($name)
    @case('home')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M11.47 2.47a.75.75 0 00-1.06 0l-7 7A.75.75 0 003.75 11H4v5.25A1.75 1.75 0 005.75 18h3.5a.75.75 0 00.75-.75V13h2.5v4.25a.75.75 0 00.75.75h3.5A1.75 1.75 0 0018 16.25V11h.25a.75.75 0 00.53-1.28l-7-7z" />
        </svg>
        @break

    @case('shield')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                  d="M10.34 2.13a.75.75 0 00-.68 0l-5.5 2.75A.75.75 0 003.75 5.5v4.25a6.75 6.75 0 004.18 6.2l1.62.65a.75.75 0 00.5 0l1.62-.65a6.75 6.75 0 004.18-6.2V5.5a.75.75 0 00-.41-.62l-5.5-2.75z"
                  clip-rule="evenodd" />
        </svg>
        @break

    @case('key')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M14.75 2a3.75 3.75 0 00-2.85 6.17l-6.26 6.26a.75.75 0 00-.22.53V17h2.04a.75.75 0 00.53-.22l1.72-1.72h1.5a.75.75 0 00.75-.75v-1.5l2.26-2.26A3.75 3.75 0 1014.75 2zm0 1.5a2.25 2.25 0 11-2.24 2.25A2.25 2.25 0 0114.75 3.5z" />
        </svg>
        @break

    @case('users')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M6 6a3 3 0 116 0 3 3 0 01-6 0zM4 12.5A2.5 2.5 0 016.5 10h7a2.5 2.5 0 012.5 2.5V14a2 2 0 01-2 2H6a2 2 0 01-2-2v-1.5z" />
        </svg>
        @break

    @case('lock')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                  d="M10 2a3.5 3.5 0 00-3.5 3.5V7H5a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2V9a2 2 0 00-2-2h-1.5V5.5A3.5 3.5 0 0010 2zm-2 5V5.5a2 2 0 114 0V7H8z"
                  clip-rule="evenodd" />
        </svg>
        @break

    @case('database')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M4 5c0-1.657 2.686-3 6-3s6 1.343 6 3-2.686 3-6 3-6-1.343-6-3zm0 3.75C4 9.991 6.686 11 10 11s6-1.009 6-2.25V12c0 1.24-2.686 2.25-6 2.25S4 13.24 4 12V8.75zm0 4.5C4 15.991 6.686 17 10 17s6-1.009 6-2.25V16c0 1.24-2.686 2.25-6 2.25S4 17.24 4 16v-2.75z" />
        </svg>
        @break

    @case('building')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                  d="M6 2a2 2 0 00-2 2v12H3a1 1 0 100 2h14a1 1 0 100-2h-1V7a2 2 0 00-2-2h-3V4a2 2 0 00-2-2H6zm3 3H7v2h2V5zm2 0h2v2h-2V5zM7 9h2v2H7V9zm4 0h2v2h-2V9zm-4 4h2v2H7v-2zm4 0h2v2h-2v-2z"
                  clip-rule="evenodd" />
        </svg>
        @break

    @case('wrench')
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M14.7 3.3a4 4 0 01-5.02 5.02l-3.9 3.9a1.5 1.5 0 002.12 2.12l3.9-3.9A4 4 0 1114.7 3.3z" />
        </svg>
        @break

    @default
        {{-- fallback dot --}}
        <div class="h-1.5 w-1.5 rounded-full bg-current"></div>
@endswitch
