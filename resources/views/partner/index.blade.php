{{--
    Reward Loyalty - Proprietary Software
    Copyright (c) 2025 NowSquare. All rights reserved.
    See LICENSE file for terms.

    Partner Dashboard - Welcome Home 4.0 (Final Polish)
    
    Design Philosophy:
    "The details are not the details. They make the design." — Charles Eames
    
    Layout Structure (The "Linear" Look):
    1. TOP: Primary Stats + Status Banners — Immediate health check
    2. MIDDLE: Week Highlights + Activity Feed — Key data and real-time events side-by-side
    3. BOTTOM: Quick Navigation — Tool links, separated for clarity
--}}

@extends('partner.layouts.default')

@section('page_title', trans('common.partner') . config('default.page_title_delimiter') . trans('common.dashboard') . config('default.page_title_delimiter') . config('default.app_name'))

@section('content')
<div class="min-h-screen relative" x-data="partnerDashboard()" x-init="initCounters()">
    {{-- Ambient Background — Ultra-subtle depth --}}
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-1/4 right-0 w-[800px] h-[800px] bg-gradient-to-br from-primary-500/5 via-transparent to-transparent rounded-full blur-3xl"></div>
        <div class="absolute -bottom-1/4 -left-1/4 w-[600px] h-[600px] bg-gradient-to-tr from-emerald-500/4 via-transparent to-transparent rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-7xl mx-auto px-6 py-8 md:px-10 md:py-14 lg:py-16">
        
        {{-- ═══════════════════════════════════════════════════════════════════════════
            HERO SECTION — Minimal, Impactful
        ═══════════════════════════════════════════════════════════════════════════ --}}
        <header class="mb-12 md:mb-16 animate-fade-in">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6">
                {{-- Greeting — Bolder, more breathing room --}}
                <div class="space-y-2">
                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-secondary-900 dark:text-white tracking-tight leading-tight">
                        <span x-data="{ greeting: '{{ $greeting }}' }" x-init="
                            const h = new Date().getHours();
                            greeting = h >= 5 && h < 12 ? '{{ trans('common.good_morning') }}'
                                : h < 17 ? '{{ trans('common.good_afternoon') }}'
                                : h < 21 ? '{{ trans('common.good_evening') }}'
                                : '{{ trans('common.good_night') }}';
                        " x-text="greeting">{{ $greeting }}</span>, <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-primary-400 dark:from-primary-400 dark:to-primary-300">{{ auth('partner')->user()->name }}</span>
                    </h1>
                    <p class="text-lg text-secondary-400 dark:text-secondary-500 font-light">
                        {{ trans('common.partnerDashboardBlocksTitle') }}
                    </p>
                </div>
                
                {{-- Quick Actions — Refined --}}
                <div class="flex items-center gap-3">
                    {{-- Create Dropdown (Dark) --}}
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl font-medium text-sm
                                bg-secondary-900 dark:bg-white text-white dark:text-secondary-900 
                                shadow-xl shadow-secondary-900/10 dark:shadow-white/10
                                hover:shadow-2xl hover:shadow-secondary-900/20 dark:hover:shadow-white/20
                                hover:scale-[1.02] active:scale-[0.98]
                                transition-all duration-300 ease-out">
                            <x-ui.icon icon="plus" class="w-4 h-4" />
                            {{ trans('common.create') }}
                            <x-ui.icon icon="chevron-down" class="w-3.5 h-3.5 transition-transform duration-300 ease-out" ::class="{ 'rotate-180': open }" />
                        </button>
                        
                        <div x-show="open" x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                            class="absolute right-0 mt-3 w-64 origin-top-right z-50">
                            <div class="bg-white dark:bg-secondary-900 rounded-2xl shadow-2xl shadow-secondary-900/20 dark:shadow-black/40 border border-secondary-100 dark:border-secondary-800 overflow-hidden">
                                <div class="p-2">
                                    {{-- Loyalty Cards + Rewards --}}
                                    @if(auth('partner')->user()->loyalty_cards_permission)
                                    <a href="{{ route('partner.data.insert', ['name' => 'cards']) }}"
                                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all duration-200">
                                        <div class="w-8 h-8 rounded-lg bg-primary-500/10 flex items-center justify-center">
                                            <x-ui.icon icon="credit-card" class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                                        </div>
                                        <span class="font-medium">{{ trans('common.loyalty_card') }}</span>
                                    </a>
                                    <a href="{{ route('partner.data.insert', ['name' => 'rewards']) }}"
                                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all duration-200">
                                        <div class="w-8 h-8 rounded-lg bg-pink-500/10 flex items-center justify-center">
                                            <x-ui.icon icon="gift" class="w-4 h-4 text-pink-600 dark:text-pink-400" />
                                        </div>
                                        <span class="font-medium">{{ trans('common.reward') }}</span>
                                    </a>
                                    
                                    <div class="my-2 mx-4 h-px bg-secondary-100 dark:bg-secondary-800"></div>
                                    @endif
                                    
                                    {{-- Stamp Cards & Vouchers --}}
                                    @if(auth('partner')->user()->stamp_cards_permission)
                                    <a href="{{ route('partner.data.insert', ['name' => 'stamp-cards']) }}"
                                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all duration-200">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                                            <x-ui.icon icon="stamp" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                        </div>
                                        <span class="font-medium">{{ trans('common.stamp_card') }}</span>
                                    </a>
                                    @endif

                                    @if(auth('partner')->user()->vouchers_permission)
                                    <a href="{{ route('partner.data.insert', ['name' => 'vouchers']) }}"
                                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all duration-200">
                                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center">
                                            <x-ui.icon icon="ticket" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                                        </div>
                                        <span class="font-medium">{{ trans('common.voucher') }}</span>
                                    </a>
                                    @endif
                                    
                                    <div class="my-2 mx-4 h-px bg-secondary-100 dark:bg-secondary-800"></div>
                                    
                                    {{-- Team & Organization --}}
                                    <a href="{{ route('partner.data.insert', ['name' => 'clubs']) }}"
                                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all duration-200">
                                        <div class="w-8 h-8 rounded-lg bg-violet-500/10 flex items-center justify-center">
                                            <x-ui.icon icon="users" class="w-4 h-4 text-violet-600 dark:text-violet-400" />
                                        </div>
                                        <span class="font-medium">{{ trans('common.club') }}</span>
                                    </a>
                                    <a href="{{ route('partner.data.insert', ['name' => 'staff']) }}"
                                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all duration-200">
                                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
                                            <x-ui.icon icon="briefcase" class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                        </div>
                                        <span class="font-medium">{{ trans('common.staff_member') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Analytics Dropdown (Light) --}}
                    @if(auth('partner')->user()->loyalty_cards_permission || auth('partner')->user()->stamp_cards_permission || auth('partner')->user()->vouchers_permission)
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl font-medium text-sm
                                text-secondary-600 dark:text-secondary-400 
                                bg-secondary-100 dark:bg-secondary-800
                                hover:bg-secondary-200 dark:hover:bg-secondary-700
                                transition-all duration-200">
                            <x-ui.icon icon="bar-chart-2" class="w-4 h-4" />
                            <span class="hidden sm:inline">{{ trans('common.analytics') }}</span>
                            <x-ui.icon icon="chevron-down" class="w-3.5 h-3.5 transition-transform duration-300 ease-out" ::class="{ 'rotate-180': open }" />
                        </button>
                        
                        <div x-show="open" x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                            class="absolute right-0 mt-3 w-56 origin-top-right z-50">
                            <div class="bg-white dark:bg-secondary-900 rounded-2xl shadow-2xl shadow-secondary-900/20 dark:shadow-black/40 border border-secondary-100 dark:border-secondary-800 overflow-hidden">
                                <div class="p-2">
                                    @if(auth('partner')->user()->loyalty_cards_permission)
                                    <a href="{{ route('partner.analytics') }}"
                                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all duration-200">
                                        <div class="w-8 h-8 rounded-lg bg-primary-500/10 flex items-center justify-center">
                                            <x-ui.icon icon="credit-card" class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                                        </div>
                                        <span class="font-medium">{{ trans('common.loyalty_cards') }}</span>
                                    </a>
                                    @endif
                                    
                                    @if(auth('partner')->user()->stamp_cards_permission)
                                    <a href="{{ route('partner.stamp-card-analytics') }}"
                                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all duration-200">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                                            <x-ui.icon icon="stamp" class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                        </div>
                                        <span class="font-medium">{{ trans('common.stamp_cards') }}</span>
                                    </a>
                                    @endif

                                    @if(auth('partner')->user()->vouchers_permission)
                                    <a href="{{ route('partner.voucher-analytics') }}"
                                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-secondary-700 dark:text-secondary-300 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all duration-200">
                                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center">
                                            <x-ui.icon icon="ticket" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                                        </div>
                                        <span class="font-medium">{{ trans('common.vouchers') }}</span>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </header>

        {{-- ═══════════════════════════════════════════════════════════════════════════
            TOP SECTION: KEY METRICS — Animated Counters, Generous Spacing
        ═══════════════════════════════════════════════════════════════════════════ --}}
        <section class="mb-14 animate-fade-in" style="animation-delay: 80ms;">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                {{-- Members --}}
                <div class="group relative bg-white dark:bg-secondary-900 rounded-3xl p-8 
                    border border-secondary-100 dark:border-secondary-800
                    hover:border-secondary-200 dark:hover:border-secondary-700
                    shadow-sm hover:shadow-xl hover:shadow-secondary-900/[0.03] dark:hover:shadow-black/10
                    transition-all duration-500 ease-out">
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-500 ease-out">
                            <x-ui.icon icon="users" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                    <p class="text-4xl md:text-5xl font-bold text-secondary-900 dark:text-white tabular-nums tracking-tight format-number"
                       x-text="animatedMembers.toLocaleString()">
                        {{ $dashboardData['metrics']['total_members'] }}
                    </p>
                    <p class="text-sm text-secondary-400 dark:text-secondary-500 mt-2 font-medium">{{ trans('common.total_members') }}</p>
                </div>

                {{-- Transactions This Month --}}
                <div class="group relative bg-white dark:bg-secondary-900 rounded-3xl p-8 
                    border border-secondary-100 dark:border-secondary-800
                    hover:border-secondary-200 dark:hover:border-secondary-700
                    shadow-sm hover:shadow-xl hover:shadow-secondary-900/[0.03] dark:hover:shadow-black/10
                    transition-all duration-500 ease-out">
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-500 ease-out">
                            <x-ui.icon icon="activity" class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        @if($dashboardData['metrics']['transaction_growth'] != 0)
                            <span @class([
                                'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-medium tracking-wide',
                                'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' => $dashboardData['metrics']['transaction_growth'] >= 0,
                                'bg-red-500/10 text-red-600 dark:text-red-400' => $dashboardData['metrics']['transaction_growth'] < 0,
                            ])>
                                <x-ui.icon :icon="$dashboardData['metrics']['transaction_growth'] >= 0 ? 'trending-up' : 'trending-down'" class="w-3 h-3" />
                                {{ $dashboardData['metrics']['transaction_growth'] >= 0 ? '+' : '' }}{{ number_format($dashboardData['metrics']['transaction_growth'], 0) }}%
                            </span>
                        @endif
                    </div>
                    <p class="text-4xl md:text-5xl font-bold text-secondary-900 dark:text-white tabular-nums tracking-tight format-number"
                       x-text="animatedTransactions.toLocaleString()">
                        {{ $dashboardData['metrics']['transactions_this_month'] }}
                    </p>
                    <p class="text-sm text-secondary-400 dark:text-secondary-500 mt-2 font-medium">{{ trans('common.transactions') }} · {{ trans('common.this_month') }}</p>
                </div>

                {{-- Active Programs (Loyalty + Stamp + Vouchers) --}}
                @php
                    $totalPrograms = ($dashboardData['metrics']['active_loyalty_cards'] ?? 0) 
                        + ($dashboardData['metrics']['active_stamp_cards'] ?? 0) 
                        + ($dashboardData['metrics']['active_vouchers'] ?? 0);
                @endphp
                <div class="group relative bg-white dark:bg-secondary-900 rounded-3xl p-8 
                    border border-secondary-100 dark:border-secondary-800
                    hover:border-secondary-200 dark:hover:border-secondary-700
                    shadow-sm hover:shadow-xl hover:shadow-secondary-900/[0.03] dark:hover:shadow-black/10
                    transition-all duration-500 ease-out">
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-violet-500/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-500 ease-out">
                            <x-ui.icon icon="layers" class="w-6 h-6 text-violet-600 dark:text-violet-400" />
                        </div>
                    </div>
                    <p class="text-4xl md:text-5xl font-bold text-secondary-900 dark:text-white tabular-nums tracking-tight format-number"
                       x-text="animatedCards.toLocaleString()">
                        {{ $totalPrograms }}
                    </p>
                    <p class="text-sm text-secondary-400 dark:text-secondary-500 mt-2 font-medium">{{ trans('common.active_programs') }}</p>
                    <p class="text-xs text-secondary-300 dark:text-secondary-600 mt-1">
                        {{ $dashboardData['metrics']['active_loyalty_cards'] }} {{ trans('common.loyalty') }} · {{ $dashboardData['metrics']['active_stamp_cards'] }} {{ trans('common.stamp') }} · {{ $dashboardData['metrics']['active_vouchers'] ?? 0 }} {{ trans('common.voucher') }}
                    </p>
                </div>
            </div>
        </section>

        {{-- ═══════════════════════════════════════════════════════════════════════════
            TOP SECTION: INSIGHT BAR — Data Storytelling (Narrative)
        ═══════════════════════════════════════════════════════════════════════════ --}}
        @if(!empty($dashboardData['insights']))
        <section class="mb-14 animate-fade-in" style="animation-delay: 160ms;">
            @foreach($dashboardData['insights'] as $insight)
                @php
                    $colorConfig = match($insight['color']) {
                        'emerald' => ['bg' => 'bg-emerald-500/5 dark:bg-emerald-500/10', 'icon' => 'text-emerald-600 dark:text-emerald-400', 'iconBg' => 'bg-emerald-500/10'],
                        'amber' => ['bg' => 'bg-amber-500/5 dark:bg-amber-500/10', 'icon' => 'text-amber-600 dark:text-amber-400', 'iconBg' => 'bg-amber-500/10'],
                        'violet' => ['bg' => 'bg-violet-500/5 dark:bg-violet-500/10', 'icon' => 'text-violet-600 dark:text-violet-400', 'iconBg' => 'bg-violet-500/10'],
                        'pink' => ['bg' => 'bg-pink-500/5 dark:bg-pink-500/10', 'icon' => 'text-pink-600 dark:text-pink-400', 'iconBg' => 'bg-pink-500/10'],
                        default => ['bg' => 'bg-primary-500/5 dark:bg-primary-500/10', 'icon' => 'text-primary-600 dark:text-primary-400', 'iconBg' => 'bg-primary-500/10'],
                    };
                @endphp
                <div class="flex items-center gap-5 p-6 rounded-2xl {{ $colorConfig['bg'] }}">
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl {{ $colorConfig['iconBg'] }} flex items-center justify-center">
                        <x-ui.icon :icon="$insight['icon']" class="w-5 h-5 {{ $colorConfig['icon'] }}" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-base font-semibold text-secondary-900 dark:text-white">{{ $insight['title'] }}</p>
                        <p class="text-sm text-secondary-600 dark:text-secondary-400 mt-0.5">{{ $insight['message'] }}</p>
                    </div>
                </div>
                @if(!$loop->last)
                    <div class="h-4"></div>
                @endif
            @endforeach
        </section>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════════════
            MIDDLE SECTION (TIER 2): Week Highlights + Activity Feed (Side-by-Side)
            Both columns MUST have exact same height for visual balance
        ═══════════════════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 mb-14 lg:items-stretch">
            
            {{-- LEFT COLUMN (3/5) — Week Summary --}}
            <div class="{{ auth('partner')->user()->activity_permission ? 'lg:col-span-3' : 'lg:col-span-5' }} flex">
                <section class="bg-white dark:bg-secondary-900 rounded-3xl p-8 border border-secondary-100 dark:border-secondary-800 shadow-sm w-full min-h-[400px] flex flex-col animate-fade-in" style="animation-delay: 240ms;">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-xl font-bold text-secondary-900 dark:text-white">{{ trans('common.this_week_highlights') }}</h2>
                        
                        {{-- Analytics Dropdown (Contextual) --}}
                        <div x-data="{ open: false }" @click.away="open = false" class="relative">
                            <button @click="open = !open" type="button"
                                class="text-sm text-secondary-400 hover:text-secondary-600 dark:hover:text-secondary-300 flex items-center gap-1.5 transition-colors">
                                {{ trans('common.program_analytics') }}
                                <x-ui.icon icon="chevron-down" class="w-3.5 h-3.5 transition-transform duration-300 ease-out" ::class="{ 'rotate-180': open }" />
                            </button>
                            
                            <div x-show="open" x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                                class="absolute right-0 mt-2 w-48 origin-top-right z-50">
                                <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-xl shadow-secondary-900/10 dark:shadow-black/30 border border-secondary-100 dark:border-secondary-800 overflow-hidden">
                                    <div class="py-1">
                                        @if(auth('partner')->user()->loyalty_cards_permission)
                                        <a href="{{ route('partner.analytics') }}"
                                            class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-secondary-600 dark:text-secondary-400 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-colors">
                                            <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                                            {{ trans('common.loyalty_cards') }}
                                        </a>
                                        @endif

                                        @if(auth('partner')->user()->stamp_cards_permission)
                                        <a href="{{ route('partner.stamp-card-analytics') }}"
                                            class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-secondary-600 dark:text-secondary-400 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-colors">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            {{ trans('common.stamp_cards') }}
                                        </a>
                                        @endif

                                        @if(auth('partner')->user()->vouchers_permission)
                                        <a href="{{ route('partner.voucher-analytics') }}"
                                            class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-secondary-600 dark:text-secondary-400 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-colors">
                                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                            {{ trans('common.vouchers') }}
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                        {{-- Best Day --}}
                        <div class="text-center">
                            <div class="w-11 h-11 mx-auto mb-3 rounded-xl bg-amber-500/10 flex items-center justify-center">
                                <x-ui.icon icon="trophy" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <p class="text-2xl font-bold text-secondary-900 dark:text-white">
                                {{ $dashboardData['weekSummary']['bestDay']['dayName'] ?? '—' }}
                            </p>
                            <p class="text-xs text-secondary-400 dark:text-secondary-500 mt-1">{{ trans('common.best_activity_day') }}</p>
                        </div>

                        {{-- New Members --}}
                        <div class="text-center">
                            <div class="w-11 h-11 mx-auto mb-3 rounded-xl bg-primary-500/10 flex items-center justify-center">
                                <x-ui.icon icon="user-plus" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                            </div>
                            <p class="text-2xl font-bold text-secondary-900 dark:text-white">
                                {{ $dashboardData['weekSummary']['newMembers'] > 0 ? '+' : '' }}<span class="format-number">{{ $dashboardData['weekSummary']['newMembers'] }}</span>
                            </p>
                            <p class="text-xs text-secondary-400 dark:text-secondary-500 mt-1">{{ trans('common.new_members_week') }}</p>
                        </div>

                        {{-- Points Issued --}}
                        <div class="text-center">
                            <div class="w-11 h-11 mx-auto mb-3 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                                <x-ui.icon icon="zap" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <p class="text-2xl font-bold text-secondary-900 dark:text-white format-number">
                                {{ $dashboardData['weekSummary']['pointsIssued'] }}
                            </p>
                            <p class="text-xs text-secondary-400 dark:text-secondary-500 mt-1">{{ trans('common.points_issued_week') }}</p>
                        </div>

                        {{-- Stamps Issued --}}
                        <div class="text-center">
                            <div class="w-11 h-11 mx-auto mb-3 rounded-xl bg-purple-500/10 flex items-center justify-center">
                                <x-ui.icon icon="stamp" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <p class="text-2xl font-bold text-secondary-900 dark:text-white format-number">
                                {{ $dashboardData['weekSummary']['stampsIssued'] }}
                            </p>
                            <p class="text-xs text-secondary-400 dark:text-secondary-500 mt-1">{{ trans('common.stamps_issued_week') }}</p>
                        </div>
                    </div>
                </section>
            </div>

            {{-- RIGHT COLUMN (2/5) — Activity Feed with Avatars --}}
            @if(auth('partner')->user()->activity_permission)
            <div class="lg:col-span-2 flex">
                <section class="bg-white dark:bg-secondary-900 rounded-3xl p-6 border border-secondary-100 dark:border-secondary-800 shadow-sm w-full min-h-[400px] flex flex-col animate-fade-in" style="animation-delay: 320ms;">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-secondary-900 dark:text-white flex items-center gap-2.5">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            {{ trans('common.activity_feed') }}
                        </h2>
                        <a href="{{ route('partner.data.list', ['name' => 'activity-logs']) }}" class="text-xs text-secondary-400 hover:text-secondary-600 dark:hover:text-secondary-300 transition-colors">
                            {{ trans('common.view_all') }}
                        </a>
                    </div>
                    
                    @if($dashboardData['recentActivity']->isNotEmpty())
                        <div class="space-y-1 flex-1 activity-feed-scroll">
                            @foreach($dashboardData['recentActivity']->take(7) as $activity)
                                @php
                                    $avatarBg = match($activity['color']) {
                                        'emerald' => 'bg-emerald-500',
                                        'violet' => 'bg-violet-500',
                                        'purple' => 'bg-purple-500',
                                        'pink' => 'bg-pink-500',
                                        default => 'bg-secondary-400',
                                    };
                                @endphp
                                <div class="group flex items-start gap-3 p-3 -mx-1 rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-colors duration-200">
                                    {{-- Avatar with Initial --}}
                                    <div class="flex-shrink-0 w-9 h-9 rounded-full {{ $avatarBg }} flex items-center justify-center text-white text-xs font-semibold uppercase">
                                        {{ substr($activity['member']?->name ?? $activity['member']?->email ?? '?', 0, 1) }}
                                    </div>
                                    <div class="flex-1 min-w-0 pt-0.5">
                                        <p class="text-sm text-secondary-900 dark:text-white">
                                            <span class="font-medium">{{ $activity['member']?->name ?? $activity['member']?->email ?? trans('common.unknown') }}</span>
                                            <span class="text-secondary-400 dark:text-secondary-500 font-normal"> · {{ $activity['description'] }}</span>
                                        </p>
                                        <p class="text-xs text-secondary-400 dark:text-secondary-500 mt-1">
                                            {{ $activity['created_at']->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <div class="w-16 h-16 rounded-2xl bg-secondary-100 dark:bg-secondary-800 flex items-center justify-center mb-4">
                                <x-ui.icon icon="activity" class="w-8 h-8 text-secondary-300 dark:text-secondary-600" />
                            </div>
                            <p class="text-sm font-medium text-secondary-400 dark:text-secondary-500">{{ trans('common.no_activity_yet') }}</p>
                            <p class="text-xs text-secondary-300 dark:text-secondary-600 mt-1 max-w-[200px]">{{ trans('common.activity_will_appear') }}</p>
                        </div>
                    @endif
                </section>
            </div>
            @endif
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════════════
            PLAN USAGE — Limits Visualization (SaaS Style)
            Only shown if at least one limit is set (not -1)
        ═══════════════════════════════════════════════════════════════════════════ --}}
        @php
            $limits = [
                'loyalty_cards' => ['limit' => auth('partner')->user()->loyalty_cards_limit, 'used' => $dashboardData['metrics']['active_loyalty_cards'] ?? 0, 'icon' => 'credit-card', 'color' => 'primary', 'label' => trans('common.loyalty_cards')],
                'rewards' => ['limit' => auth('partner')->user()->rewards_limit, 'used' => $dashboardData['metrics']['total_rewards'] ?? 0, 'icon' => 'gift', 'color' => 'pink', 'label' => trans('common.rewards')],
                'stamp_cards' => ['limit' => auth('partner')->user()->stamp_cards_limit, 'used' => $dashboardData['metrics']['active_stamp_cards'] ?? 0, 'icon' => 'stamp', 'color' => 'emerald', 'label' => trans('common.stamp_cards')],
                'vouchers' => ['limit' => auth('partner')->user()->vouchers_limit, 'used' => $dashboardData['metrics']['active_vouchers'] ?? 0, 'icon' => 'ticket', 'color' => 'purple', 'label' => trans('common.vouchers')],
                'staff' => ['limit' => auth('partner')->user()->staff_members_limit, 'used' => $dashboardData['metrics']['staff_count'] ?? 0, 'icon' => 'briefcase', 'color' => 'amber', 'label' => trans('common.staff_members')],
            ];

            // Filter out unlimited items (-1)
            $activeLimits = array_filter($limits, fn($item) => $item['limit'] != -1);
        @endphp

        @if(!empty($activeLimits))
        <section class="mb-14 animate-fade-in" style="animation-delay: 360ms;">
            <h2 class="text-lg font-bold text-secondary-900 dark:text-white mb-6">{{ trans('common.plan_usage') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                @foreach($activeLimits as $key => $data)
                    @php
                        $percentage = $data['limit'] > 0 ? min(100, round(($data['used'] / $data['limit']) * 100)) : 0;
                        $isNearLimit = $percentage >= 80;
                        $isAtLimit = $data['used'] >= $data['limit'];
                        
                        $colors = match($data['color']) {
                            'emerald' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-600 dark:text-emerald-400', 'pale' => 'bg-emerald-500/10', 'bar' => 'bg-emerald-500'],
                            'purple' => ['bg' => 'bg-purple-500', 'text' => 'text-purple-600 dark:text-purple-400', 'pale' => 'bg-purple-500/10', 'bar' => 'bg-purple-500'],
                            'pink' => ['bg' => 'bg-pink-500', 'text' => 'text-pink-600 dark:text-pink-400', 'pale' => 'bg-pink-500/10', 'bar' => 'bg-pink-500'],
                            'amber' => ['bg' => 'bg-amber-500', 'text' => 'text-amber-600 dark:text-amber-400', 'pale' => 'bg-amber-500/10', 'bar' => 'bg-amber-500'],
                            default => ['bg' => 'bg-primary-500', 'text' => 'text-primary-600 dark:text-primary-400', 'pale' => 'bg-primary-500/10', 'bar' => 'bg-primary-500'],
                        };
                        
                        if ($isAtLimit) {
                             $colors['bar'] = 'bg-red-500';
                             $colors['text'] = 'text-red-600 dark:text-red-400';
                        }
                    @endphp
                    <div class="bg-white dark:bg-secondary-900 rounded-3xl p-6 border border-secondary-100 dark:border-secondary-800 shadow-sm flex flex-col justify-between h-full">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl {{ $colors['pale'] }} flex items-center justify-center">
                                <x-ui.icon :icon="$data['icon']" class="w-5 h-5 {{ $colors['text'] }}" />
                            </div>
                            <span class="text-xs font-semibold px-2 py-1 rounded-lg {{ $isAtLimit ? 'bg-red-500/10 text-red-600 dark:text-red-400' : 'bg-secondary-100 dark:bg-secondary-800 text-secondary-500' }}">
                                {{ $data['used'] }} / {{ $data['limit'] }}
                            </span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-secondary-900 dark:text-white text-sm mb-3">{{ $data['label'] }}</h3>
                            <div class="w-full bg-secondary-100 dark:bg-secondary-800 rounded-full h-2 overflow-hidden">
                                <div class="{{ $colors['bar'] }} h-2 rounded-full transition-all duration-1000 ease-out" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════════════
            BOTTOM SECTION: Quick Navigation — Tool links, separated for clarity
        ═══════════════════════════════════════════════════════════════════════════ --}}
        <section class="mb-14 animate-fade-in" style="animation-delay: 400ms;">
            <h2 class="text-lg font-bold text-secondary-900 dark:text-white mb-6">{{ trans('common.quick_navigation') }}</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($quickAccessLinks as $link)
                    @php
                        $iconBg = match($link['color']) {
                            'primary' => 'bg-primary-500/10',
                            'emerald', 'green' => 'bg-emerald-500/10',
                            'purple', 'violet' => 'bg-purple-500/10',
                            'amber' => 'bg-amber-500/10',
                            default => 'bg-secondary-500/10',
                        };
                        $iconColor = match($link['color']) {
                            'primary' => 'text-primary-600 dark:text-primary-400',
                            'emerald', 'green' => 'text-emerald-600 dark:text-emerald-400',
                            'purple', 'violet' => 'text-purple-600 dark:text-purple-400',
                            'amber' => 'text-amber-600 dark:text-amber-400',
                            default => 'text-secondary-600 dark:text-secondary-400',
                        };
                    @endphp
                    <a href="{{ $link['link'] }}"
                        class="group flex items-center gap-4 p-5 bg-white dark:bg-secondary-900 rounded-2xl 
                            border border-secondary-100 dark:border-secondary-800
                            hover:border-secondary-200 dark:hover:border-secondary-700
                            shadow-sm hover:shadow-lg hover:shadow-secondary-900/[0.03] dark:hover:shadow-black/10
                            transition-all duration-300 ease-out">
                        <div class="w-11 h-11 rounded-xl {{ $iconBg }} flex items-center justify-center group-hover:scale-110 transition-transform duration-300 ease-out">
                            <x-ui.icon :icon="$link['icon']" class="w-5 h-5 {{ $iconColor }}" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-secondary-900 dark:text-white">{{ $link['title'] }}</p>
                            <p class="text-xs text-secondary-400 dark:text-secondary-500 truncate mt-0.5">{{ $link['desc'] }}</p>
                        </div>
                        <x-ui.icon icon="chevron-right" class="w-4 h-4 text-secondary-300 dark:text-secondary-600 group-hover:translate-x-1 rtl:group-hover:-translate-x-1 transition-transform duration-300 ease-out" />
                    </a>
                @endforeach
            </div>
        </section>

        {{-- ═══════════════════════════════════════════════════════════════════════════
            TIERS DISCOVERY — Subtle Feature Promotion
        ═══════════════════════════════════════════════════════════════════════════ --}}
        @php
            $hasTiers = \App\Models\Tier::where('created_by', auth('partner')->id())->exists();
        @endphp
        
        @if(!$hasTiers && auth('partner')->user()->loyalty_cards_permission)
        <section class="animate-fade-in" style="animation-delay: 480ms;">
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/20 dark:to-orange-950/20 rounded-3xl p-8 border border-amber-200/30 dark:border-amber-800/20">
                <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/20 flex-shrink-0">
                        <x-ui.icon icon="award" class="w-7 h-7 text-white" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-secondary-900 dark:text-white">
                            {{ trans('common.tiers_feature_title') }}
                        </h3>
                        <p class="text-sm text-secondary-600 dark:text-secondary-400 mt-1">
                            {{ trans('common.tiers_feature_description') }}
                        </p>
                    </div>
                    <a href="{{ route('partner.data.list', ['name' => 'tiers']) }}"
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl font-medium text-sm
                            bg-secondary-900 dark:bg-white text-white dark:text-secondary-900 
                            shadow-lg shadow-secondary-900/10
                            hover:shadow-xl hover:scale-[1.02] active:scale-[0.98]
                            transition-all duration-300 ease-out">
                        <x-ui.icon icon="sparkles" class="w-4 h-4" />
                        {{ trans('common.setup_tiers') }}
                    </a>
                </div>
            </div>
        </section>
        @endif
    </div>
