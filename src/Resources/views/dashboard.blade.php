<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Candle Dashboard</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex flex-col h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold text-gray-800">Candle</h1>
                        </div>
                        <nav class="ml-6 flex space-x-8">
                            <a href="{{ route('candle.dashboard') }}" class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Dashboard
                            </a>
                            <a href="{{ route('candle.sites.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Sites
                            </a>
                            <a href="{{ route('candle.api-keys.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                API Keys
                            </a>
                        </nav>
                    </div>
                    <div class="flex items-center">
                        @if (isset($sites) && count($sites) > 0)
                        <div class="relative">
                            <select id="site-selector" class="block appearance-none bg-white border border-gray-300 hover:border-gray-400 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($sites as $siteOption)
                                <option value="{{ $siteOption->id }}" {{ $site->id == $siteOption->id ? 'selected' : '' }}>
                                    {{ $siteOption->name }}
                                </option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                        @endif

                        <div class="ml-4 relative">
                            <select id="period-selector" class="block appearance-none bg-white border border-gray-300 hover:border-gray-400 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="30days" selected>Last 30 Days</option>
                                <option value="90days">Last 90 Days</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Metrics Overview -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <!-- Pageviews Card -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 rounded-md p-3 bg-indigo-500">
                                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                Pageviews
                                            </dt>
                                            <dd>
                                                <div class="text-lg font-medium text-gray-900" id="total-pageviews">--</div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Unique Visitors Card -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 rounded-md p-3 bg-green-500">
                                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                Unique Visitors
                                            </dt>
                                            <dd>
                                                <div class="text-lg font-medium text-gray-900" id="unique-visitors">--</div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bounce Rate Card -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 rounded-md p-3 bg-red-500">
                                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                Bounce Rate
                                            </dt>
                                            <dd>
                                                <div class="text-lg font-medium text-gray-900" id="bounce-rate">--</div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Avg. Session Duration Card -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 rounded-md p-3 bg-yellow-500">
                                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                Avg. Session Duration
                                            </dt>
                                            <dd>
                                                <div class="text-lg font-medium text-gray-900" id="avg-session-duration">--</div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="mt-8 grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <!-- Traffic Chart -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Visitors Over Time</h3>
                                <div class="mt-4 h-64">
                                    <canvas id="visitors-chart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Devices Chart -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Devices</h3>
                                <div class="mt-4 h-64 flex items-center justify-center">
                                    <canvas id="devices-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Pages & Referrers -->
                    <div class="mt-8 grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <!-- Top Pages Table -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Top Pages</h3>
                                <div class="mt-4 max-h-96 overflow-y-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Page
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Pageviews
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200" id="top-pages-table">
                                            <tr>
                                                <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                                                    Loading...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Referrers Table -->
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Top Referrers</h3>
                                <div class="mt-4 max-h-96 overflow-y-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Source
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Visitors
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200" id="referrers-table">
                                            <tr>
                                                <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                                                    Loading...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Live Sessions Section -->
        <div class="mt-8 bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Recent Sessions <span id="live-indicator" class="text-xs text-green-600 ml-2">Live</span></h3>
            <button class="text-sm text-blue-600 hover:underline" onclick="fetchSessions()">Refresh</button>
        </div>
        <ul id="sessions-list" class="divide-y divide-gray-200">
            <li class="py-2 text-sm text-gray-700">Loading sessions...</li>
        </ul>
        </div>

        <!-- Session Modal -->
        <div id="session-modal" class="fixed inset-0 bg-black bg-opacity-40 hidden z-50">
        <div class="absolute right-0 top-0 w-full max-w-lg h-full bg-white p-6 overflow-y-auto">
            <button class="text-red-500 text-sm mb-4" onclick="closeSessionModal()">Close</button>
            <h4 class="text-lg font-bold mb-2">Session Details</h4>
            <div id="session-events" class="space-y-3 text-sm text-gray-700"></div>
        </div>
        </div>
    </div>


    <!-- Dashboard JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set CSRF token for all AJAX requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Get elements
            const siteSelector = document.getElementById('site-selector');
            const periodSelector = document.getElementById('period-selector');

            // Initialize state
            const state = {
                siteId: siteSelector ? siteSelector.value : {{ $site->id ?? 'null' }},
                period: periodSelector.value,
                charts: {},
                apiBase: '{{ config("candle.route_prefix", "api/analytics") }}'
            };

            // Event listeners
            if (siteSelector) {
                siteSelector.addEventListener('change', function() {
                    state.siteId = this.value;
                    loadDashboardData();
                });
            }

            periodSelector.addEventListener('change', function() {
                state.period = this.value;
                loadDashboardData();
            });

            // Initialize charts
            initCharts();

            // Load initial data
            loadDashboardData();

            function fetchSessions() {
                fetchData('sessions')
                .then(json => renderSessions(json.data))
                .catch(console.error);
            }

            function renderSessions(sessions) {
                const container = document.getElementById('sessions-list');
                if (!sessions.length) {
                container.innerHTML = '<li class="py-2 text-sm text-gray-500">No sessions found.</li>';
                return;
                }

                container.innerHTML = '';
                sessions.forEach(session => {
                const li = document.createElement('li');
                li.className = 'py-2 flex justify-between items-center cursor-pointer hover:bg-gray-50 px-2';
                li.innerHTML = `
                    <div>
                    <p class="text-sm font-medium">Session: ${session.session_id}</p>
                    <p class="text-xs text-gray-500">${session.events} events</p>
                    </div>
                    <button class="text-blue-600 text-sm" onclick="showSessionDetails('${session.session_id}')">View</button>
                `;
                container.appendChild(li);
                });
            }

            window.showSessionDetails = function(sessionId) {
                const url = `{{ route('candle.sessions.show', ['session_id' => '__SESSION__']) }}`
                    .replace('__SESSION__', sessionId) + `?site_id=${state.siteId}`;

                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Dashboard-Request': 'true',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Failed to fetch session details');
                    return res.json();
                })
                .then(json => {
                    const container = document.getElementById('session-events');
                    container.innerHTML = json.data.map(event => `
                        <div class="border-l-4 pl-2 border-indigo-500">
                            <p><strong>${event.event_name}</strong> at ${new Date(event.created_at).toLocaleTimeString()}</p>
                            <p class="text-xs text-gray-500">${event.url}</p>
                            <pre class="text-xs bg-gray-100 p-2 mt-1 rounded">${JSON.stringify(event.properties, null, 2)}</pre>
                        </div>
                    `).join('');
                    document.getElementById('session-modal').classList.remove('hidden');
                })
                .catch(console.error);
            };


            window.closeSessionModal = function() {
                document.getElementById('session-modal').classList.add('hidden');
            };


            setInterval(fetchSessions, 15000);
            fetchSessions();

            // Function to initialize charts
            function initCharts() {
                // Visitors chart
                const visitorsCtx = document.getElementById('visitors-chart').getContext('2d');
                state.charts.visitors = new Chart(visitorsCtx, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [
                            {
                                label: 'Pageviews',
                                data: [],
                                borderColor: 'rgb(99, 102, 241)',
                                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                tension: 0.1,
                                fill: true
                            },
                            {
                                label: 'Unique Visitors',
                                data: [],
                                borderColor: 'rgb(16, 185, 129)',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                tension: 0.1,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // Devices chart
                const devicesCtx = document.getElementById('devices-chart').getContext('2d');
                state.charts.devices = new Chart(devicesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Desktop', 'Mobile', 'Tablet'],
                        datasets: [{
                            data: [0, 0, 0],
                            backgroundColor: [
                                'rgba(99, 102, 241, 0.8)',
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(251, 191, 36, 0.8)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right'
                            }
                        }
                    }
                });
            }

            // Function to load dashboard data
            function loadDashboardData() {
                // Show loading state
                document.getElementById('total-pageviews').textContent = '--';
                document.getElementById('unique-visitors').textContent = '--';
                document.getElementById('bounce-rate').textContent = '--';
                document.getElementById('avg-session-duration').textContent = '--';
                document.getElementById('top-pages-table').innerHTML = '<tr><td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">Loading...</td></tr>';
                document.getElementById('referrers-table').innerHTML = '<tr><td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">Loading...</td></tr>';

                // Load all data in parallel
                Promise.all([
                    fetchData('pageviews'),
                    fetchData('unique-visitors'),
                    fetchData('sessions'),
                    fetchData('top-pages'),
                    fetchData('referrers'),
                    fetchData('devices')
                ]).then(function(results) {
                    updateDashboard(results[0], results[1], results[2], results[3], results[4], results[5]);
                }).catch(function(error) {
                    console.error('Error loading dashboard data:', error);
                    alert('Failed to load dashboard data. Please try again later.');
                });
            }

            // Function to fetch API data
            function fetchData(endpoint) {
                let url = '';

                // Use the correct route names that match your API routes
                switch(endpoint) {
                    case 'pageviews':
                        url = "{{ route('candle.pageviews') }}";
                        break;
                    case 'unique-visitors':
                        url = "{{ route('candle.unique-visitors') }}";
                        break;
                    case 'sessions':
                        url = "{{ route('candle.sessions') }}";
                        break;
                    case 'top-pages':
                        url = "{{ route('candle.top-pages') }}";
                        break;
                    case 'referrers':
                        url = "{{ route('candle.referrers') }}";
                        break;
                    case 'devices':
                        url = "{{ route('candle.devices') }}";
                        break;
                    default:
                        url = `/${state.apiBase}/${endpoint}`;
                }

                // Add query parameters
                url = `${url}?site_id=${state.siteId}&period=${state.period}`;

                return fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Dashboard-Request': 'true', // Special header for dashboard requests
                        'X-CSRF-TOKEN': csrfToken
                    }
                }).then(function(response) {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                });
            }

            // Function to update the dashboard with fetched data
            function updateDashboard(pageviewsData, visitorsData, sessionsData, topPagesData, referrersData, devicesData) {
                // Update metrics
                document.getElementById('total-pageviews').textContent = pageviewsData.data.total.toLocaleString();
                document.getElementById('unique-visitors').textContent = visitorsData.data.total.toLocaleString();

                // Calculate and update bounce rate
                const bounceRate = calculateBounceRate(sessionsData.data.sessions);
                document.getElementById('bounce-rate').textContent = bounceRate + '%';

                // Update average session duration
                const avgDuration = formatDuration(sessionsData.data.average_duration_seconds);
                document.getElementById('avg-session-duration').textContent = avgDuration;

                // Update visitors chart
                updateVisitorsChart(pageviewsData.data.pageviews, visitorsData.data.visitors);

                // Update devices chart
                updateDevicesChart(devicesData.data);

                // Update top pages table
                updateTopPagesTable(topPagesData.data);

                // Update referrers table
                updateReferrersTable(referrersData.data);
            }

            // Function to update the visitors chart
            function updateVisitorsChart(pageviewsData, visitorsData) {
                // Prepare data
                const labels = pageviewsData.map(item => formatDate(item.date));
                const pageviews = pageviewsData.map(item => item.count);
                const visitors = visitorsData.map(item => item.count);

                // Update chart datasets
                state.charts.visitors.data.labels = labels;
                state.charts.visitors.data.datasets[0].data = pageviews;
                state.charts.visitors.data.datasets[1].data = visitors;
                state.charts.visitors.update();
            }

            // Function to update the devices chart
            function updateDevicesChart(devicesData) {
                const desktop = devicesData.filter(item => item.device_type === 'desktop')
                    .reduce((sum, item) => sum + item.count, 0);

                const mobile = devicesData.filter(item => item.device_type === 'mobile')
                    .reduce((sum, item) => sum + item.count, 0);

                const tablet = devicesData.filter(item => item.device_type === 'tablet')
                    .reduce((sum, item) => sum + item.count, 0);

                // Update chart datasets
                state.charts.devices.data.datasets[0].data = [desktop, mobile, tablet];
                state.charts.devices.update();
            }

            // Function to update the top pages table
            function updateTopPagesTable(topPagesData) {
                if (!topPagesData || topPagesData.length === 0) {
                    document.getElementById('top-pages-table').innerHTML =
                        '<tr><td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No data available</td></tr>';
                    return;
                }

                let html = '';
                topPagesData.forEach(function(page) {
                    html += `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${formatUrl(page.url)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                ${page.pageviews.toLocaleString()}
                            </td>
                        </tr>
                    `;
                });

                document.getElementById('top-pages-table').innerHTML = html;
            }

            // Function to update the referrers table
            function updateReferrersTable(referrersData) {
                if (!referrersData || referrersData.length === 0) {
                    document.getElementById('referrers-table').innerHTML =
                        '<tr><td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">No data available</td></tr>';
                    return;
                }

                let html = '';
                referrersData.forEach(function(referrer) {
                    html += `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${formatReferrer(referrer.referrer)}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                ${referrer.count.toLocaleString()}
                            </td>
                        </tr>
                    `;
                });

                document.getElementById('referrers-table').innerHTML = html;
            }

            // Utility: Calculate bounce rate
            function calculateBounceRate(sessions) {
                if (!sessions || sessions.length === 0) return '0.0';

                const bouncedSessions = sessions.filter(session => session.pageviews === 1).length;
                const bounceRate = (bouncedSessions / sessions.length) * 100;

                return bounceRate.toFixed(1);
            }

            // Utility: Format duration in seconds to readable time
            function formatDuration(seconds) {
                if (!seconds) return '0:00';

                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = Math.floor(seconds % 60);

                return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
            }

            // Utility: Format date
            function formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
            }

            // Utility: Format URL for display
            function formatUrl(url) {
                try {
                    // Extract path from URL
                    const urlObj = new URL(url);
                    let path = urlObj.pathname;

                    // Handle homepage
                    if (path === '/') return '/';

                    // Limit to 50 characters
                    if (path.length > 50) {
                        path = path.substring(0, 47) + '...';
                    }

                    return path;
                } catch (e) {
                    // Fallback if URL parsing fails
                    return url.substring(0, 50);
                }
            }

            // Utility: Format referrer for display
            function formatReferrer(referrer) {
                if (!referrer) return 'Direct / None';

                try {
                    const urlObj = new URL(referrer);
                    return urlObj.hostname;
                } catch (e) {
                    return referrer.substring(0, 50);
                }
            }
        });
    </script>
</body>
</html>
