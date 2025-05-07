<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Keys - Candle</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
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
                            <a href="{{ route('candle.dashboard') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Dashboard
                            </a>
                            <a href="{{ route('candle.sites.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Sites
                            </a>
                            <a href="{{ route('candle.api-keys.index') }}" class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                API Keys
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <div class="py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Page Header -->
                    <div class="pb-5 border-b border-gray-200 sm:flex sm:items-center sm:justify-between">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            API Keys
                        </h3>
                        <div class="mt-3 sm:mt-0 sm:ml-4">
                            <a href="{{ route('candle.api-keys.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Create New API Key
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

                    <!-- API Keys List -->
                    <div class="mt-8 flex flex-col">
                        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                    @if(count($apiKeys) > 0)
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Name
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Site
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
                                            <tbody>
                                                @foreach($apiKeys as $apiKey)
                                                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                            {{ $apiKey->name }}
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            {{ $apiKey->site->name }}
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
                                                            <a href="{{ route('candle.api-keys.show', $apiKey) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                                View
                                                            </a>

                                                            @if($apiKey->active)
                                                                <form action="{{ route('candle.api-keys.revoke', $apiKey) }}" method="POST" class="inline-block">
                                                                    @csrf
                                                                    <button type="submit" class="text-red-600 hover:text-red-900 mr-3" onclick="return confirm('Are you sure you want to revoke this API key?')">
                                                                        Revoke
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <form action="{{ route('candle.api-keys.activate', $apiKey) }}" method="POST" class="inline-block">
                                                                    @csrf
                                                                    <button type="submit" class="text-green-600 hover:text-green-900 mr-3">
                                                                        Activate
                                                                    </button>
                                                                </form>
                                                            @endif

                                                            <form action="{{ route('candle.api-keys.destroy', $apiKey) }}" method="POST" class="inline-block">
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
                                        <div class="bg-white px-6 py-12 text-center">
                                            <h3 class="text-lg font-medium text-gray-900">No API keys found</h3>
                                            <p class="mt-2 text-sm text-gray-500">Get started by creating a new API key.</p>
                                            <div class="mt-6">
                                                <a href="{{ route('candle.api-keys.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    Create New API Key
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($apiKeys->hasPages())
                        <div class="mt-4">
                            {{ $apiKeys->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</body>
</html>
