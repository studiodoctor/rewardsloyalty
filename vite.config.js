/**
 * ═══════════════════════════════════════════════════════════════════════════
 * VITE BUILD CONFIGURATION - Optimized for Multi-Role Laravel App
 * ═══════════════════════════════════════════════════════════════════════════
 *
 * This configuration implements code-splitting by user role to minimize
 * JavaScript payload for each user type:
 *
 * BUNDLE ARCHITECTURE:
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │ core.js (~70KB)     - Alpine, HTMX, Lucide, forms, etc. (ALL PAGES)    │
 * │ member.js (~15KB)   - PWA, session, premium cards, confetti            │
 * │ staff.js (~70KB)    - QR scanner (ZXing), confetti, datepicker         │
 * │ partner.js (~180KB) - TipTap, Pickr, ApexCharts, AI, premium cards     │
 * │ admin.js (~60KB)    - ApexCharts, datepicker                           │
 * └─────────────────────────────────────────────────────────────────────────┘
 *
 * LAYOUT USAGE:
 * - Member layout:  @vite(['resources/css/app.css', 'resources/js/core.js', 'resources/js/member.js'])
 * - Staff layout:   @vite(['resources/css/app.css', 'resources/js/core.js', 'resources/js/staff.js'])
 * - Partner layout: @vite(['resources/css/app.css', 'resources/js/partner.js', 'resources/js/core.js'])
 * - Admin layout:   @vite(['resources/css/app.css', 'resources/js/admin.js', 'resources/js/core.js'])
 *
 * IMPORTANT: Partner/Admin load their bundle BEFORE core.js because TipTap
 * registers Alpine.data() which must happen before Alpine.start() in core.js.
 *
 * @copyright 2025 NowSquare. All rights reserved.
 */

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // ═══════════════════════════════════════════════════════════
                // CSS - Shared across all pages
                // ═══════════════════════════════════════════════════════════
                'resources/css/app.css',
                
                // ═══════════════════════════════════════════════════════════
                // JS Entry Points - Split by user role for optimal loading
                // ═══════════════════════════════════════════════════════════
                
                // Core: Universal foundation (~70KB gzipped)
                // Alpine, HTMX, Lucide, forms, image upload, QR display, PIN input
                'resources/js/core.js',
                
                // Member: Customer-facing PWA experience (~15KB gzipped)
                // Session tracking, PWA caching, premium cards, confetti
                'resources/js/member.js',
                
                // Staff: Point-of-sale operations (~70KB gzipped)
                // QR scanner (ZXing), confetti, datepicker
                'resources/js/staff.js',
                
                // Partner: Business owner dashboard (~180KB gzipped)
                // TipTap, Pickr, ApexCharts, premium cards, AI
                'resources/js/partner.js',
                
                // Admin: Platform administration (~60KB gzipped)
                // ApexCharts, datepicker (lean admin experience)
                'resources/js/admin.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        // Target modern browsers that support ES2020 features
        target: 'es2020',
        
        // Disable source maps in production for smaller files
        sourcemap: false,
        
        // Optimize chunk splitting for better caching
        rollupOptions: {
            output: {
                // Manual chunk splitting for vendor libraries
                // These get cached separately from app code
                manualChunks: {
                    // Alpine.js ecosystem - cached together
                    'alpine': ['alpinejs', '@alpinejs/collapse'],
                    
                    // ApexCharts - large library, cache separately
                    'apexcharts': ['apexcharts'],
                    
                    // ZXing QR Scanner - very large, cache separately
                    'zxing': ['@zxing/browser', '@zxing/library'],
                    
                    // TipTap/ProseMirror - large editor, cache separately
                    // Note: @tiptap/pm has non-standard exports, so we exclude it from manual chunks
                    'tiptap': [
                        '@tiptap/core',
                        '@tiptap/starter-kit',
                        '@tiptap/extension-link',
                        '@tiptap/extension-underline',
                        '@tiptap/extension-text-align',
                    ],
                },
            },
        },
        
        // Increase warning limit since we've analyzed bundle sizes
        chunkSizeWarningLimit: 700,
    },
});
