@extends('candle::layouts.main')

@section('content')
<div x-data="dashboard()" x-init="init()">
    <!-- Metrics Overview -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Pageviews Card -->
        <x-candle::metric-card
            title="Pageviews"
            :loading="false"
            value="--"
            :percentage="12.3"
            :trend="'up'"
            color="indigo"
            id="metric-pageviews"
        >
            <x-slot:icon>
                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </x-slot:icon>
        </x-candle::metric-card>

        <!-- Unique Visitors Card -->
        <x-candle::metric-card
            title="Visitors"
            :loading="false"
            value="--"
            :percentage="8.7"
            :trend="'up'"
            color="green"
            id="metric-visitors"
        >
            <x-slot:icon>
                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </x-slot:icon>
        </x-candle::metric-card>

        <!-- Bounce Rate Card -->
        <x-candle::metric-card
            title="Bounce Rate"
            :loading="false"
            value="--"
            :percentage="-5.2"
            :trend="'down'"
            color="red"
            id="metric-bounce"
        >
            <x-slot:icon>
                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </x-slot:icon>
        </x-candle::metric-card>

        <!-- Avg. Session Duration Card -->
        <x-candle::metric-card
            title="Duration"
            :loading="false"
            value="--"
            :percentage="15.1"
            :trend="'up'"
            color="yellow"
            id="metric-duration"
        >
            <x-slot:icon>
                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:icon>
        </x-candle::metric-card>
    </div>

    <!-- Charts Section -->
    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Traffic Chart -->
        <x-candle::chart
            title="Visitors Over Time"
            id="visitors-chart"
            x-bind:loading="loading.visitorsChart"
        >
            <x-slot:actions>
                <div class="flex space-x-2">
                    <button
                        type="button"
                        class="text-xs px-2 py-1 rounded border"
                        :class="chartView === 'daily' ? 'bg-primary-50 border-primary-500 text-primary-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
                        @click="setChartView('daily')"
                    >
                        Daily
                    </button>
                    <button
                        type="button"
                        class="text-xs px-2 py-1 rounded border"
                        :class="chartView === 'weekly' ? 'bg-primary-50 border-primary-500 text-primary-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
                        @click="setChartView('weekly')"
                    >
                        Weekly
                    </button>
                    <button
                        type="button"
                        class="text-xs px-2 py-1 rounded border"
                        :class="chartView === 'monthly' ? 'bg-primary-50 border-primary-500 text-primary-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
                        @click="setChartView('monthly')"
                    >
                        Monthly
                    </button>
                </div>
            </x-slot:actions>
        </x-candle::chart>

        <!-- Devices Chart -->
        <x-candle::chart
            title="Device Distribution"
            id="devices-chart"
            type="doughnut"
            x-bind:loading="loading.devicesChart"
        >
            <x-slot:actions>
                <div class="flex space-x-2">
                    <button
                        type="button"
                        class="text-xs px-2 py-1 rounded border"
                        :class="deviceView === 'types' ? 'bg-primary-50 border-primary-500 text-primary-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
                        @click="setDeviceView('types')"
                    >
                        Device Types
                    </button>
                    <button
                        type="button"
                        class="text-xs px-2 py-1 rounded border"
                        :class="deviceView === 'browsers' ? 'bg-primary-50 border-primary-500 text-primary-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
                        @click="setDeviceView('browsers')"
                    >
                        Browsers
                    </button>
                </div>
            </x-slot:actions>
        </x-candle::chart>
    </div>

    <!-- Top Pages & Referrers -->
    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Top Pages Table -->
        <x-candle::data-table
            title="Top Pages"
            x-bind:loading="loading.topPages"
        >
            <x-candle::table>
                <x-slot:header>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Page
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Pageviews
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Visitors
                    </th>
                </x-slot:header>

                <x-slot:body>
                    <template x-if="!topPages.length && !loading.topPages">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                No data available
                            </td>
                        </tr>
                    </template>

                    <template x-for="(page, index) in topPages" :key="index">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <span
                                        class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full text-xs font-medium"
                                        :class="index < 3 ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-800'"
                                        x-text="index + 1"
                                    ></span>
                                    <div class="ml-3 truncate max-w-xs" x-text="formatUrl(page.url)"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right" x-text="page.pageviews"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right" x-text="page.visitors"></td>
                        </tr>
                    </template>
                </x-slot:body>
            </x-candle::table>
        </x-candle::data-table>

        <!-- Referrers Table -->
        <x-candle::data-table
            title="Top Referrers"
            x-bind:loading="loading.referrers"
        >
            <x-candle::table>
                <x-slot:header>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Source
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Visitors
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Conversion Rate
                    </th>
                </x-slot:header>

                <x-slot:body>
                    <template x-if="!referrers.length && !loading.referrers">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                No data available
                            </td>
                        </tr>
                    </template>

                    <template x-for="(referrer, index) in referrers" :key="index">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <span
                                        class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full text-xs font-medium"
                                        :class="index < 3 ? 'bg-primary-100 text-primary-800' : 'bg-gray-100 text-gray-800'"
                                        x-text="index + 1"
                                    ></span>
                                    <div class="ml-3" x-text="formatReferrer(referrer.referrer)"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right" x-text="referrer.count"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right" x-text="(referrer.conversion_rate || 0) + '%'"></td>
                        </tr>
                    </template>
                </x-slot:body>
            </x-candle::table>
        </x-candle::data-table>
    </div>

    <!-- Live Sessions Section -->
    <div class="mt-8">
        <x-candle::live-sessions :site="$site" />
    </div>

    <!-- Session Modal Component -->
    <x-candle::session-modal :site="$site" />
</div>

@push('scripts')
     <!-- Define global functions and config BEFORE loading dashboard.js -->
    <script>
        // Define ajaxSetup function globally
        window.ajaxSetup = function() {
            return {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Dashboard-Request': 'true'  // Add this header for dashboard requests
                },
                credentials: 'same-origin'
            };
        };

        // Define dashboard config BEFORE initializing Alpine
        window.dashboardConfig = {
            siteId: "{{ $site->id }}",
            routes: {
                pageviews: "{{ route('candle.pageviews') }}",
                uniqueVisitors: "{{ route('candle.unique-visitors') }}",
                sessions: "{{ route('candle.sessions') }}",
                sessionData: "{{ route('candle.sessions.data') }}",
                topPages: "{{ route('candle.top-pages') }}",
                referrers: "{{ route('candle.referrers') }}",
                devices: "{{ route('candle.devices') }}"
            }
        };
    </script>

    <!-- Load dashboard.js AFTER config is defined -->
    <script src="{{ asset('vendor/candle/js/dashboard.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize with site data
            window.dashboardConfig = {
                siteId: "{{ $site->id }}",
                routes: {
                    pageviews: "{{ route('candle.pageviews') }}",
                    uniqueVisitors: "{{ route('candle.unique-visitors') }}",
                    sessions: "{{ route('candle.sessions') }}",
                    sessionData: "{{ route('candle.sessions.data') }}",
                    topPages: "{{ route('candle.top-pages') }}",
                    referrers: "{{ route('candle.referrers') }}",
                    devices: "{{ route('candle.devices') }}"
                }
            };
        });
    </script>
@endpush
@endsection
