<!-- Sidebar content wrapper -->
<div class="flex flex-col flex-grow pt-5 pb-4 overflow-y-auto">
    <!-- Logo -->
    <div class="flex items-center flex-shrink-0 px-4">
        <div class="flex items-center">
            <svg class="h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
            <span class="ml-2 text-xl font-bold text-gray-900">Candle</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="mt-8 flex-1 px-2 bg-white space-y-2">
        <!-- Dashboard Link -->
        <a
            href="{{ route('candle.dashboard') }}"
            class="{{ request()->routeIs('candle.dashboard') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}
                  group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-all duration-200"
        >
            <svg
                class="{{ request()->routeIs('candle.dashboard') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500' }}
                      mr-3 flex-shrink-0 h-6 w-6 transition-colors duration-200"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>

        <!-- Sites Link -->
        <a
            href="{{ route('candle.sites.index') }}"
            class="{{ request()->routeIs('candle.sites.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}
                  group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-all duration-200"
        >
            <svg
                class="{{ request()->routeIs('candle.sites.*') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500' }}
                      mr-3 flex-shrink-0 h-6 w-6 transition-colors duration-200"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Sites
        </a>

        <!-- API Keys Link -->
        <a
            href="{{ route('candle.api-keys.index') }}"
            class="{{ request()->routeIs('candle.api-keys.*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}
                  group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-all duration-200"
        >
            <svg
                class="{{ request()->routeIs('candle.api-keys.*') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500' }}
                      mr-3 flex-shrink-0 h-6 w-6 transition-colors duration-200"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            API Keys
        </a>

        <!-- Settings Link -->
        <a
            href="{{ route('candle.sites.edit', ['site' => $site->id ?? 1]) }}"
            class="{{ request()->routeIs('candle.sites.edit') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}
                  group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-all duration-200"
        >
            <svg
                class="{{ request()->routeIs('candle.sites.edit') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500' }}
                      mr-3 flex-shrink-0 h-6 w-6 transition-colors duration-200"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Settings
        </a>
    </nav>

    <!-- Documentation Link -->
    <div class="mt-auto px-4 py-4 border-t border-gray-200">
        <a href="#" class="group flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors duration-200">
            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Documentation
        </a>
    </div>
</div>
