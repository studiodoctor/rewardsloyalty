{{--
Premium Navigation Link Component

Modern nav link with subtle hover states and active indicators.

@props
- active: boolean - Is this link active
- href: string - Link URL
- icon: string - Lucide icon
--}}

@props(['active' => false, 'href' => '#', 'icon' => null])

<a href="{{ $href }}" 
    {{ $attributes->merge(['class' => 'group/link relative flex items-center gap-3.5 px-4 py-2.5 rounded-xl transition-all duration-300 ease-out overflow-hidden']) }} 
    @if($active) data-nav-active="true" @endif>
    
    {{-- Background layers - only show hover, not on active --}}
    <div class="absolute inset-0 bg-secondary-50/0 dark:bg-secondary-800/0 group-hover/link:bg-secondary-50/80 dark:group-hover/link:bg-secondary-800/50 transition-all duration-300 ease-out rounded-xl"></div>
    
    {{-- Content --}}
    <div class="relative flex items-center gap-3.5 flex-1 z-10">
        @if($icon)
            <div class="relative flex items-center justify-center">
                <x-ui.icon 
                    :icon="$icon"
                    class="w-[18px] h-[18px] transition-all duration-300 ease-out {{ $active ? 'text-primary-600 dark:text-primary-400' : 'text-secondary-400 dark:text-secondary-500 group-hover/link:text-secondary-700 dark:group-hover/link:text-secondary-300 group-hover/link:scale-110' }}" 
                />
                
                @if($active)
                    {{-- Subtle icon glow for active state --}}
                    <div class="absolute inset-0 bg-primary-500/10 dark:bg-primary-400/10 blur-lg rounded-full scale-150"></div>
                @endif
            </div>
        @endif
        
        <span class="flex-1 text-[13px] tracking-tight transition-all duration-300 {{ $active ? 'text-primary-900 dark:text-primary-100 font-semibold' : 'text-secondary-600 dark:text-secondary-400 font-medium group-hover/link:text-secondary-900 dark:group-hover/link:text-white' }}">
            {{ $slot }}
        </span>
        
        @if($active)
            {{-- Active indicator - refined dot with glow --}}
            <div class="flex items-center justify-center">
                <div class="relative">
                    <div class="w-1.5 h-1.5 rounded-full bg-primary-600 dark:bg-primary-400"></div>
                    <div class="absolute inset-0 w-1.5 h-1.5 rounded-full bg-primary-500 dark:bg-primary-400 blur-sm animate-pulse"></div>
                </div>
            </div>
        @else
            {{-- Hover arrow indicator --}}
            <div class="opacity-0 -translate-x-2 group-hover/link:opacity-100 group-hover/link:translate-x-0 transition-all duration-300 ease-out">
                <x-ui.icon icon="arrow-right" class="w-3.5 h-3.5 text-secondary-400 dark:text-secondary-500" />
            </div>
        @endif
    </div>
</a>
