{{-- 
 Reward Loyalty - Proprietary Software
 Copyright (c) 2025 NowSquare. All rights reserved.
 See LICENSE file for terms.
--}}

@props([
    'title',
    'icon' => null,
    'open' => false,
    'badgeLabel' => null,
    'badgeColor' => 'warning',
])

<div x-data="{ 
    open: @js($open),
    hasActiveChild: false,
    init() {
        // Check if any child nav-link is active
        this.hasActiveChild = this.$el.querySelectorAll('[data-nav-active=true]').length > 0;
        // Auto-open if has active child
        if (this.hasActiveChild) {
            this.open = true;
        }
    }
}" class="group/nav-group">
    {{-- Group Header --}}
    <button 
        @click="open = !open"
        class="w-full flex items-center gap-4 px-4 py-3 rounded-2xl text-[13px] font-semibold tracking-tight transition-all duration-300 ease-out group/header relative overflow-hidden"
        :class="{ 
            'text-secondary-900 dark:text-white': hasActiveChild,
            'text-secondary-500 dark:text-secondary-400 hover:text-secondary-900 dark:hover:text-white': !hasActiveChild 
        }">
        
        {{-- Hover background effect --}}
        <div class="absolute inset-0 bg-gradient-to-br from-secondary-50/80 to-secondary-100/40 dark:from-secondary-800/50 dark:to-secondary-900/30 opacity-0 group-hover/header:opacity-100 transition-all duration-500 ease-out rounded-2xl backdrop-blur-sm"
            :class="{ 'opacity-100': hasActiveChild }"></div>
        
        {{-- Active glow effect --}}
        <div x-show="hasActiveChild" 
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            class="absolute inset-0 bg-gradient-to-r from-primary-500/5 via-primary-400/10 to-primary-500/5 rounded-2xl"
            style="display: none;"></div>
        
        <div class="relative flex items-center gap-4 flex-1 z-10">
            @if($icon)
                <div class="relative">
                    <x-ui.icon 
                        :icon="$icon" 
                        class="w-5 h-5 flex-shrink-0 transition-all duration-300 ease-out"
                        ::class="{ 
                            'text-primary-600 dark:text-primary-400 scale-110': hasActiveChild,
                            'group-hover/header:scale-110 group-hover/header:rotate-[-4deg]': !hasActiveChild
                        }" />
                    {{-- Icon glow --}}
                    <div x-show="hasActiveChild" 
                        class="absolute inset-0 bg-primary-500/20 blur-xl rounded-full scale-150"
                        style="display: none;"></div>
                </div>
            @endif
            
            <span class="flex-1 text-left uppercase tracking-wide" 
                ::class="{ 'text-primary-900 dark:text-primary-100': hasActiveChild }">
                {{ $title }}
            </span>
            
            @if($badgeLabel)
                <x-ui.badge 
                    :variant="$badgeColor" 
                    size="sm"
                    class="relative z-10">
                    {{ $badgeLabel }}
                </x-ui.badge>
            @endif
            
            <x-ui.icon 
                icon="chevron-right" 
                class="w-4 h-4 transition-all duration-500 ease-out flex-shrink-0" 
                ::class="{ 
                    'rotate-90 scale-110': open,
                    'group-hover/header:translate-x-0.5': !open
                }" />
        </div>
    </button>

    {{-- Group Items --}}
    <div 
        x-show="open" 
        x-collapse
        x-transition:enter="transition-all ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="mt-1 space-y-0.5 relative">
        {{-- Elegant connection line --}}
        <div class="absolute left-7 top-2 bottom-2 w-px bg-gradient-to-b from-secondary-200 via-secondary-200/50 to-transparent dark:from-secondary-800 dark:via-secondary-800/50 dark:to-transparent"></div>
        
        <div class="ml-8 space-y-0.5">
            {{ $slot }}
        </div>
    </div>
</div>
