@extends('staff.layouts.default')

@section('page_title', trans('common.scan_qr') . config('default.page_title_delimiter') . trans('common.dashboard') . config('default.page_title_delimiter') . config('default.app_name'))

@section('content')
{{-- 
    ╔══════════════════════════════════════════════════════════════════════════════╗
    ║  QR SCANNER PAGE - Premium Staff Interface                                     ║
    ╠══════════════════════════════════════════════════════════════════════════════╣
    ║  Design Philosophy:                                                            ║
    ║  - Clean, focused interface for QR scanning operations                         ║
    ║  - Visual feedback states for scanning process                                 ║
    ║  - Minimal distractions to maximize scanning efficiency                        ║
    ║  - Dark theme optimized for camera viewfinder contrast                         ║
    ╚══════════════════════════════════════════════════════════════════════════════╝
--}}
<div class="min-h-[80vh] flex flex-col items-center justify-center px-4 py-6 md:px-8 md:py-8">
    <div class="w-full max-w-md mx-auto">
        {{-- Header Section --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-600 to-primary-400 shadow-lg shadow-accent-500/25 mb-4 animate-fade-in-up">
                <x-ui.icon icon="qr-code" class="w-8 h-8 text-white" />
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-secondary-900 dark:text-white mb-2 tracking-tight animate-fade-in-up" style="animation-delay: 100ms">
                {{ trans('common.scan_qr') }}
            </h1>
            <p class="text-secondary-500 dark:text-secondary-400 animate-fade-in-up" style="animation-delay: 200ms">
                {{ trans('common.qr_scanner_info') }}
            </p>
        </div>

        {{-- Scanner Container --}}
        <div class="relative mb-6 animate-fade-in-up" style="animation-delay: 300ms">
            {{-- Video Container with Scanner Frame --}}
            <div class="relative aspect-square w-full max-w-sm mx-auto rounded-3xl overflow-hidden bg-secondary-900 shadow-2xl shadow-secondary-900/50 dark:shadow-black/50">
                {{-- Corner Decorations (Scanner Frame Effect) --}}
                <div class="absolute inset-0 z-10 pointer-events-none">
                    {{-- Top Left Corner --}}
                    <div class="absolute top-4 left-4 w-12 h-12 border-l-4 border-t-4 border-primary-500 rounded-tl-lg"></div>
                    {{-- Top Right Corner --}}
                    <div class="absolute top-4 right-4 w-12 h-12 border-r-4 border-t-4 border-primary-500 rounded-tr-lg"></div>
                    {{-- Bottom Left Corner --}}
                    <div class="absolute bottom-4 left-4 w-12 h-12 border-l-4 border-b-4 border-primary-500 rounded-bl-lg"></div>
                    {{-- Bottom Right Corner --}}
                    <div class="absolute bottom-4 right-4 w-12 h-12 border-r-4 border-b-4 border-primary-500 rounded-br-lg"></div>
                    
                    {{-- Scanning Line Animation --}}
                    <div class="scanning-line absolute left-4 right-4 h-0.5 bg-gradient-to-r from-transparent via-primary-500 to-transparent"></div>
                </div>
                
                {{-- Video Element --}}
                <video id="video" class="w-full h-full object-cover"></video>
                
                {{-- Placeholder when camera not active --}}
                <div id="camera-placeholder" class="absolute inset-0 flex flex-col items-center justify-center bg-secondary-800 text-secondary-400">
                    <x-ui.icon icon="camera" class="w-16 h-16 mb-4 opacity-50" />
                    <p class="text-sm">{{ trans('common.camera_will_appear_here') ?? 'Camera will appear here' }}</p>
                </div>
            </div>
        </div>

        {{-- Scan Button --}}
        <div class="mb-6 animate-fade-in-up" style="animation-delay: 400ms">
            <button type="button" class="scan-qr disable-on-scan w-full btn-primary btn-lg h-14 flex items-center justify-center gap-3 group">
                <x-ui.icon icon="qr-code" class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" />
                <span>{{ trans('common.scan_qr') }}</span>
            </button>
        </div>

        {{-- Status Messages --}}
        <div class="space-y-3 animate-fade-in-up" style="animation-delay: 500ms">
            {{-- Info Message --}}
            <div id="scanner-info" class="hide-on-scan flex items-center gap-4 p-4 bg-white dark:bg-secondary-800/50 rounded-2xl border border-secondary-200 dark:border-secondary-700/50 shadow-sm">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-accent-100 dark:bg-accent-900/30 flex items-center justify-center">
                    <x-ui.icon icon="info" class="w-5 h-5 text-accent-600 dark:text-accent-400"/>
                </div>
                <p class="text-sm text-secondary-600 dark:text-secondary-300">
                    {{ trans('common.qr_scanner_info') }}
                </p>
            </div>

            {{-- Success Message --}}
            <div id="code-found" class="hidden flex items-center gap-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-2xl border border-green-200 dark:border-green-700/50 shadow-sm">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <x-ui.icon icon="check" class="w-5 h-5 text-green-600 dark:text-green-400"/>
                </div>
                <div>
                    <p class="font-medium text-green-800 dark:text-green-300">
                        {{ trans('common.code_found') }}
                    </p>
                    <p class="text-sm text-green-600 dark:text-green-400">
                        {{ trans('common.redirecting') ?? 'Redirecting...' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Scanning line animation */
    .scanning-line {
        animation: scan 2s ease-in-out infinite;
    }
    
    @keyframes scan {
        0%, 100% {
            top: 1rem;
            opacity: 0;
        }
        10% {
            opacity: 1;
        }
        90% {
            opacity: 1;
        }
        100% {
            top: calc(100% - 1rem);
            opacity: 0;
        }
    }
    
    /* Hide placeholder when video is playing */
    #video:not([src=""]) ~ #camera-placeholder,
    #video.active ~ #camera-placeholder {
        display: none;
    }
</style>

<script>
window.onload = function() {
    const codeFound = document.getElementById('code-found');
    const scannerInfo = document.getElementById('scanner-info');
    const cameraPlaceholder = document.getElementById('camera-placeholder');
    const video = document.getElementById('video');

    // Listen to the pageshow event
    window.addEventListener('pageshow', function(event) {
        // If the page is loaded from the cache (like when using the back button)
        if (event.persisted) {
            // Hide the codeFound element and show scanner info
            codeFound.classList.add('hidden');
            scannerInfo.classList.remove('hidden');
        }
    });
    
    // Handle video stream state for placeholder visibility
    video.addEventListener('loadedmetadata', function() {
        if (cameraPlaceholder) {
            cameraPlaceholder.style.display = 'none';
        }
        video.classList.add('active');
    });
};
</script>
@stop
