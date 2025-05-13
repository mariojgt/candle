@extends('candle::layouts.main')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="pb-5 border-b border-gray-200 sm:flex sm:items-center sm:justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Site Details: {{ $site->name }}
            </h3>
            <div class="mt-3 sm:mt-0 sm:ml-4 flex">
                <a href="{{ route('candle.dashboard', ['site_id' => $site->id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-3">
                    View Analytics
                </a>
                <a href="{{ route('candle.sites.edit', $site) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 mr-3">
                    Edit Site
                </a>
                <a href="{{ route('candle.sites.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Back to Sites
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <!-- API Key Display -->
        @if (session('api_key'))
            <div class="mt-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                <p class="font-bold">Your API key has been created</p>
                <p class="mb-2">This key will only be shown once. Please save it in a secure location:</p>
                <div class="bg-white p-2 rounded border border-blue-400 font-mono text-sm break-all">
                    {{ session('api_key') }}
                </div>
            </div>
        @endif

        <!-- Site Information -->
        <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Site Information
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Details and settings for this site.
                </p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Site Name
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $site->domain }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Created
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $site->created_at->format('F j, Y, g:i a') }}
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Allowed Origins
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if(!empty($site->allowed_origins))
                                <ul class="border border-gray-200 rounded-md divide-y divide-gray-200">
                                    @foreach($site->allowed_origins as $origin)
                                        <li class="pl-3 pr-4 py-2 flex items-center justify-between text-sm">
                                            <div class="w-0 flex-1 flex items-center">
                                                <span class="ml-2 flex-1 w-0 truncate">{{ $origin }}</span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-gray-500">No additional origins allowed. Only {{ $site->domain }} can send analytics data.</span>
                            @endif
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Tracking Settings
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <ul class="border border-gray-200 rounded-md divide-y divide-gray-200">
                                <li class="pl-3 pr-4 py-2 flex items-center justify-between text-sm">
                                    <div class="w-0 flex-1 flex items-center">
                                        <span class="ml-2 flex-1 w-0 truncate">Exclude Bots</span>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <span class="{{ $site->settings['exclude_bots'] ?? true ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $site->settings['exclude_bots'] ?? true ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </div>
                                </li>
                                <li class="pl-3 pr-4 py-2 flex items-center justify-between text-sm">
                                    <div class="w-0 flex-1 flex items-center">
                                        <span class="ml-2 flex-1 w-0 truncate">Anonymize IP Addresses</span>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <span class="{{ $site->settings['anonymize_ips'] ?? true ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $site->settings['anonymize_ips'] ?? true ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </div>
                                </li>
                                <li class="pl-3 pr-4 py-2 flex items-center justify-between text-sm">
                                    <div class="w-0 flex-1 flex items-center">
                                        <span class="ml-2 flex-1 w-0 truncate">Track Clicks</span>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <span class="{{ $site->settings['track_clicks'] ?? true ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $site->settings['track_clicks'] ?? true ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </div>
                                </li>
                                <li class="pl-3 pr-4 py-2 flex items-center justify-between text-sm">
                                    <div class="w-0 flex-1 flex items-center">
                                        <span class="ml-2 flex-1 w-0 truncate">Track Form Submissions</span>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <span class="{{ $site->settings['track_forms'] ?? true ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $site->settings['track_forms'] ?? true ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </div>
                                </li>
                                <li class="pl-3 pr-4 py-2 flex items-center justify-between text-sm">
                                    <div class="w-0 flex-1 flex items-center">
                                        <span class="ml-2 flex-1 w-0 truncate">Track Route Changes (SPAs)</span>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <span class="{{ $site->settings['track_route_changes'] ?? true ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $site->settings['track_route_changes'] ?? true ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Integration Instructions -->
        <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Integration Instructions
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Add the tracking code to your website to start collecting analytics data.
                </p>
            </div>
            <div class="border-t border-gray-200">
                <div class="px-4 py-5 sm:px-6">
                    <h4 class="text-sm font-medium text-gray-500 mb-2">Option 1: Add script tag directly</h4>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <pre class="text-xs overflow-x-auto"><code>&lt;script src="{{ route('candle.tracker') }}?site_id={{ $site->id }}"&gt;&lt;/script&gt;</code></pre>
                    </div>

                    <h4 class="text-sm font-medium text-gray-500 mt-6 mb-2">Option 2: Asynchronous loading (recommended)</h4>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <pre class="text-xs overflow-x-auto"><code>&lt;script&gt;
(function() {
    var sa = document.createElement('script');
    sa.type = 'text/javascript';
    sa.async = true;
    sa.src = '{{ route('candle.tracker') }}?site_id={{ $site->id }}';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(sa, s);
})();
&lt;/script&gt;</code></pre>
                    </div>

                    <p class="mt-4 text-sm text-gray-500">
                        Add this code to the <code>&lt;head&gt;</code> section of your website. The script will automatically track page views.
                    </p>

                    <h4 class="text-sm font-medium text-gray-500 mt-6 mb-2">Tracking custom events</h4>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <pre class="text-xs overflow-x-auto"><code>// Track a custom event
Candle.track('button_click', {
    button_id: 'submit-form',
    page: '/checkout'
});

// Manually track a page view (for SPAs)
Candle.trackPageview();</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Keys -->
        <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        API Keys
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        API keys for this site.
                    </p>
                </div>
                <div>
                    <a href="{{ route('candle.api-keys.create', ['site_id' => $site->id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create API Key
                    </a>
                </div>
            </div>
            <div class="border-t border-gray-200">
                @if(count($apiKeys) > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Last Used
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($apiKeys as $apiKey)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $apiKey->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $apiKey->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $apiKey->last_used_at ? $apiKey->last_used_at->format('M d, Y') : 'Never' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $apiKey->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $apiKey->active ? 'Active' : 'Revoked' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($apiKey->active)
                                            <form action="{{ route('candle.api-keys.revoke', $apiKey) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to revoke this API key?')">
                                                    Revoke
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('candle.api-keys.activate', $apiKey) }}" method="POST" class="inline-block">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900">
                                                    Activate
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('candle.api-keys.destroy', $apiKey) }}" method="POST" class="inline-block ml-3">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this API key? This action cannot be undone.')">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="bg-white px-6 py-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No API keys</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            You haven't created any API keys for this site yet.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('candle.api-keys.create', ['site_id' => $site->id]) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Create API Key
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.title = "{{ $site->name }} - Candle";
</script>
@endpush
