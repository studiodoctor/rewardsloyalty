{{--
Premium Empty State Component

Beautiful empty states with gradient icons and clear CTAs.
Inspired by Linear and Notion's friendly empty states.

@props
- icon: string - Lucide icon name (default: 'inbox')
- title: string - Main heading
- description: string - Supporting text
- action: string - CTA button text
- actionHref: string - CTA link
- illustration: string - Optional illustration path
--}}

@props([
    'icon' => 'inbox',
    'title' => '',
    'description' => '',
    'action' => null,
    'actionHref' => null,
    'illustration' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center py-16 px-6']) }}>
    {{-- Icon or Illustration --}}
    @if($illustration)
        <img src="{{ $illustration }}" alt="{{ $title }}" class="w-48 h-48 mb-8 opacity-90">
    @else
        <div class="relative mb-8">
            {{-- Ambient glow --}}
            <div class="absolute inset-0 bg-primary-500/20 rounded-full blur-2xl scale-150"></div>
            
            {{-- Icon container --}}
            <div class="relative w-24 h-24 rounded-3xl bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 flex items-center justify-center shadow-xl shadow-slate-900/10 dark:shadow-slate-900/50">
                <x-ui.icon :icon="$icon" class="w-12 h-12 text-slate-400 dark:text-slate-500" />
            </div>
        </div>
    @endif
    
    {{-- Title --}}
    <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-3 tracking-tight">
        {{ $title }}
    </h3>
    
    {{-- Description --}}
    <p class="text-slate-600 dark:text-slate-400 max-w-sm mb-8 leading-relaxed">
        {{ $description }}
    </p>

    {{-- Action Button --}}
    @if($action && $actionHref)
        <a href="{{ $actionHref }}" class="group relative inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/25 hover:shadow-xl hover:shadow-primary-500/30 transition-all duration-300 hover:-translate-y-0.5">
            <x-ui.icon icon="plus" class="w-5 h-5 transition-transform group-hover:rotate-90 duration-300" />
            <span>{{ $action }}</span>
        </a>
    @endif
    
    {{-- Optional Slot --}}
    @if($slot->isNotEmpty())
        <div class="mt-6">
            {{ $slot }}
        </div>
    @endif
</div>
