@extends('candle::layouts.main')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="pb-5 border-b border-gray-200 sm:flex sm:items-center sm:justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                API Key: {{ $apiKey->name }}
            </h3>
            <div class="mt-3 sm:mt-0 sm:ml-4 flex">
                <a href="{{ route('candle.api-keys.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Back to API Keys
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

        <!-- API Key Information -->
        <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    API Key Information
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Details and settings for this API key.
                </p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Name
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $apiKey->name }}
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Site
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <a href="{{ route('candle.sites.show', $apiKey->site) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ $apiKey->site->name }}
                            </a>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Status
                        </dt>
                        <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $apiKey->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $apiKey->active ? 'Active' : 'Revoked' }}
                            </span>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Created
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $apiKey->created_at->format('F j, Y, g:i a') }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Last Used
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $apiKey->last_used_at ? $apiKey->last_used_at->format('F j, Y, g:i a') : 'Never' }}
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Permissions
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <ul class="border border-gray-200 rounded-md divide-y divide-gray-200">
                                <li class="pl-3 pr-4 py-2 flex items-center justify-between text-sm">
                                    <div class="w-0 flex-1 flex items-center">
                                        <span class="ml-2 flex-1 w-0 truncate">Read</span>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <span class="{{ isset($apiKey->permissions['read']) && $apiKey->permissions['read'] ? 'text-green-600' : 'text-red-600' }}">
                                            {{ isset($apiKey->permissions['read']) && $apiKey->permissions['read'] ? 'Allowed' : 'Denied' }}
                                        </span>
                                    </div>
                                </li>
                                <li class="pl-3 pr-4 py-2 flex items-center justify-between text-sm">
                                    <div class="w-0 flex-1 flex items-center">
                                        <span class="ml-2 flex-1 w-0 truncate">Write</span>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <span class="{{ isset($apiKey->permissions['write']) && $apiKey->permissions['write'] ? 'text-green-600' : 'text-red-600' }}">
                                            {{ isset($apiKey->permissions['write']) && $apiKey->permissions['write'] ? 'Allowed' : 'Denied' }}
                                        </span>
                                    </div>
                                </li>
                                <li class="pl-3 pr-4 py-2 flex items-center justify-between text-sm">
                                    <div class="w-0 flex-1 flex items-center">
                                        <span class="ml-2 flex-1 w-0 truncate">Admin</span>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <span class="{{ isset($apiKey->permissions['admin']) && $apiKey->permissions['admin'] ? 'text-green-600' : 'text-red-600' }}">
                                            {{ isset($apiKey->permissions['admin']) && $apiKey->permissions['admin'] ? 'Allowed' : 'Denied' }}
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Usage Instructions -->
        <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    API Key Usage
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    How to use this API key in your applications.
                </p>
            </div>
            <div class="border-t border-gray-200">
                <div class="px-4 py-5 sm:px-6">
                    <p class="mb-4 text-sm text-gray-600">
                        Include this API key in your requests to authenticate with the Candle API.
                    </p>

                    <h4 class="text-sm font-medium text-gray-700 mb-2">HTTP Header (recommended)</h4>
                    <div class="bg-gray-50 p-2 rounded-md">
                        <pre class="text-xs overflow-x-auto"><code>X-API-Key: [your-api-key]</code></pre>
                    </div>

                    <h4 class="text-sm font-medium text-gray-700 mt-4 mb-2">Query Parameter</h4>
                    <div class="bg-gray-50 p-2 rounded-md">
                        <pre class="text-xs overflow-x-auto"><code>https://example.com/api/analytics/events?api_key=[your-api-key]</code></pre>
                    </div>

                    <h4 class="text-sm font-medium text-gray-700 mt-4 mb-2">JavaScript Tracker</h4>
                    <div class="bg-gray-50 p-2 rounded-md">
                        <pre class="text-xs overflow-x-auto"><code>&lt;script src="{{ route('candle.tracker') }}?site_id={{ $apiKey->site->id }}&api_key=[your-api-key]"&gt;&lt;/script&gt;</code></pre>
                    </div>

                    <div class="mt-4 text-sm text-red-600">
                        <p>Important: Keep your API key secret. Do not expose it in client-side code or public repositories.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex justify-end space-x-4">
            @if($apiKey->active)
                <form action="{{ route('candle.api-keys.revoke', $apiKey) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to revoke this API key?')">
                        Revoke API Key
                    </button>
                </form>
            @else
                <form action="{{ route('candle.api-keys.activate', $apiKey) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Activate API Key
                    </button>
                </form>
            @endif

            <form action="{{ route('candle.api-keys.destroy', $apiKey) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to delete this API key? This action cannot be undone.')">
                    Delete API Key
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.title = "{{ $apiKey->name }} - API Key - Candle";
</script>
@endpush
