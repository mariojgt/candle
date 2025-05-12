@props([
    'title',
    'loading' => false,
])

<div class="bg-white overflow-hidden shadow rounded-lg animate-on-scroll">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $title }}</h3>
            <div>
                {{ $actions ?? '' }}
            </div>
        </div>

        @if($loading)
            <div class="animate-pulse">
                <div class="h-8 bg-gray-200 rounded mb-4"></div>
                <div class="h-8 bg-gray-100 rounded mb-2"></div>
                <div class="h-8 bg-gray-100 rounded mb-2"></div>
                <div class="h-8 bg-gray-100 rounded mb-2"></div>
                <div class="h-8 bg-gray-100 rounded mb-2"></div>
                <div class="h-8 bg-gray-100 rounded"></div>
            </div>
        @else
            <div class="mt-4 max-h-96 overflow-y-auto">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
