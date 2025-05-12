@props([
    'title',
    'value' => '--',
    'icon' => null,
    'color' => 'indigo',
    'loading' => false,
    'percentage' => null,
    'trend' => null,
])

@php
    $colors = [
        'indigo' => [
            'bg' => 'bg-indigo-500',
            'text' => 'text-indigo-500',
            'trend_up' => 'text-green-500',
            'trend_down' => 'text-red-500',
        ],
        'green' => [
            'bg' => 'bg-green-500',
            'text' => 'text-green-500',
            'trend_up' => 'text-green-500',
            'trend_down' => 'text-red-500',
        ],
        'red' => [
            'bg' => 'bg-red-500',
            'text' => 'text-red-500',
            'trend_up' => 'text-red-500',
            'trend_down' => 'text-green-500',
        ],
        'yellow' => [
            'bg' => 'bg-yellow-500',
            'text' => 'text-yellow-500',
            'trend_up' => 'text-green-500',
            'trend_down' => 'text-red-500',
        ],
        'blue' => [
            'bg' => 'bg-blue-500',
            'text' => 'text-blue-500',
            'trend_up' => 'text-green-500',
            'trend_down' => 'text-red-500',
        ],
        'cyan' => [
            'bg' => 'bg-cyan-500',
            'text' => 'text-cyan-500',
            'trend_up' => 'text-green-500',
            'trend_down' => 'text-red-500',
        ],
        'purple' => [
            'bg' => 'bg-purple-500',
            'text' => 'text-purple-500',
            'trend_up' => 'text-green-500',
            'trend_down' => 'text-red-500',
        ],
    ];

    $colorClasses = $colors[$color] ?? $colors['indigo'];
@endphp

<div class="bg-white overflow-hidden shadow rounded-lg group hover:shadow-md transition-all duration-300 animate-on-scroll">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center">
            @if($icon)
                <div class="flex-shrink-0 rounded-md p-3 {{ $colorClasses['bg'] }} group-hover:scale-110 transition-transform duration-300">
                    {{ $icon }}
                </div>
            @endif

            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                        {{ $title }}
                    </dt>
                    <dd>
                        @if($loading)
                            <div class="animate-pulse h-7 w-16 mt-1 bg-gray-200 rounded"></div>
                        @else
                            <div class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900">
                                    {{ $value }}
                                </div>

                                @if($percentage !== null)
                                    <span class="ml-2 text-sm font-medium {{ $trend === 'up' ? $colorClasses['trend_up'] : ($trend === 'down' ? $colorClasses['trend_down'] : 'text-gray-500') }} flex items-center">
                                        @if($trend === 'up')
                                            <svg class="self-center flex-shrink-0 h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                            </svg>
                                        @elseif($trend === 'down')
                                            <svg class="self-center flex-shrink-0 h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                            </svg>
                                        @endif
                                        {{ $percentage }}%
                                    </span>
                                @endif
                            </div>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
