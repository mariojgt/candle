@extends('candle::layouts.main')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="pb-5 border-b border-gray-200 sm:flex sm:items-center sm:justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Create New Site
            </h3>
            <div class="mt-3 sm:mt-0 sm:ml-4">
                <a href="{{ route('candle.sites.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Back to Sites
                </a>
            </div>
        </div>

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Whoops!</strong>
                <span class="block sm:inline">There were some problems with your input.</span>
                <ul class="mt-3 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Section -->
        <div class="mt-10 sm:mt-0">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Site Information</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Create a new site to track analytics data.
                            You'll need to add the tracking script to your website once the site is created.
                        </p>
                    </div>
                </div>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <form action="{{ route('candle.sites.store') }}" method="POST">
                        @csrf
                        <div class="shadow overflow-hidden sm:rounded-md">
                            <div class="px-4 py-5 bg-white sm:p-6">
                                <div class="grid grid-cols-6 gap-6">
                                    <div class="col-span-6 sm:col-span-4">
                                        <label for="name" class="block text-sm font-medium text-gray-700">Site Name</label>
                                        <input type="text" name="name" id="name" value="{{ old('name') }}" autocomplete="name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                        <p class="mt-2 text-sm text-gray-500">
                                            A friendly name to identify your site (e.g., "My Blog" or "Company Website").
                                        </p>
                                    </div>

                                    <div class="col-span-6 sm:col-span-4">
                                        <label for="domain" class="block text-sm font-medium text-gray-700">Domain</label>
                                        <input type="text" name="domain" id="domain" value="{{ old('domain') }}" autocomplete="domain" placeholder="example.com" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                                        <p class="mt-2 text-sm text-gray-500">
                                            The domain name of your website without http:// or https:// (e.g., "example.com").
                                        </p>
                                    </div>

                                    <div class="col-span-6">
                                        <label for="allowed_origins" class="block text-sm font-medium text-gray-700">Allowed Origins (Optional)</label>
                                        <textarea name="allowed_origins" id="allowed_origins" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('allowed_origins') }}</textarea>
                                        <p class="mt-2 text-sm text-gray-500">
                                            Optional. List the domains that can send analytics data to this site, one per line.
                                            Leave empty to only allow tracking from the main domain.
                                            Use "*" prefix for wildcard subdomains (e.g., "*.example.com").
                                        </p>
                                    </div>

                                    <div class="col-span-6">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">Tracking Settings</h4>

                                        <div class="space-y-4">
                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input id="settings[exclude_bots]" name="settings[exclude_bots]" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ old('settings.exclude_bots', true) ? 'checked' : '' }}>
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="settings[exclude_bots]" class="font-medium text-gray-700">Exclude Bots</label>
                                                    <p class="text-gray-500">Automatically filter out bot traffic from your analytics.</p>
                                                </div>
                                            </div>

                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input id="settings[anonymize_ips]" name="settings[anonymize_ips]" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ old('settings.anonymize_ips', true) ? 'checked' : '' }}>
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="settings[anonymize_ips]" class="font-medium text-gray-700">Anonymize IP Addresses</label>
                                                    <p class="text-gray-500">Mask the last part of visitors' IP addresses for enhanced privacy.</p>
                                                </div>
                                            </div>

                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input id="settings[track_clicks]" name="settings[track_clicks]" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ old('settings.track_clicks', true) ? 'checked' : '' }}>
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="settings[track_clicks]" class="font-medium text-gray-700">Track Clicks</label>
                                                    <p class="text-gray-500">Automatically track clicks on links and buttons.</p>
                                                </div>
                                            </div>

                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input id="settings[track_forms]" name="settings[track_forms]" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ old('settings.track_forms', true) ? 'checked' : '' }}>
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="settings[track_forms]" class="font-medium text-gray-700">Track Form Submissions</label>
                                                    <p class="text-gray-500">Automatically track when users submit forms on your site.</p>
                                                </div>
                                            </div>

                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input id="settings[track_route_changes]" name="settings[track_route_changes]" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ old('settings.track_route_changes', true) ? 'checked' : '' }}>
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="settings[track_route_changes]" class="font-medium text-gray-700">Track Route Changes (SPAs)</label>
                                                    <p class="text-gray-500">Track navigation in single-page applications (SPAs).</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Create Site
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
