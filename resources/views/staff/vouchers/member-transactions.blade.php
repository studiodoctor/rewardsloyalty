{{--
Reward Loyalty - Proprietary Software
Copyright (c) 2025 NowSquare. All rights reserved.
See LICENSE file for terms.

Staff Voucher Transaction History Per Member
--}}

@extends('staff.layouts.default')

@section('page_title', trans('common.voucher_redemption_history') . config('default.page_title_delimiter') . ($member ? $member->name : '') . config('default.page_title_delimiter') . config('default.app_name'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-stone-50 via-white to-stone-100 dark:from-secondary-950 dark:via-secondary-900 dark:to-secondary-950">
    {{-- Ambient background effects --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/3 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 right-1/3 w-72 h-72 bg-violet-500/10 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 2s;"></div>
    </div>

    <div class="relative z-10 w-full max-w-lg mx-auto px-4 py-8 md:py-12">
        @if($member && $voucher)
            {{-- Header --}}
            <div class="animate-fade-in" style="animation-delay: 50ms;">
                <x-ui.page-header
                    icon="ticket"
                    iconBg="purple"
                    :title="trans('common.voucher_redemption_history')"
                    :description="$voucher->code"
                    compact
                />
            </div>

            {{-- Messages --}}
            <div class="mb-6 animate-fade-in-up" style="animation-delay: 75ms;">
                <x-forms.messages />
            </div>

            {{-- Voucher Card Display --}}
            <div class="mb-6 animate-fade-in-up" style="animation-delay: 100ms;">
                <x-member.voucher-card
                    :voucher="$voucher"
                    :member="$member"
                    :show-balance="true"
                    :links="false"
                />
            </div>

            {{-- Redeem Voucher Button --}}
            <div class="mb-6 animate-fade-in-up" style="animation-delay: 125ms;">
                <a href="{{ route('staff.vouchers.redeem.show', ['member_identifier' => $member->unique_identifier, 'voucher_id' => $voucher->id]) }}" 
                   class="relative group/btn block w-full">
                    {{-- Button glow --}}
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-500 via-violet-600 to-purple-500 rounded-2xl opacity-0 group-hover/btn:opacity-75 blur-md transition-all duration-300"></div>
                    
                    {{-- Button content --}}
                    <div class="relative flex items-center justify-center gap-3 px-8 py-4 bg-gradient-to-r from-purple-600 to-violet-600 hover:from-purple-500 hover:to-violet-500 text-white font-semibold rounded-2xl shadow-lg shadow-purple-900/20 transition-all duration-300 group-hover/btn:shadow-xl group-hover/btn:shadow-purple-500/30">
                        <x-ui.icon icon="scan" class="w-5 h-5 transition-transform group-hover/btn:scale-110" />
                        <span>{{ trans('common.redeem_voucher') }}</span>
                        <x-ui.icon icon="arrow-right" class="w-5 h-5 transition-transform group-hover/btn:translate-x-1" />
                    </div>
                </a>
            </div>

            {{-- Divider --}}
            <div class="relative my-8 animate-fade-in-up" style="animation-delay: 150ms;">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-stone-200 dark:border-secondary-800"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="px-4 text-xs font-medium text-secondary-500 dark:text-secondary-400 bg-gradient-to-br from-stone-50 via-white to-stone-100 dark:from-secondary-950 dark:via-secondary-900 dark:to-secondary-950 uppercase tracking-widest">
                        {{ trans('common.member') }}
                    </span>
                </div>
            </div>

            {{-- Member Card & History --}}
            <div class="space-y-6">
                <div class="animate-fade-in-up" style="animation-delay: 175ms;">
                    <x-member.member-card :member="$member" />
                </div>
                
                <div class="animate-fade-in-up" style="animation-delay: 200ms;">
                    <div class="bg-white dark:bg-secondary-900 rounded-2xl border border-stone-200 dark:border-secondary-800 shadow-lg">
                        {{-- History Header --}}
                        <div class="px-6 py-4 border-b border-stone-200 dark:border-secondary-800 bg-stone-50/50 dark:bg-secondary-800/50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-100 to-violet-200 dark:from-purple-900/50 dark:to-violet-800/50 flex items-center justify-center">
                                    <x-ui.icon icon="clock" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-secondary-900 dark:text-white">{{ trans('common.redemption_history') }}</h3>
                                    <p class="text-xs text-secondary-500 dark:text-secondary-400">{{ trans('common.member_redemptions_for_voucher') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        {{-- History Content --}}
                        <div class="p-6">
                            <x-member.voucher-history 
                                :member="$member" 
                                :voucher="$voucher"
                                :show-notes="true"
                                :show-staff="true" />
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Error State --}}
            <div class="space-y-6">
                <div class="animate-fade-in-up">
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200/50 dark:border-red-700/50 rounded-2xl p-8 text-center">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-red-100 dark:bg-red-900/50 flex items-center justify-center mb-4">
                            <x-ui.icon icon="user-x" class="w-8 h-8 text-red-500 dark:text-red-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-red-800 dark:text-red-300">{{ trans('common.member_not_found') }}</h3>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    @keyframes fade-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulse-slow {
        0%, 100% { opacity: 0.4; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.05); }
    }
    .animate-fade-in {
        animation: fade-in 0.6s ease-out forwards;
    }
    .animate-fade-in-up {
        animation: fade-in-up 0.6s ease-out forwards;
        opacity: 0;
    }
    .animate-pulse-slow {
        animation: pulse-slow 4s ease-in-out infinite;
    }
</style>
@stop
