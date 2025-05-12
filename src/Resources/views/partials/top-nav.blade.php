<header class="flex-shrink-0 relative h-16 bg-white shadow">
    <div class="flex justify-between items-center h-full px-4 sm:px-6 lg:px-8">
        <!-- Mobile menu button -->
        <button
            @click="sidebarOpen = true"
            type="button"
            class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500"
        >
            <span class="sr-only">Open sidebar</span>
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Site & Period Selectors -->
        <div class="flex items-center space-x-4">
            @if (isset($sites) && count($sites) > 0)
            <div class="relative" x-data="{ open: false }">
                <button
                    @click="open = !open"
                    type="button"
                    class="flex items-center space-x-2 text-sm border border-gray-300 rounded-md bg-white py-2 pl-3 pr-10 text-left hover:border-gray-400 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                >
                    <span class="truncate max-w-xs">{{ $site->name }}</span>
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    @click.away="open = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md py-1 max-h-60 overflow-auto focus:outline-none text-sm"
                    x-cloak
                >
                    @foreach($sites as $siteOption)
                    <a
                        href="{{ route('candle.dashboard', ['site_id' => $siteOption->id]) }}"
                        class="{{ $site->id == $siteOption->id ? 'bg-primary-50 text-primary-700' : 'text-gray-900 hover:bg-gray-100' }} cursor-pointer block px-4 py-2"
                    >
                        {{ $siteOption->name }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="relative" x-data="{ open: false }">
                <button
                    @click="open = !open"
                    type="button"
                    class="flex items-center space-x-2 text-sm border border-gray-300 rounded-md bg-white py-2 pl-3 pr-10 text-left hover:border-gray-400 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                >
                    <span id="period-display">Last 30 Days</span>
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    @click.away="open = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 z-10 mt-1 w-56 bg-white shadow-lg rounded-md py-1 focus:outline-none text-sm"
                    x-cloak
                >
                    <a href="#" class="period-option block px-4 py-2 text-gray-900 hover:bg-gray-100" data-value="today" data-display="Today">Today</a>
                    <a href="#" class="period-option block px-4 py-2 text-gray-900 hover:bg-gray-100" data-value="yesterday" data-display="Yesterday">Yesterday</a>
                    <a href="#" class="period-option block px-4 py-2 text-gray-900 hover:bg-gray-100" data-value="week" data-display="This Week">This Week</a>
                    <a href="#" class="period-option block px-4 py-2 text-gray-900 hover:bg-gray-100" data-value="month" data-display="This Month">This Month</a>
                    <a href="#" class="period-option block px-4 py-2 text-gray-900 hover:bg-gray-100" data-value="30days" data-display="Last 30 Days">Last 30 Days</a>
                    <a href="#" class="period-option block px-4 py-2 text-gray-900 hover:bg-gray-100" data-value="90days" data-display="Last 90 Days">Last 90 Days</a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <a href="#" class="period-option block px-4 py-2 text-gray-900 hover:bg-gray-100" data-value="custom" data-display="Custom Range">Custom Range</a>
                </div>
            </div>
        </div>

        <!-- Right side user menu -->
        <div class="flex items-center" x-data="{ userMenu: false }">
            <!-- Add site button -->
            <a href="{{ route('candle.sites.create') }}" class="mr-4 flex items-center text-sm text-primary-600 hover:text-primary-800 transition-colors duration-200">
                <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Site
            </a>

            <!-- User menu -->
            <div class="ml-4 relative">
                <button
                    @click="userMenu = !userMenu"
                    type="button"
                    class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
                >
                    <span class="sr-only">Open user menu</span>
                    <svg class="h-6 w-6 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </button>

                <div
                    x-show="userMenu"
                    @click.away="userMenu = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                    x-cloak
                >
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                    {{-- <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Sign out
                        </button>
                    </form> --}}
                </div>
            </div>
        </div>
    </div>
</header>
