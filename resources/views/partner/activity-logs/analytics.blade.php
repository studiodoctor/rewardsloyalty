{{--
  Reward Loyalty - Proprietary Software
  Copyright (c) 2025 NowSquare. All rights reserved.
  See LICENSE file for terms.

  Activity Log Analytics Dashboard - Partner View
  Provides scoped audit trail insights for partner's entities.
--}}

@extends('partner.layouts.default')

@section('page_title', trans('common.activity_log_analytics') . config('default.page_title_delimiter') . config('default.app_name'))

@section('content')
<div class="w-full max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-8">
    
    {{-- Page Header --}}
    <x-ui.page-header
        icon="activity"
        :title="trans('common.activity_log_analytics')"
        :description="trans('common.activity_log_analytics_description')"
    >
        <x-slot name="actions">
            {{-- Range Selector --}}
            <div class="relative group min-w-[200px]">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                    <x-ui.icon icon="calendar" class="h-5 w-5 text-secondary-400 group-hover:text-primary-500 transition-colors" />
                </div>
                <select id="range"
                    class="appearance-none w-full bg-white dark:bg-secondary-800 border border-stone-200 dark:border-secondary-700 text-secondary-700 dark:text-secondary-300 text-sm rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 pl-12 pr-12 py-3 transition-all cursor-pointer hover:border-stone-300 dark:hover:border-secondary-600 shadow-sm">
                        <option value="7" @if($range == '7') selected @endif>{{ trans('common.last_7_days') }}</option>
                        <option value="14" @if($range == '14') selected @endif>{{ trans('common.last_14_days') }}</option>
                        <option value="30" @if($range == '30') selected @endif>{{ trans('common.last_30_days') }}</option>
                        <option value="90" @if($range == '90') selected @endif>{{ trans('common.last_90_days') }}</option>
                        <option value="365" @if($range == '365') selected @endif>{{ trans('common.last_year') }}</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <x-ui.icon icon="chevron-down" class="h-4 w-4 text-secondary-400 group-hover:text-secondary-600 dark:group-hover:text-secondary-300 transition-colors" />
                </div>
            </div>
            {{-- View Full List Button --}}
            <a href="{{ route('partner.data.list', ['name' => 'activity-logs']) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 
                       text-sm font-medium text-white 
                       bg-primary-600 hover:bg-primary-500
                       rounded-xl shadow-sm hover:shadow-md
                       focus:outline-none focus:ring-2 focus:ring-primary-500/20
                       transition-all duration-200 active:scale-[0.98]">
                <x-ui.icon icon="list" class="w-4 h-4" />
                <span class="hidden sm:inline">{{ trans('common.view_all_logs') }}</span>
            </a>
        </x-slot>
    </x-ui.page-header>

    {{-- Content Grid: All Analytics Cards --}}
    <div class="space-y-6">
        {{-- Summary Metrics Cards - Clean elevated design --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            {{-- Total Activities - Deep Sapphire Blue --}}
            <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-2xl shadow-gray-200/50 dark:shadow-none dark:border dark:border-secondary-800 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-[#0047AB]/10 dark:bg-[#0047AB]/20 rounded-xl">
                        <x-ui.icon icon="activity" class="w-6 h-6 text-[#0047AB] dark:text-blue-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-secondary-500 dark:text-secondary-400">{{ trans('common.total_activities') }}</p>
                        <p class="text-3xl font-extrabold text-[#0047AB] dark:text-white">
                            <span class="format-number">{{ $metrics['total'] }}</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Today - Vivid Emerald Green (positive recent activity) --}}
            <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-2xl shadow-gray-200/50 dark:shadow-none dark:border dark:border-secondary-800 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                            <x-ui.icon icon="clock" class="w-6 h-6 text-[#10B981] dark:text-emerald-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-secondary-500 dark:text-secondary-400">{{ trans('common.today') }}</p>
                            <p class="text-3xl font-extrabold text-secondary-900 dark:text-white">
                                <span class="format-number">{{ $metrics['today'] }}</span>
                            </p>
                        </div>
                    </div>
                    @include('partner.activity-logs.partials.diff-badge', ['diff' => $todayDiff])
                </div>
            </div>

            {{-- This Week - Deep Teal --}}
            <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-2xl shadow-gray-200/50 dark:shadow-none dark:border dark:border-secondary-800 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-teal-50 dark:bg-teal-900/20 rounded-xl">
                            <x-ui.icon icon="calendar-days" class="w-6 h-6 text-[#007A65] dark:text-teal-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-secondary-500 dark:text-secondary-400">{{ trans('common.this_week') }}</p>
                            <p class="text-3xl font-extrabold text-secondary-900 dark:text-white">
                                <span class="format-number">{{ $metrics['this_week'] }}</span>
                            </p>
                        </div>
                    </div>
                    @include('partner.activity-logs.partials.diff-badge', ['diff' => $weekDiff])
                </div>
            </div>

            {{-- This Month - Platform Accent Blue --}}
            <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-2xl shadow-gray-200/50 dark:shadow-none dark:border dark:border-secondary-800 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                            <x-ui.icon icon="calendar" class="w-6 h-6 text-[#3B82F6] dark:text-blue-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-secondary-500 dark:text-secondary-400">{{ trans('common.this_month') }}</p>
                            <p class="text-3xl font-extrabold text-secondary-900 dark:text-white">
                                <span class="format-number">{{ $metrics['this_month'] }}</span>
                            </p>
                        </div>
                    </div>
                    @include('partner.activity-logs.partials.diff-badge', ['diff' => $monthDiff])
                </div>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Activity Timeline Chart --}}
            <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-2xl shadow-gray-200/50 dark:shadow-none dark:border dark:border-secondary-800 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-[#1F2937] dark:text-white flex items-center">
                        <x-ui.icon icon="trending-up" class="w-5 h-5 mr-2 text-[#0047AB]" />
                        {{ trans('common.activity_timeline') }}
                    </h3>
                    <span class="text-sm text-secondary-500 dark:text-secondary-400">
                        {{ trans('common.total') }}: <span class="format-number font-semibold text-[#1F2937] dark:text-white">{{ $timeline['total'] }}</span>
                    </span>
                </div>
                <div id="activity-timeline-chart"
                    data-chart-type="line"
                    data-labels='@json($timeline['labels'])'
                    data-values='@json($timeline['values'])'
                    data-label="{{ trans('common.activities') }}"
                    data-color="#0047AB"
                    data-height="280"></div>
            </div>

            {{-- Event Breakdown Donut - Semantic Rich Colors --}}
            <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-2xl shadow-gray-200/50 dark:shadow-none dark:border dark:border-secondary-800 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-[#1F2937] dark:text-white flex items-center">
                        <x-ui.icon icon="pie-chart" class="w-5 h-5 mr-2 text-[#0047AB]" />
                        {{ trans('common.events_breakdown') }}
                    </h3>
                </div>
                <div id="event-breakdown-chart"
                    data-chart-type="donut"
                    data-labels='@json($eventBreakdown->keys())'
                    data-values='@json($eventBreakdown->values())'
                    data-colors='["#10B981","#007A65","#F26419","#0047AB","#7C3AED","#3B82F6"]'
                    data-total-label="{{ trans('common.total') }}"
                    data-height="280"></div>
            </div>
        </div>

        {{-- Second Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Log Category Breakdown --}}
            <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-2xl shadow-gray-200/50 dark:shadow-none dark:border dark:border-secondary-800 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-[#1F2937] dark:text-white flex items-center">
                        <x-ui.icon icon="folder" class="w-5 h-5 mr-2 text-[#0047AB]" />
                        {{ trans('common.categories_breakdown') }}
                    </h3>
                </div>
                <div id="category-breakdown-chart"
                    data-chart-type="donut"
                    data-labels='@json($logNameBreakdown->keys())'
                    data-values='@json($logNameBreakdown->values())'
                    data-colors='["#0047AB","#7C3AED","#F26419","#10B981","#007A65","#3B82F6"]'
                    data-total-label="{{ trans('common.total') }}"
                    data-height="280"></div>
            </div>

            {{-- User Type Breakdown - Partner Blue, Member Orange --}}
            <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-2xl shadow-gray-200/50 dark:shadow-none dark:border dark:border-secondary-800 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-[#1F2937] dark:text-white flex items-center">
                        <x-ui.icon icon="users" class="w-5 h-5 mr-2 text-[#0047AB]" />
                        {{ trans('common.user_types_breakdown') }}
                    </h3>
                </div>
                <div id="user-type-breakdown-chart"
                    data-chart-type="donut"
                    data-labels='@json($causerTypeBreakdown->keys())'
                    data-values='@json($causerTypeBreakdown->values())'
                    data-colors='["#0047AB","#F26419","#10B981","#7C3AED","#007A65"]'
                    data-total-label="{{ trans('common.total') }}"
                    data-height="280"></div>
            </div>
        </div>

        {{-- Bottom Row: Most Active Users + Recent Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Most Active Users --}}
            <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-2xl shadow-gray-200/50 dark:shadow-none dark:border dark:border-secondary-800 p-6">
                <h3 class="text-lg font-bold text-[#1F2937] dark:text-white flex items-center mb-6">
                    <x-ui.icon icon="trophy" class="w-5 h-5 mr-2 text-[#F26419]" />
                    {{ trans('common.most_active_users') }}
                </h3>
                <div class="divide-y divide-gray-100 dark:divide-secondary-800">
                    @forelse($mostActiveUsers->take(5) as $index => $user)
                        @php
                            // Rich color palette for rank badges
                            $rankClass = match($index) {
                                0 => 'bg-[#F26419]/15 text-[#F26419]',
                                1 => 'bg-[#0047AB]/15 text-[#0047AB]',
                                2 => 'bg-[#10B981]/15 text-[#10B981]',
                                default => 'bg-secondary-100 text-secondary-500 dark:bg-secondary-800 dark:text-secondary-400',
                            };
                        @endphp
                        <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                            <div class="flex items-center">
                                <span class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold {{ $rankClass }}">
                                    {{ $index + 1 }}
                                </span>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-[#1F2937] dark:text-white">{{ $user['name'] }}</p>
                                    <p class="text-xs text-secondary-500 dark:text-secondary-400">{{ $user['type'] }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-[#1F2937] dark:text-white tabular-nums format-number">{{ $user['count'] }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 text-center py-4">{{ trans('common.no_data_available') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- Recent Activity - Clean feed design --}}
            <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-2xl shadow-gray-200/50 dark:shadow-none dark:border dark:border-secondary-800 p-6">
                <h3 class="text-lg font-bold text-[#1F2937] dark:text-white flex items-center mb-6">
                    <x-ui.icon icon="clock" class="w-5 h-5 mr-2 text-[#0047AB]" />
                    {{ trans('common.recent_activity') }}
                </h3>
                <div class="divide-y divide-gray-100 dark:divide-secondary-800 max-h-80 overflow-y-auto activity-feed-scroll">
                    @forelse($recentActivities->take(7) as $index => $activity)
                        @php
                            $iconName = match($activity->event) {
                                'created' => 'plus',
                                'updated' => 'pencil',
                                'deleted' => 'trash',
                                'login' => 'log-in',
                                'logout' => 'log-out',
                                default => 'activity',
                            };
                            // Rich color rotation for avatars/icons
                            $colorPalette = ['#0047AB', '#F26419', '#10B981', '#7C3AED', '#007A65', '#3B82F6', '#DC2626'];
                            $avatarColor = $colorPalette[$index % count($colorPalette)];
                            $iconClass = match($activity->event) {
                                'created' => 'text-[#10B981]',
                                'updated' => 'text-[#F26419]',
                                'deleted' => 'text-[#DC2626]',
                                'login' => 'text-[#0047AB]',
                                'logout' => 'text-[#7C3AED]',
                                default => 'text-secondary-600 dark:text-secondary-400',
                            };
                        @endphp
                        <div class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="flex-shrink-0 mt-0.5">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full" style="background-color: {{ $avatarColor }}15;">
                                    <x-ui.icon :icon="$iconName" class="w-3.5 h-3.5 {{ $iconClass }}" />
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-[#1F2937] dark:text-white leading-snug">{{ $activity->description }}</p>
                                <p class="text-xs text-secondary-500 dark:text-secondary-400 mt-0.5">
                                    <span class="text-[#3B82F6] hover:underline cursor-pointer">{{ $activity->causer?->name ?? $activity->causer?->email ?? 'System' }}</span>
                                    <span class="mx-1">·</span>
                                    {{ $activity->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 text-center py-4">{{ trans('common.no_recent_activity') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    {{-- End Content Grid --}}
</div>

{{-- Range selector script --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const rangeSelect = document.querySelector('#range');
        if (rangeSelect) {
            rangeSelect.addEventListener('change', () => {
                window.location.href = window.location.pathname + '?range=' + encodeURIComponent(rangeSelect.value);
            });
        }
    });
</script>
@stop

{{-- Charts initialize automatically via resources/js/analytics.js --}}

