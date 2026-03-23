@extends('staff.layouts.default')

@section('page_title', trans('common.stamp_card_history') . config('default.page_title_delimiter') . ($card ? $card->name : '') . config('default.page_title_delimiter') . ($member ? $member->name : '') . config('default.page_title_delimiter') . config('default.app_name'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-stone-50 via-white to-stone-100 dark:from-secondary-950 dark:via-secondary-900 dark:to-secondary-950">
    {{-- Ambient background effects --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute bottom-1/3 left-1/4 w-80 h-80 bg-teal-500/15 rounded-full blur-3xl animate-float-slow-delayed"></div>
        <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-emerald-400/10 rounded-full blur-2xl animate-pulse-glow"></div>
    </div>

    <div class="relative z-10 w-full max-w-lg mx-auto px-4 py-8 md:py-12">
        @if($member && $card)
            @php
                $stampIcon = $card->stamp_icon ?? '☕';
                $isEmoji = preg_match('/[^\x00-\x7F]/', $stampIcon);
            @endphp
            
            {{-- Header --}}
            <div class="animate-fade-in" style="animation-delay: 50ms;">
                <x-ui.page-header
                    icon="stamp"
                    iconBg="emerald"
                    :title="trans('common.stamp_card_history')"
                    compact
                />
            </div>

            {{-- Messages --}}
            <div class="mb-6 animate-fade-in-up" style="animation-delay: 75ms;">
                <x-forms.messages />
            </div>

            {{-- Card Display --}}
            <div class="mb-6 animate-fade-in-up" style="animation-delay: 100ms;">
                <x-member.stamp-card
                    :stamp-card="$card"
                    :member="$member"
                    :show-balance="true"
                    :links="false"
                />
            </div>

            {{-- Add Stamp Button --}}
            <div class="mb-6 animate-fade-in-up" style="animation-delay: 125ms;">
                <a href="{{ route('staff.stamps.add.show', ['member_identifier' => $member->unique_identifier, 'stamp_card_id' => $card->id]) }}" 
                   class="relative group/btn block w-full">
                    {{-- Button glow --}}
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500 via-teal-600 to-emerald-500 rounded-2xl opacity-0 group-hover/btn:opacity-75 blur-md transition-all duration-300"></div>
                    
                    {{-- Button content --}}
                    <div class="relative flex items-center justify-center gap-3 px-8 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold rounded-2xl shadow-lg shadow-emerald-900/20 transition-all duration-300 group-hover/btn:shadow-xl group-hover/btn:shadow-emerald-500/30">
                        @if($isEmoji)
                            <span class="text-xl">{{ $stampIcon }}</span>
                        @else
                            <x-ui.icon :icon="$stampIcon" class="w-5 h-5 transition-transform group-hover/btn:scale-110" />
                        @endif
                        <span>{{ trans('common.add_stamp') }}</span>
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
                    <x-member.member-card :member="$member" :club="$card->club" :show-tier="true" />
                </div>
                
                <div class="animate-fade-in-up" style="animation-delay: 200ms;">
                    <div class="bg-white dark:bg-secondary-900 rounded-2xl border border-stone-200 dark:border-secondary-800 shadow-lg">
                        {{-- History Header --}}
                        <div class="px-6 py-4 border-b border-stone-200 dark:border-secondary-800 bg-stone-50/50 dark:bg-secondary-800/50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-100 to-teal-200 dark:from-emerald-900/50 dark:to-teal-800/50 flex items-center justify-center">
                                    <x-ui.icon icon="clock" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-secondary-900 dark:text-white">{{ trans('common.stamp_history') }}</h3>
                                    <p class="text-xs text-secondary-500 dark:text-secondary-400">Stamps earned and rewards claimed</p>
                                </div>
                            </div>
                        </div>
                        
                        {{-- History Content --}}
                        <div class="p-6">
                            <x-member.stamp-history :stamp-card="$card" :member="$member" :show-notes="true" :show-attachments="true" :show-staff="true" />
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Error States --}}
            <div class="space-y-6">
                @if(!$member)
                    <div class="animate-fade-in-up">
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200/50 dark:border-red-700/50 rounded-2xl p-8 text-center">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-red-100 dark:bg-red-900/50 flex items-center justify-center mb-4">
                                <x-ui.icon icon="user-x" class="w-8 h-8 text-red-500 dark:text-red-400" />
                            </div>
                            <h3 class="text-lg font-semibold text-red-800 dark:text-red-300">{{ trans('common.member_not_found') }}</h3>
                        </div>
                    </div>
                @endif

                @if(!$card)
                    <div class="animate-fade-in-up" style="animation-delay: 50ms;">
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200/50 dark:border-red-700/50 rounded-2xl p-8 text-center">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-red-100 dark:bg-red-900/50 flex items-center justify-center mb-4">
                                <x-ui.icon icon="credit-card" class="w-8 h-8 text-red-500 dark:text-red-400" />
                            </div>
                            <h3 class="text-lg font-semibold text-red-800 dark:text-red-300">{{ trans('common.stamp_card_not_found') }}</h3>
                        </div>
                    </div>
                @endif
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
    @keyframes float-slow {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(30px, -30px) scale(1.05); }
        66% { transform: translate(-20px, 20px) scale(0.95); }
    }
    @keyframes float-slow-delayed {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(-25px, 25px) scale(1.03); }
        66% { transform: translate(25px, -15px) scale(0.97); }
    }
    @keyframes pulse-glow {
        0%, 100% { opacity: 0.3; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.1); }
    }
    .animate-fade-in {
        animation: fade-in 0.6s ease-out forwards;
    }
    .animate-fade-in-up {
        animation: fade-in-up 0.6s ease-out forwards;
        opacity: 0;
    }
    .animate-float-slow {
        animation: float-slow 20s ease-in-out infinite;
    }
    .animate-float-slow-delayed {
        animation: float-slow-delayed 25s ease-in-out infinite;
    }
    .animate-pulse-glow {
        animation: pulse-glow 8s ease-in-out infinite;
    }
</style>
@stop

