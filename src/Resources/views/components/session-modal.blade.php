@props(['id' => 'session-modal', 'site' => null])

<div
    id="{{ $id }}"
    x-data="{
        open: false,
        sessionId: null,
        activeTab: 'timeline',
        get events() {
            return Alpine.store('sessionModal').events;
        },
        get hasPageviews() {
            return this.events.filter(e => e.event_name === 'pageview').length > 0;
        },
        get hasClicks() {
            return this.events.filter(e => e.event_name === 'click').length > 0;
        }
    }"
    x-show="open"
    x-cloak
    @open-session-modal.window="
        open = true;
        sessionId = $event.detail.sessionId;
        fetchSessionData(sessionId);
    "
    class="fixed inset-0 z-50 overflow-hidden"
    style="backdrop-filter: blur(5px);"
>
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Background overlay with blur effect -->
        <div
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-50"
            aria-hidden="true"
            @click="open = false"
        ></div>

        <!-- Modal panel -->
        <div
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-8"
            class="relative bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-2xl transform transition-all w-full max-w-4xl max-h-[85vh] flex flex-col"
        >
            <!-- Session Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gradient-to-r from-primary-500 to-primary-700 text-white">
                <div class="flex items-center space-x-3">
                    <div class="bg-white bg-opacity-20 p-2 rounded-lg">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold">Session Explorer</h3>
                        <div class="text-sm text-white text-opacity-80 font-mono truncate" x-text="sessionId"></div>
                    </div>
                </div>
                <button @click="open = false" class="rounded-full p-1.5 hover:bg-white hover:bg-opacity-20 transition-colors">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Loading state -->
            <div x-show="Alpine.store('sessionModal').loading" class="flex-1 flex items-center justify-center p-8">
                <div class="flex flex-col items-center space-y-4">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
                    <p class="text-gray-500 dark:text-gray-400">Loading session data...</p>
                </div>
            </div>

            <!-- Error state -->
            <div x-show="Alpine.store('sessionModal').error && !Alpine.store('sessionModal').loading" class="flex-1 flex items-center justify-center p-8">
                <div class="bg-red-50 dark:bg-red-900 dark:bg-opacity-20 text-red-700 dark:text-red-400 px-6 py-8 rounded-lg max-w-lg text-center">
                    <svg class="h-12 w-12 mx-auto text-red-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-lg font-semibold mb-2">Unable to Load Session Data</h3>
                    <p x-text="Alpine.store('sessionModal').error"></p>
                </div>
            </div>

            <!-- Content container when data is loaded -->
            <div x-show="events.length && !Alpine.store('sessionModal').loading && !Alpine.store('sessionModal').error" class="flex-1 flex flex-col">
                <!-- Session overview -->
                <div class="px-6 pt-4 pb-2">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Get first event for user info -->
                        <template x-if="events.length > 0">
                            <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="bg-primary-100 dark:bg-primary-900 p-3 rounded-lg mr-3">
                                    <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Visitor</div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100" x-text="events[0].device_type === 'desktop' ? 'Desktop User' : (events[0].device_type === 'mobile' ? 'Mobile User' : 'Tablet User')"></div>
                                </div>
                            </div>
                        </template>

                        <!-- Get total event count -->
                        <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="bg-primary-100 dark:bg-primary-900 p-3 rounded-lg mr-3">
                                <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Interactions</div>
                                <div class="font-medium text-gray-900 dark:text-gray-100" x-text="events.length + ' events'"></div>
                            </div>
                        </div>

                        <!-- Browser info -->
                        <template x-if="events.length > 0">
                            <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="bg-primary-100 dark:bg-primary-900 p-3 rounded-lg mr-3">
                                    <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Browser</div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">
                                        <span x-text="events[0].browser"></span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="' v' + events[0].browser_version"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="px-6 pt-4">
                    <div class="flex border-b border-gray-200 dark:border-gray-700">
                        <button
                            @click="activeTab = 'timeline'"
                            :class="activeTab === 'timeline' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300'"
                            class="py-2 px-4 font-medium text-sm border-b-2 whitespace-nowrap flex items-center space-x-1"
                        >
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Activity Timeline</span>
                        </button>
                        <button
                            @click="activeTab = 'pages'"
                            :class="activeTab === 'pages' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300'"
                            class="py-2 px-4 font-medium text-sm border-b-2 whitespace-nowrap flex items-center space-x-1"
                            x-show="hasPageviews"
                        >
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Pages Viewed</span>
                        </button>
                        <button
                            @click="activeTab = 'clicks'"
                            :class="activeTab === 'clicks' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300'"
                            class="py-2 px-4 font-medium text-sm border-b-2 whitespace-nowrap flex items-center space-x-1"
                            x-show="hasClicks"
                        >
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                            </svg>
                            <span>Click Events</span>
                        </button>
                        <button
                            @click="activeTab = 'technical'"
                            :class="activeTab === 'technical' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300'"
                            class="py-2 px-4 font-medium text-sm border-b-2 whitespace-nowrap flex items-center space-x-1"
                        >
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Technical Details</span>
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="flex-1 overflow-y-auto p-6 max-h-[calc(85vh-160px)]">
                    <!-- Timeline Tab -->
                    <div x-show="activeTab === 'timeline'" class="space-y-4">
                        <div class="relative pl-8 pb-1">
                            <div class="absolute top-0 left-3 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

                            <template x-for="(event, index) in events" :key="index">
                                <div class="relative mb-6">
                                    <!-- Timeline dot -->
                                    <div class="absolute -left-5 mt-1.5">
                                        <div
                                            :class="{
                                                'bg-blue-500': event.event_name === 'pageview',
                                                'bg-green-500': event.event_name === 'click',
                                                'bg-purple-500': !['pageview', 'click'].includes(event.event_name)
                                            }"
                                            class="w-5 h-5 rounded-full border-2 border-white dark:border-gray-800 shadow"
                                        ></div>
                                    </div>

                                    <!-- Event card -->
                                    <div
                                        :class="{
                                            'border-l-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900 dark:hover:bg-opacity-10': event.event_name === 'pageview',
                                            'border-l-green-500 hover:bg-green-50 dark:hover:bg-green-900 dark:hover:bg-opacity-10': event.event_name === 'click',
                                            'border-l-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900 dark:hover:bg-opacity-10': !['pageview', 'click'].includes(event.event_name)
                                        }"
                                        class="border-l-4 rounded-r-lg bg-white dark:bg-gray-800 shadow-sm hover:shadow transition-all px-4 py-3"
                                    >
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <div class="flex items-center">
                                                    <div
                                                        :class="{
                                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:bg-opacity-30 dark:text-blue-300': event.event_name === 'pageview',
                                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:bg-opacity-30 dark:text-green-300': event.event_name === 'click',
                                                            'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:bg-opacity-30 dark:text-purple-300': !['pageview', 'click'].includes(event.event_name)
                                                        }"
                                                        class="text-xs py-1 px-2 rounded-full capitalize font-medium"
                                                    >
                                                        <span x-text="event.event_name"></span>
                                                    </div>

                                                    <span class="mx-2 text-gray-400">•</span>

                                                    <span class="text-sm text-gray-500 dark:text-gray-400" x-text="new Date(event.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'})"></span>
                                                </div>

                                                <!-- Event specific content -->
                                                <div class="mt-2">
                                                    <!-- Pageview event -->
                                                    <template x-if="event.event_name === 'pageview'">
                                                        <div>
                                                            <div class="flex items-center mt-1">
                                                                <svg class="h-4 w-4 text-gray-500 dark:text-gray-400 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                                </svg>
                                                                <a href="#" class="text-primary-600 dark:text-primary-400 hover:underline break-all" x-text="event.url"></a>
                                                            </div>
                                                            <div class="ml-5 mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                                <span x-text="event.properties && event.properties.title ? event.properties.title : 'Page Title Not Available'"></span>
                                                            </div>
                                                            <div x-show="event.properties && event.properties.referrer" class="flex items-center ml-5 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                                <span>Referred from: </span>
                                                                <span class="ml-1 text-gray-600 dark:text-gray-300 truncate" x-text="event.properties.referrer"></span>
                                                            </div>
                                                        </div>
                                                    </template>

                                                    <!-- Click event -->
                                                    <template x-if="event.event_name === 'click'">
                                                        <div>
                                                            <div class="flex items-center">
                                                                <template x-if="event.properties && event.properties.tag">
                                                                    <div class="text-gray-600 dark:text-gray-300">
                                                                        <span>Clicked </span>
                                                                        <span
                                                                            class="inline-block px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs rounded font-mono"
                                                                            x-text="'<' + event.properties.tag + '>'"
                                                                        ></span>
                                                                        <template x-if="event.properties && event.properties.text">
                                                                            <span>
                                                                                <span> with text </span>
                                                                                <span class="font-medium" x-text="'\"' + event.properties.text + '\"'"></span>
                                                                            </span>
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                            </div>

                                                            <div class="flex items-center mt-1 ml-5 text-xs text-gray-500 dark:text-gray-400">
                                                                <svg class="h-3.5 w-3.5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                                </svg>
                                                                <span class="text-primary-600 dark:text-primary-400 truncate" x-text="event.url"></span>
                                                            </div>
                                                        </div>
                                                    </template>

                                                    <!-- Other event types -->
                                                    <template x-if="!['pageview', 'click'].includes(event.event_name)">
                                                        <div class="text-gray-600 dark:text-gray-300">
                                                            <div class="flex items-center mt-1">
                                                                <svg class="h-4 w-4 text-gray-500 dark:text-gray-400 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                                </svg>
                                                                <a href="#" class="text-primary-600 dark:text-primary-400 hover:underline break-all" x-text="event.url"></a>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            <!-- Event details button -->
                                            <div
                                                x-data="{ showDetails: false }"
                                            >
                                                <button
                                                    @click="showDetails = !showDetails"
                                                    class="text-xs flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 mt-2"
                                                >
                                                    <span x-text="showDetails ? 'Hide details' : 'Show details'"></span>
                                                    <svg
                                                        x-show="!showDetails"
                                                        class="ml-1 h-4 w-4"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 20 20"
                                                        fill="currentColor"
                                                    >
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                    <svg
                                                        x-show="showDetails"
                                                        class="ml-1 h-4 w-4"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 20 20"
                                                        fill="currentColor"
                                                    >
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>

                                                <div x-show="showDetails" x-transition class="mt-2">
                                                    <pre class="text-xs bg-gray-100 dark:bg-gray-900 p-3 rounded overflow-x-auto"><code x-text="JSON.stringify(event.properties || {}, null, 2)"></code></pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Pages Tab -->
                    <div x-show="activeTab === 'pages'" class="space-y-4">
                        <template x-for="(event, index) in events.filter(e => e.event_name === 'pageview')" :key="index">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-l-4 border-blue-500 mb-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-medium text-gray-900 dark:text-gray-100">
                                            <span x-text="event.properties && event.properties.title ? event.properties.title : 'Unknown Page'"></span>
                                        </h3>
                                        <a href="#" class="text-primary-600 dark:text-primary-400 text-sm break-all" x-text="event.url"></a>

                                        <div class="flex flex-wrap items-center mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            <div class="flex items-center mr-4">
                                                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span x-text="new Date(event.created_at).toLocaleString()"></span>
                                            </div>

                                            <template x-if="event.properties && event.properties.referrer">
                                                <div class="flex items-center">
                                                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                    <span>From: </span>
                                                    <span class="ml-1 text-gray-600 dark:text-gray-300 truncate" x-text="event.properties.referrer"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="bg-blue-100 dark:bg-blue-900 dark:bg-opacity-30 p-2 rounded-lg" title="Page view">
                                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="!hasPageviews" class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <svg class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p>No page views recorded for this session.</p>
                        </div>
                    </div>

                    <!-- Clicks Tab -->
                    <div x-show="activeTab === 'clicks'" class="space-y-4">
                        <template x-for="(event, index) in events.filter(e => e.event_name === 'click')" :key="index">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-l-4 border-green-500 mb-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <template x-if="event.properties && event.properties.tag">
                                                <div class="text-gray-800 dark:text-gray-100 flex items-center">
                                                    <span>Clicked </span>
                                                    <span
                                                        class="inline-block px-2 py-0.5 mx-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs rounded font-mono"
                                                        x-text="'<' + event.properties.tag + '>'"
                                                    ></span>
                                                    <template x-if="event.properties && event.properties.text">
                                                        <span>
                                                            <span> with text </span>
                                                            <span class="font-medium text-green-600 dark:text-green-400" x-text="'\"' + event.properties.text + '\"'"></span>
                                                        </span>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>

                                        <div class="mt-1">
                                            <a href="#" class="text-primary-600 dark:text-primary-400 text-sm hover:underline break-all" x-text="event.url"></a>
                                        </div>

                                        <div class="flex items-center mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span x-text="new Date(event.created_at).toLocaleString()"></span>
                                        </div>

                                        <template x-if="event.properties && (event.properties.id || event.properties.class || event.properties.href)">
                                            <div class="mt-2 grid grid-cols-2 gap-2">
                                                <template x-if="event.properties.id">
                                                    <div class="text-xs">
                                                        <span class="text-gray-500 dark:text-gray-400">ID: </span>
                                                        <span class="font-mono text-gray-800 dark:text-gray-200" x-text="event.properties.id"></span>
                                                    </div>
                                                </template>

                                                <template x-if="event.properties.class">
                                                    <div class="text-xs">
                                                        <span class="text-gray-500 dark:text-gray-400">Class: </span>
                                                        <span class="font-mono text-gray-800 dark:text-gray-200" x-text="event.properties.class"></span>
                                                    </div>
                                                </template>

                                                <template x-if="event.properties.href">
                                                    <div class="text-xs col-span-2">
                                                        <span class="text-gray-500 dark:text-gray-400">Href: </span>
                                                        <span class="font-mono text-primary-600 dark:text-primary-400" x-text="event.properties.href"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>

                                    <div class="bg-green-100 dark:bg-green-900 dark:bg-opacity-30 p-2 rounded-lg" title="Click event">
                                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="!hasClicks" class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <svg class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                            </svg>
                            <p>No click events recorded for this session.</p>
                        </div>
                    </div>

                    <!-- Technical Details Tab -->
                    <div x-show="activeTab === 'technical'" class="space-y-6">
                        <template x-if="events.length > 0">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Session Technical Details</h3>

                                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5 border border-gray-200 dark:border-gray-700">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Device Information</h4>

                                            <div class="space-y-2">
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">Device Type</span>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="events[0].device_type || 'Unknown'"></span>
                                                </div>

                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">Operating System</span>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="events[0].os + (events[0].os_version ? ' ' + events[0].os_version : '') || 'Unknown'"></span>
                                                </div>

                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">Browser</span>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="events[0].browser + ' ' + events[0].browser_version || 'Unknown'"></span>
                                                </div>

                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">Language</span>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="events[0].language || 'Unknown'"></span>
                                                </div>

                                                <template x-if="events[0].screen_width && events[0].screen_height">
                                                    <div class="flex justify-between">
                                                        <span class="text-sm text-gray-500 dark:text-gray-400">Screen Resolution</span>
                                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="events[0].screen_width + 'x' + events[0].screen_height"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Location Information</h4>

                                            <div class="space-y-2">
                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">IP Address</span>
                                                    <span class="text-sm font-mono text-gray-900 dark:text-gray-100" x-text="events[0].ip_address || 'Unknown'"></span>
                                                </div>

                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">Country</span>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="events[0].country || 'Unknown'"></span>
                                                </div>

                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">Region</span>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="events[0].region || 'Unknown'"></span>
                                                </div>

                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">City</span>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="events[0].city || 'Unknown'"></span>
                                                </div>

                                                <div class="flex justify-between">
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">User ID</span>
                                                    <span class="text-sm font-mono text-gray-900 dark:text-gray-100 truncate max-w-[200px]" x-text="events[0].user_id || 'Unknown'"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">User Agent</h4>
                                    <div class="bg-gray-100 dark:bg-gray-900 p-3 rounded-md overflow-x-auto">
                                        <pre class="text-xs text-gray-800 dark:text-gray-200" x-text="events[0].user_agent || 'Not available'"></pre>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Footer with close button -->
            <div class="bg-gray-50 dark:bg-gray-900 px-6 py-3 flex justify-end border-t border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    @click="open = false"
                    class="inline-flex justify-center items-center rounded-md bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
                >
                    <svg class="h-4 w-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize the Alpine store before it's used
    document.addEventListener('alpine:init', () => {
        Alpine.store('sessionModal', {
            events: [],
            loading: false,
            error: null
        });
    });

    function fetchSessionData(sessionId) {
        if (!sessionId) return;

        const store = Alpine.store('sessionModal');
        store.loading = true;
        store.error = null;
        store.events = [];

        const url = "{{ route('candle.sessions.show', ['session_id' => '__SESSION_ID__']) }}"
            .replace('__SESSION_ID__', sessionId);

        // Ensure we have the X-Dashboard-Request header
        const requestOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                'X-Dashboard-Request': 'true'
            },
            credentials: 'same-origin'
        };
        // ge the site_id from the query string using js
        const siteId = new URLSearchParams(window.location.search).get('site_id');
        fetch(url + `?site_id=${siteId}`, requestOptions)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.data) {
                    store.events = data.data;
                    console.log('Events loaded:', store.events.length);
                } else {
                    store.error = 'No session data available';
                }
                store.loading = false;
            })
            .catch(error => {
                console.error('Error fetching session data:', error);
                store.error = `Failed to fetch session data: ${error.message}`;
                store.loading = false;
                if (typeof window.toast === 'function') {
                    window.toast('Failed to fetch session data', 'error');
                }
            });
    }
</script>
@endpush
