@extends('member.layouts.default')

@section('page_title', trans('common.send_points') . config('default.page_title_delimiter') . config('default.app_name'))

@section('content')
<div class="min-h-screen relative">
    {{-- Ambient Background --}}
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 -left-40 w-96 h-96 bg-primary-500/10 dark:bg-primary-500/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="flex flex-col w-full px-4 md:px-8 py-8 md:py-12">
        <div class="space-y-6 w-full place-items-center animate-fade-in-up">
            <div class="max-w-md mx-auto w-full">

                {{-- Page Header - Always at Top --}}
                @php
                    if ($memberOwnsRequestLink) {
                        $breadcrumbs = [
                            ['url' => route('member.cards'), 'icon' => 'home', 'title' => trans('common.home')],
                            ['url' => route('member.data.list', ['name' => 'request-links']), 'text' => trans('common.request_links')],
                            ['text' => trans('common.send_points')]
                        ];
                        $pageIcon = 'share-2';
                        $pageTitle = trans('common.share_link');
                    } else {
                        if ($pointRequest->card) {
                            $breadcrumbs = [
                                ['url' => route('member.cards'), 'icon' => 'home', 'title' => trans('common.home')],
                                ['url' => route('member.cards'), 'text' => trans('common.wallet')],
                                ['url' => route('member.card', ['card_id' => $pointRequest->card->id]), 'text' => $pointRequest->card->head],
                                ['text' => trans('common.send_points')]
                            ];
                        } else {
                            $breadcrumbs = [
                                ['url' => route('member.cards'), 'icon' => 'home', 'title' => trans('common.home')],
                                ['url' => route('member.cards'), 'text' => trans('common.wallet')],
                                ['text' => trans('common.send_points')]
                            ];
                        }
                        $pageIcon = 'send';
                        $pageTitle = trans('common.send_points');
                    }
                @endphp
                
                <div class="mb-8 animate-fade-in-up delay-50">
                    <x-ui.page-header
                        :icon="$pageIcon"
                        :title="$pageTitle"
                        :breadcrumbs="$breadcrumbs"
                        compact
                    />
                </div>

                <!-- If the request is tied to a specific card, display it -->
                @if($pointRequest->card)
                    <div class="mb-8 max-w-sm mx-auto animate-pop delay-100">
                        <div class="premium-card-wrapper transform hover:scale-105 transition-transform duration-500">
                            <x-member.premium-card :card="$pointRequest->card" :member="null" :flippable="false" :links="false"
                                :show-qr="false" />
                        </div>
                    </div>
                @endif

                @if($memberOwnsRequestLink)

                <div class="hide-on-scan z-40 mb-6 flex items-center w-full p-5 text-secondary-600 bg-white dark:bg-secondary-800 border border-secondary-200 dark:border-secondary-700 rounded-2xl shadow-sm dark:text-secondary-300 animate-fade-in-up delay-200"
                    role="alert">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-10 h-10 text-emerald-600 bg-emerald-100 rounded-xl dark:bg-emerald-900/30 dark:text-emerald-400">
                        <x-ui.icon icon="handshake" class="w-5 h-5" />
                    </div>
                    <div class="ml-4 text-sm font-medium">{{ trans('common.member_owns_link_info') }}</div>
                </div>

                <div class="bg-white dark:bg-secondary-900 rounded-2xl border border-secondary-200 dark:border-secondary-700 p-6 shadow-lg animate-fade-in-up delay-300">
                    <div class="grid grid-cols-8 gap-3 w-full">
                        <label for="request_link" class="sr-only">{{ trans('common.link') }}</label>
                        <input id="request_link" type="text"
                            class="col-span-6 bg-secondary-50 dark:bg-secondary-800 border border-secondary-200 dark:border-secondary-700 text-secondary-600 dark:text-secondary-400 text-sm rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 block w-full p-3 transition-all"
                            value="{{ $requestLink }}" disabled readonly>
                        <button type="button" data-copy-target="request_link"
                            class="col-span-2 text-white bg-gradient-to-br from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-xl text-sm w-full py-3 text-center items-center inline-flex justify-center shadow-lg shadow-emerald-500/25 hover:-translate-y-0.5 transition-all duration-200">
                            <span id="default-message">{{ trans('common.copy') }}</span>
                            <span id="success-message" class="hidden">
                                <div class="inline-flex items-center">
                                    <x-ui.icon icon="check" class="w-4 h-4 mr-1.5" />
                                    {{ trans('common.copied') }}
                                </div>
                            </span>
                        </button>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const targetInput = document.getElementById('request_link');
                        const copyButton = document.querySelector('[data-copy-target="request_link"]');
                        const defaultMessage = document.getElementById('default-message');
                        const successMessage = document.getElementById('success-message');

                        copyButton.addEventListener('click', function () {
                            try {
                                // Remove disabled attribute temporarily if present
                                const isDisabled = targetInput.disabled;
                                if (isDisabled) {
                                    targetInput.disabled = false;
                                }

                                // Select the text
                                targetInput.select();
                                targetInput.setSelectionRange(0, 99999); // For mobile devices

                                // Copy the text
                                const successful = document.execCommand('copy');

                                // Clear selection using multiple methods for cross-browser compatibility
                                window.getSelection().removeAllRanges();
                                targetInput.setSelectionRange(0, 0);
                                targetInput.blur();

                                // Restore disabled state if it was disabled
                                if (isDisabled) {
                                    targetInput.disabled = true;
                                }

                                if (successful) {
                                    // Show success message
                                    defaultMessage.classList.add('hidden');
                                    successMessage.classList.remove('hidden');

                                    // Reset to default state after 2 seconds
                                    setTimeout(() => {
                                        defaultMessage.classList.remove('hidden');
                                        successMessage.classList.add('hidden');
                                    }, 2000);
                                }
                            } catch (err) {
                                console.error('Failed to copy text: ', err);
                            }
                        });
                    });
                </script>
            @else
                <div class="hide-on-scan z-40 mb-6 flex items-center w-full p-5 text-secondary-600 bg-white dark:bg-secondary-800 border border-secondary-200 dark:border-secondary-700 rounded-2xl shadow-sm dark:text-secondary-300 animate-fade-in-up delay-200"
                    role="alert">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-10 h-10 text-emerald-600 bg-emerald-100 rounded-xl dark:bg-emerald-900/30 dark:text-emerald-400">
                        <x-ui.icon icon="handshake" class="w-5 h-5" />
                    </div>
                    <div class="ml-4 text-sm font-medium">
                        {{ trans('common.send_points_request_info', ['memberName' => $pointRequest->member->name]) }}
                    </div>
                </div>

                <x-forms.messages class="mt-6 animate-fade-in-up delay-300" />

                @if(!$memberHasPoints)
                    <div class="flex p-4 mb-6 text-amber-800 rounded-xl bg-amber-50 border border-amber-100 dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-300 animate-fade-in-up delay-300"
                        role="alert">
                        <x-ui.icon icon="exclamation-triangle" class="flex-shrink-0 w-5 h-5" />
                        <div class="ml-3 text-sm font-medium">
                            {{ trans('common.member_has_no_points_to_send_msg') }}
                        </div>
                    </div>
                @else

                    <script>
                        const cardBalances = @json($cardBalances);

                        window.addEventListener('load', function () {
                            const pointsInput = document.getElementById('points');
                            const cardSelect = document.querySelector('select[name="card_id"]');
                            const hiddenCardInput = document.querySelector('input[type="hidden"][name="card_id"]');

                            function updatePointsInput(cardId) {
                                const maxPoints = cardBalances[cardId];
                                if (maxPoints !== undefined) {
                                    pointsInput.setAttribute('max', maxPoints);
                                    pointsInput.placeholder = "{{ trans('common.send_points_placeholder') }} (1 - " + window.appFormatNumber(maxPoints) + ")";
                                    // If current value is higher than new max, adjust it
                                    if (parseInt(pointsInput.value) > maxPoints) {
                                        pointsInput.value = maxPoints;
                                    }
                                }
                            }

                            if (cardSelect) {
                                // Multiple cards case
                                cardSelect.addEventListener('change', function () {
                                    updatePointsInput(this.value);
                                });
                                cardSelect.dispatchEvent(new Event('change'));
                            } else if (hiddenCardInput) {
                                // Single card case
                                updatePointsInput(hiddenCardInput.value);
                            }
                        });
                    </script>

                    <div class="animate-fade-in-up delay-300">
                        <div class="bg-white dark:bg-secondary-900 rounded-2xl border border-secondary-200 dark:border-secondary-700 p-6 shadow-lg">
                            <x-forms.form-open
                                action="{{ route('member.request.points.send.post', ['request_identifier' => $pointRequest->unique_identifier]) }}"
                                method="POST" />
                            @csrf

                            <div class="grid gap-5 mb-6">
                                @if(count($memberCardsOptions) > 1)
                                    <x-forms.select name="card_id" :label="trans('common.select_card')" :options="$memberCardsOptions"
                                        required="true" />
                                @else
                                    {{-- For single card, use a hidden input instead --}}
                                    <input type="hidden" name="card_id" value="{{ key($memberCardsOptions) }}">
                                @endif

                                <x-forms.input name="points" :label="trans('common.points')" type="number" inputmode="numeric"
                                    icon="coins" min="1" step="1" placeholder="{{ trans('common.send_points_placeholder') }}"
                                    required />

                                <div class="flex items-start p-4 bg-secondary-50 dark:bg-secondary-800/50 rounded-xl border border-secondary-100 dark:border-secondary-700/50">
                                    <x-forms.checkbox name="confirm" :checked="false"
                                        :label="trans('common.confirm_send_points')" />
                                </div>
                            </div>

                            <button type="submit" disabled id="submitButton"
                                class="w-full py-4 px-6 text-white bg-gradient-to-br from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 focus:ring-4 focus:ring-emerald-300 font-bold rounded-xl text-lg shadow-lg shadow-emerald-500/25 hover:shadow-xl hover:shadow-emerald-500/30 hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none">
                                {{ trans('common.send_points') }}
                            </button>

                            <script>
                                window.addEventListener('load', function () {
                                    const submitButton = document.getElementById('submitButton');
                                    const pointsInput = document.getElementById('points');
                                    // Find the visible checkbox - the component renders a hidden + visible pair
                                    const confirmCheckbox = document.querySelector('input[type="checkbox"][id^="confirm"]') 
                                        || document.querySelector('input[name="confirm"][type="checkbox"]');

                                    function updateSubmitState() {
                                        if (!submitButton || !pointsInput || !confirmCheckbox) return;
                                        
                                        const pointsValue = parseInt(pointsInput.value, 10);
                                        const maxPoints = parseInt(pointsInput.getAttribute('max'), 10) || Infinity;
                                        const isConfirmed = confirmCheckbox.checked;
                                        const hasValidPoints = !isNaN(pointsValue) && pointsValue >= 1 && pointsValue <= maxPoints;
                                        
                                        submitButton.disabled = !(isConfirmed && hasValidPoints);
                                    }

                                    if (confirmCheckbox) {
                                        confirmCheckbox.addEventListener('change', updateSubmitState);
                                    }
                                    if (pointsInput) {
                                        pointsInput.addEventListener('input', updateSubmitState);
                                    }
                                    
                                    // Initial state check
                                    updateSubmitState();
                                });
                            </script>
                            <x-forms.form-close />
                        </div>
                    </div>
                @endif
            @endif
            </div>
        </div>
    </div>
</div>
@stop