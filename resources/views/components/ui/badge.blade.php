{{--
Premium Badge Component

Modern pill badges with gradient backgrounds and status dots.
Inspired by Linear's labels and Vercel's status indicators.

@props
- variant: 'primary'|'success'|'warning'|'danger'|'info'|'neutral' (default: 'neutral')
- size: 'sm'|'md'|'lg' (default: 'md')
- dot: boolean - Show status dot (default: false)
- pulse: boolean - Animate dot (default: false)
--}}

@props([
    'variant' => 'neutral',
    'size' => 'md',
    'dot' => false,
    'pulse' => false,
])

@php
$variants = [
    'primary' => 'bg-primary-50 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 ring-1 ring-primary-200/50 dark:ring-primary-500/30',
    'success' => 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-200/50 dark:ring-emerald-500/30',
    'warning' => 'bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200/50 dark:ring-amber-500/30',
    'danger' => 'bg-red-50 dark:bg-red-500/15 text-red-700 dark:text-red-300 ring-1 ring-red-200/50 dark:ring-red-500/30',
    'info' => 'bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 ring-1 ring-sky-200/50 dark:ring-sky-500/30',
    'neutral' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 ring-1 ring-slate-200/50 dark:ring-slate-700/50',
];

$dotColors = [
    'primary' => 'bg-primary-500',
    'success' => 'bg-emerald-500',
    'warning' => 'bg-amber-500',
    'danger' => 'bg-red-500',
    'info' => 'bg-sky-500',
    'neutral' => 'bg-slate-500',
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs gap-1',
    'md' => 'px-2.5 py-1 text-xs gap-1.5',
    'lg' => 'px-3 py-1.5 text-sm gap-2',
];

$classes = implode(' ', [
    'inline-flex items-center font-medium rounded-full transition-all duration-200',
    $variants[$variant] ?? $variants['neutral'],
    $sizes[$size] ?? $sizes['md'],
]);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="relative flex h-2 w-2">
            @if($pulse)
                <span class="absolute inline-flex h-full w-full rounded-full opacity-75 animate-ping {{ $dotColors[$variant] ?? $dotColors['neutral'] }}"></span>
            @endif
            <span class="relative inline-flex rounded-full h-2 w-2 {{ $dotColors[$variant] ?? $dotColors['neutral'] }}"></span>
        </span>
    @endif
    
    {{ $slot }}
</span>