</div>

{{-- Alpine.js Dashboard Component with Animated Counters --}}
<script>
function partnerDashboard() {
    return {
        animatedMembers: 0,
        animatedTransactions: 0,
        animatedCards: 0,
        
        initCounters() {
            // Target values from server (including vouchers in total)
            const targetMembers = {{ $dashboardData['metrics']['total_members'] }};
            const targetTransactions = {{ $dashboardData['metrics']['transactions_this_month'] }};
            const targetCards = {{ ($dashboardData['metrics']['active_loyalty_cards'] ?? 0) + ($dashboardData['metrics']['active_stamp_cards'] ?? 0) + ($dashboardData['metrics']['active_vouchers'] ?? 0) }};
            
            // Animate with easeOutExpo for that premium feel
            this.animateValue('animatedMembers', targetMembers, 1200);
            this.animateValue('animatedTransactions', targetTransactions, 1400);
            this.animateValue('animatedCards', targetCards, 1600);
        },
        
        animateValue(property, target, duration) {
            const start = 0;
            const startTime = performance.now();
            
            const easeOutExpo = (t) => t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
            
            const animate = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                this[property] = Math.round(easeOutExpo(progress) * target);
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            };
            
            requestAnimationFrame(animate);
        },
        
        formatNumber(value) {
            return new Intl.NumberFormat().format(value);
        }
    }
}
</script>

{{-- Custom Animations & Styles --}}
<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
    animation: fade-in 0.6s cubic-bezier(0.16, 1, 0.3, 1) backwards;
}

/* Activity Feed — Show scrollbar only on hover (macOS/Linear style) */
.activity-feed-scroll {
    max-height: 420px;
    overflow-y: auto;
    scrollbar-width: none; /* Firefox: hidden by default */
}

.activity-feed-scroll::-webkit-scrollbar {
    width: 0; /* Hidden by default */
    transition: width 0.2s ease;
}

.activity-feed-scroll:hover {
    scrollbar-width: thin; /* Firefox: show on hover */
    scrollbar-color: oklch(0.85 0.005 250) transparent;
}

.activity-feed-scroll:hover::-webkit-scrollbar {
    width: 4px;
}

.activity-feed-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.activity-feed-scroll::-webkit-scrollbar-thumb {
    background: oklch(0.85 0.005 250);
    border-radius: 9999px;
}

.dark .activity-feed-scroll:hover {
    scrollbar-color: oklch(0.3 0.005 250) transparent;
}

.dark .activity-feed-scroll::-webkit-scrollbar-thumb {
    background: oklch(0.3 0.005 250);
}
</style>
@stop
