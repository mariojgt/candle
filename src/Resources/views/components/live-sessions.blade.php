@props(['site'])

<div
    x-data="liveSessions()"
    x-init="init"
    class="bg-white overflow-hidden shadow rounded-lg animate-on-scroll"
>
    <div class="px-4 py-5 sm:p-6">
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Sessions</h3>
                <span
                    x-show="isLive"
                    class="ml-2 flex h-3 w-3"
                    x-cloak
                >
                    <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </span>
                <span
                    x-show="!isLive"
                    class="ml-2 text-xs text-gray-500"
                    x-cloak
                >
                    Paused
                </span>
            </div>

            <div class="flex items-center space-x-2">
                <button
                    @click="toggleLive"
                    class="text-xs px-2 py-1 rounded border"
                    :class="isLive ? 'border-red-300 text-red-600 hover:bg-red-50' : 'border-green-300 text-green-600 hover:bg-green-50'"
                    x-text="isLive ? 'Pause' : 'Resume'"
                ></button>

                <button
                    @click="fetchSessions"
                    class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-50"
                >
                    Refresh
                </button>
            </div>
        </div>

        <div x-show="loading && !sessions.length" class="py-4 text-center text-sm text-gray-500">
            <div class="flex items-center justify-center">
                <div class="animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-primary-500 mr-2"></div>
                Loading sessions...
            </div>
        </div>

        <div x-show="!loading && !sessions.length" class="py-4 text-center text-sm text-gray-500">
            No sessions found.
        </div>

        <ul class="divide-y divide-gray-200">
            <template x-for="(session, index) in sessions" :key="session.session_id">
                <li
                    class="py-3 flex justify-between hover:bg-gray-50 cursor-pointer transition-colors duration-200 rounded-md px-2"
                    :class="{'slide-in': index < 3 && firstLoad}"
                    style="animation-delay: calc(0.1s * var(--i))"
                    :style="{'--i': index}"
                    @click="openSessionDetails(session.session_id)"
                >
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <template x-if="session.device_type === 'mobile'">
                                <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </template>
                            <template x-if="session.device_type === 'tablet'">
                                <svg class="h-5 w-5 text-purple-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </template>
                            <template x-if="session.device_type === 'desktop' || !session.device_type">
                                <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </template>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-900 truncate" x-text="formatSessionId(session.session_id)"></p>
                            <div class="flex items-center mt-1">
                                <span class="text-xs text-gray-500 mr-2" x-text="formatTime(session.most_recent)"></span>
                                <span class="text-xs bg-primary-100 text-primary-800 rounded-full px-2 py-0.5" x-text="`${session.events} events`"></span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <span
                            x-show="isSessionActive(session.most_recent)"
                            class="inline-flex h-2 w-2"
                        >
                            <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                    </div>
                </li>
            </template>
        </ul>
    </div>
</div>

@push('scripts')
<script>
    function liveSessions() {
        return {
            sessions: [],
            loading: true,
            isLive: true,
            fetchInterval: null,
            firstLoad: true,

            init() {
                this.fetchSessions();
                this.startLiveUpdates();
            },

            startLiveUpdates() {
                this.fetchInterval = setInterval(() => {
                    if (this.isLive) {
                        this.fetchSessions(true);
                    }
                }, 15000); // 15 seconds refresh
            },

            toggleLive() {
                this.isLive = !this.isLive;
                if (this.isLive && !this.fetchInterval) {
                    this.startLiveUpdates();
                }
            },

            fetchSessions(isSilent = false) {
                if (!isSilent) {
                    this.loading = true;
                }

                const url = "{{ route('candle.sessions') }}?site_id={{ $site->id }}";

                fetch(url, window.ajaxSetup())
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            // Sort by most recent first
                            this.sessions = data.data.sort((a, b) => {
                                return new Date(b.most_recent || 0) - new Date(a.most_recent || 0);
                            }).slice(0, 10); // Limit to 10 sessions
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching sessions:', error);
                    })
                    .finally(() => {
                        this.loading = false;
                        this.firstLoad = false;
                    });
            },

            formatSessionId(sessionId) {
                if (!sessionId) return 'Unknown Session';
                if (sessionId.length > 12) {
                    return sessionId.substring(0, 8) + '...';
                }
                return sessionId;
            },

            formatTime(timestamp) {
                if (!timestamp) return '';
                const date = new Date(timestamp);
                const now = new Date();
                const diff = Math.floor((now - date) / 1000); // Seconds ago

                if (diff < 60) return 'Just now';
                if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
                if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;

                return date.toLocaleDateString();
            },

            isSessionActive(timestamp) {
                if (!timestamp) return false;
                const date = new Date(timestamp);
                const now = new Date();
                const diff = Math.floor((now - date) / 1000); // Seconds ago

                return diff < 300; // 5 minutes
            },

            openSessionDetails(sessionId) {
                window.dispatchEvent(new CustomEvent('open-session-modal', {
                    detail: { sessionId }
                }));
            }
        };
    }
</script>
@endpush
