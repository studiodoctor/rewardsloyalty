{{--
    Reward Loyalty - Proprietary Software
    Copyright (c) 2025 NowSquare. All rights reserved.
    See LICENSE file for terms.

    Member Card Component - Revolut × Jony Ive
    
    Minimal. One status dot in tier color.
    Everything else: clean neutrals.
--}}

@php
    $tier = $memberTier?->tier;
    $tierName = $tier?->getTranslation('display_name', app()->getLocale());
    $tierColor = $tier?->color ?? '#10B981';
    $tierIcon = $tier?->icon ?? '🎖️';
    $multiplier = $tier?->points_multiplier ?? 1.00;
@endphp

<div {{ $attributes->except('class') }} 
     class="group bg-white dark:bg-secondary-900 rounded-2xl border border-secondary-200 dark:border-secondary-800 shadow-sm hover:shadow-lg transition-all duration-300 {{ $attributes->get('class') }}">
    <div class="p-5">
        <div class="flex items-center gap-4">
            {{-- Avatar --}}
            <div class="relative flex-shrink-0">
                @if ($member->avatar)
                    <img class="w-12 h-12 rounded-xl object-cover" 
                         src="{{ $member->avatar }}" 
                         alt="{{ parse_attr($member->name) }}">
                @else
                    <div class="w-12 h-12 rounded-xl bg-secondary-100 dark:bg-secondary-800 flex items-center justify-center">
                        <x-ui.icon icon="user" class="w-6 h-6 text-secondary-400 dark:text-secondary-500" />
                    </div>
                @endif
                {{-- Status dot --}}
                <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-secondary-900 animate-pulse"
                      style="background: {{ $showTier && $tier ? $tierColor : '#10B981' }};"></span>
            </div>
            
            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <h4 class="text-base font-semibold text-secondary-900 dark:text-white truncate">
                    {{ $member->name }}
                </h4>
                <div class="flex items-center gap-2 mt-0.5">
                    @if($showTier && $tier)
                        <span class="text-sm text-secondary-500 dark:text-secondary-400">{{ $tierIcon }} {{ $tierName }}</span>
                        <span class="text-secondary-300 dark:text-secondary-600">·</span>
                    @endif
                    <span class="text-sm text-secondary-400 dark:text-secondary-500">
                        {{ $member->created_at->setTimezone(app()->make('i18n')->time_zone)->translatedFormat('M Y') }}
                    </span>
                </div>
            </div>
            
            {{-- Right --}}
            <div class="flex-shrink-0 text-right">
                <p class="text-xs font-mono text-secondary-500 dark:text-secondary-400">{{ $member->unique_identifier }}</p>
                @if($showTier && $tier && $multiplier > 1.00)
                    <p class="text-sm font-semibold text-secondary-900 dark:text-white mt-0.5 flex items-center gap-1">
                        {{ number_format($multiplier, 1) }}×
                        <x-ui.icon icon="coins" class="w-3.5 h-3.5 text-secondary-400 dark:text-secondary-400" />
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
