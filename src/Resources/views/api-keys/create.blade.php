<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create API Key - Candle</title>
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
                            Create New API Key
                        </h3>
                        <div class="mt-3 sm:mt-0 sm:ml-4">
                            <a href="{{ route('candle.api-keys.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Back to API Keys
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
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">API Key Information</h3>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Create a new API key to authenticate your analytics tracking and API requests.
                                    </p>
                                </div>
                            </div>
                            <div class="mt-5 md:mt-0 md:col-span-2">
                                <form action="{{ route('candle.api-keys.store') }}" method="POST">
                                    @csrf
                                    <div class="shadow overflow-hidden sm:rounded-md">
                                        <div class="px-4 py-5 bg-white sm:p-6">
                                            <div class="grid grid-cols-6 gap-6">
                                                <div class="col-span-6">
                                                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                                    <input type="text" name="name" id="name" autocomplete="name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="My API Key" required>
                                                    <p class="mt-2 text-sm text-gray-500">
                                                        A descriptive name to help you identify this API key later.
                                                    </p>
                                                </div>

                                                <div class="col-span-6">
                                                    <label for="site_id" class="block text-sm font-medium text-gray-700">Site</label>
                                                    <select id="site_id" name="site_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                                        @foreach($sites as $site)
                                                            <option value="{{ $site->id }}">{{ $site->name }} ({{ $site->domain }})</option>
                                                        @endforeach
                                                    </select>
                                                    <p class="mt-2 text-sm text-gray-500">
                                                        Select which site this API key will be associated with.
                                                    </p>
                                                </div>

                                                <div class="col-span-6">
                                                    <label class="block text-sm font-medium text-gray-700">Permissions</label>
                                                    <div class="mt-4 space-y-4">
                                                        <div class="flex items-start">
                                                            <div class="flex items-center h-5">
                                                                <input id="permissions[read]" name="permissions[read]" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" checked>
                                                            </div>
                                                            <div class="ml-3 text-sm">
                                                                <label for="permissions[read]" class="font-medium text-gray-700">Read</label>
                                                                <p class="text-gray-500">Allow reading analytics data</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-start">
                                                            <div class="flex items-center h-5">
                                                                <input id="permissions[write]" name="permissions[write]" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" checked>
                                                            </div>
                                                            <div class="ml-3 text-sm">
                                                                <label for="permissions[write]" class="font-medium text-gray-700">Write</label>
                                                                <p class="text-gray-500">Allow creating and updating analytics events</p>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-start">
                                                            <div class="flex items-center h-5">
                                                                <input id="permissions[admin]" name="permissions[admin]" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                            </div>
                                                            <div class="ml-3 text-sm">
                                                                <label for="permissions[admin]" class="font-medium text-gray-700">Admin</label>
                                                                <p class="text-gray-500">Allow managing site settings and API keys</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Create API Key
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Security Note -->
                    <div class="mt-8 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Security Note</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>API keys grant access to your analytics data. Keep them secure and do not share them publicly.</p>
                                    <p class="mt-1">When you create an API key, it will be shown only once. Make sure to save it somewhere secure.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
