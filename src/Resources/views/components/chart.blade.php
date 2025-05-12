@props([
    'title',
    'id',
    'height' => '300px',
    'type' => 'line',
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
            <div class="flex items-center justify-center" style="height: {{ $height }}">
                <div class="flex flex-col items-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-500"></div>
                    <span class="mt-2 text-sm text-gray-500">Loading data...</span>
                </div>
            </div>
        @else
            <div style="height: {{ $height }}" class="chart-container">
                <canvas id="{{ $id }}"></canvas>
            </div>
            {{ $slot }}
        @endif
    </div>
</div>
