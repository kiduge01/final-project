<?php $B = $baseUrl ?? ''; ?>
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="text-center">
        <p class="text-7xl font-bold text-primary-600 mb-2">404</p>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Page Not Found</h1>
        <p class="text-gray-500 mb-6">The page you requested does not exist or has been moved.</p>
        <a href="<?= $B ?>/" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-xl shadow-sm transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Back to Dashboard
        </a>
    </div>
</div>
