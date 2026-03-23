@extends('staff.layouts.default')

@section('page_title', trans('common.points_transaction_history') . config('default.page_title_delimiter') . $card->head . config('default.page_title_delimiter') . $member->name . config('default.page_title_delimiter') . config('default.app_name'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-stone-50 via-white to-stone-100 dark:from-secondary-950 dark:via-secondary-900 dark:to-secondary-950">
    {{-- Ambient background effects --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-primary-500/20 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute bottom-1/3 left-1/4 w-80 h-80 bg-blue-500/15 rounded-full blur-3xl animate-float-slow-delayed"></div>
        <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-primary-400/10 rounded-full blur-2xl animate-pulse-glow"></div>
    </div>

    <div class="relative z-10 w-full max-w-lg mx-auto px-4 py-8 md:py-12">
        {{-- Header --}}
        <div class="animate-fade-in" style="animation-delay: 50ms;">
            <x-ui.page-header
                icon="coins"
                iconBg="primary"
                :title="trans('common.points_transaction_history')"
                compact
            />
        </div>

        {{-- Messages --}}
        <div class="mb-6 animate-fade-in-up" style="animation-delay: 75ms;">
            <x-forms.messages />
        </div>

        {{-- Card Display --}}
        @if($card)
            <div class="mb-6 animate-fade-in-up" style="animation-delay: 100ms;">
                <x-member.card
                    :card="$card"
                    :member="$member"
                    :flippable="false"
                    :links="false"
                    :show-qr="false"
                />
            </div>
        @endif

        {{-- Add Transaction Button --}}
        @if($card && $member)
            <div class="mb-6 animate-fade-in-up" style="animation-delay: 125ms;">
                <a href="{{ route('staff.earn.points', ['member_identifier' => $member->unique_identifier, 'card_identifier' => $card->unique_identifier]) }}" 
                   class="relative group/btn block w-full">
                    {{-- Button glow --}}
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-primary-500 via-primary-600 to-primary-500 rounded-2xl opacity-0 group-hover/btn:opacity-75 blur-md transition-all duration-300"></div>
                    
                    {{-- Button content --}}
                    <div class="relative flex items-center justify-center gap-3 px-8 py-4 bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white font-semibold rounded-2xl shadow-lg shadow-primary-900/20 transition-all duration-300 group-hover/btn:shadow-xl group-hover/btn:shadow-primary-500/30">
                        <x-ui.icon icon="coins" class="w-5 h-5 transition-transform group-hover/btn:scale-110" />
                        <span>{{ trans('common.add_transaction') }}</span>
                        <x-ui.icon icon="arrow-right" class="w-5 h-5 transition-transform group-hover/btn:translate-x-1" />
                    </div>
                </a>
            </div>
        @endif

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
        @if($member)
            <div class="space-y-6">
                <div class="animate-fade-in-up" style="animation-delay: 175ms;">
                    <x-member.member-card :member="$member" :club="$card?->club" :show-tier="true" />
                </div>
                
                <div class="animate-fade-in-up" style="animation-delay: 200ms;">
                    <div class="bg-white dark:bg-secondary-900 rounded-2xl border border-stone-200 dark:border-secondary-800 shadow-lg">
                        {{-- History Header --}}
                        <div class="px-6 py-4 border-b border-stone-200 dark:border-secondary-800 bg-stone-50/50 dark:bg-secondary-800/50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-100 to-blue-200 dark:from-primary-900/50 dark:to-blue-800/50 flex items-center justify-center">
                                    <x-ui.icon icon="clock" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-secondary-900 dark:text-white">{{ trans('common.transaction_history') }}</h3>
                                    <p class="text-xs text-secondary-500 dark:text-secondary-400">Points earned and rewards claimed</p>
                                </div>
                            </div>
                        </div>
                        
                        {{-- History Content --}}
                        <div class="p-6">
                            <x-member.history :card="$card" :show-notes="true" :show-attachments="true" :member="$member" />
                        </div>
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
