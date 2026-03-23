{{--
Premium Stat Card Component

Modern metric display with gradient icons and trend indicators.
Inspired by Robinhood and Revolut's analytics cards.

@props
- label: string - Metric label
- value: string|number - Main value
- change: string - Change indicator
- changeType: 'positive'|'negative'|'neutral'
- icon: string - Lucide icon
- iconColor: string - Icon gradient classes
- trend: array - Optional sparkline data
--}}

@props([
    'label' => '',
    'value' => '',
    'change' => null,
    'changeType' => 'neutral',
    'icon' => null,
    'iconColor' => 'from-primary-500 to-primary-600',
    'trend' => null,
])

@php
$changeConfig = [
    'positive' => ['color' => 'text-emerald-600 dark:text-emerald-400', 'bg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'icon' => 'trending-up'],
    'negative' => ['color' => 'text-red-600 dark:text-red-400', 'bg' => 'bg-red-50 dark:bg-red-500/10', 'icon' => 'trending-down'],
    'neutral' => ['color' => 'text-slate-600 dark:text-slate-400', 'bg' => 'bg-slate-50 dark:bg-slate-500/10', 'icon' => 'minus'],
];
$cc = $changeConfig[$changeType] ?? $changeConfig['neutral'];
@endphp

<div class="group relative overflow-hidden bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl rounded-2xl border border-slate-200/50 dark:border-slate-700/50 shadow-lg shadow-slate-900/5 dark:shadow-slate-900/50 p-5 transition-all duration-300 hover:shadow-xl hover:-translate-y-0.5">
    {{-- Decorative gradient --}}
    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br {{ $iconColor }} opacity-5 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2 group-hover:opacity-10 transition-opacity"></div>
    
    <div class="relative flex items-start justify-between">
        <div class="flex-1 min-w-0">
            {{-- Label --}}
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                {{ $label }}
            </p>
            
            {{-- Value --}}
            <p class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight transition-transform duration-300 group-hover:scale-105 origin-left">
                {{ $value }}
            </p>
            
            {{-- Change Indicator --}}
            @if($change)
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium {{ $cc['bg'] }} {{ $cc['color'] }}">
                        <x-ui.icon :icon="$cc['icon']" class="w-3.5 h-3.5" />
                        {{ $change }}
                    </span>
                </div>
            @endif
        </div>
        
        {{-- Icon --}}
        @if($icon)
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $iconColor }} flex items-center justify-center shadow-lg transition-all duration-300 group-hover:scale-110 group-hover:rotate-3">
                <x-ui.icon :icon="$icon" class="w-6 h-6 text-white" />
            </div>
        @endif
    </div>
    
    {{-- Trend Sparkline --}}
    @if($trend)
        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-700/50">
            <div class="h-10 flex items-end gap-0.5">
                @foreach($trend as $point)
                    <div 
                        class="flex-1 bg-gradient-to-t {{ $iconColor }} rounded-t opacity-60 hover:opacity-100 transition-all duration-200" 
                        style="height: {{ $point }}%"
                    ></div>
                @endforeach
            </div>
        </div>
    @endif
</div>
