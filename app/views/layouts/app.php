<?php
/** @var string $title */
/** @var string $page */
/** @var string $viewPath */
/** @var string $baseUrl */

$user = $_SESSION['user'] ?? null;
$B = $baseUrl; // shorthand for links
$themeVerseRef = trim((string)($themeVerse['reference'] ?? '1 Wakorintho 14:40'));
$themeVerseText = trim((string)($themeVerse['verse'] ?? 'Mambo yote na yatendeke kwa uzuri na kwa utaratibu.'));

$menu = [
    'dashboard'     => ['label' => 'Dashboard',     'perm' => null,                 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'href' => \App\Core\Route::get('dashboard')],
    'members'       => ['label' => 'Members',       'perm' => 'members.view',        'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'href' => \App\Core\Route::get('members')],
    'guests'        => ['label' => 'Guests',        'perm' => 'members.view',        'icon' => 'M18 18a6 6 0 00-12 0M12 12a4 4 0 100-8 4 4 0 000 8m7 2a4 4 0 00-2-3.465', 'href' => \App\Core\Route::get('guests')],
    'attendance'    => ['label' => 'Attendance',    'perm' => 'attendance.view',     'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'href' => \App\Core\Route::get('attendance')],
    'finance'       => ['label' => 'Church Giving', 'perm' => 'finance.view',        'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'href' => \App\Core\Route::get('finance')],
    'assets'        => ['label' => 'Assets',        'perm' => 'assets.view',         'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2', 'href' => \App\Core\Route::get('assets')],
    'communication' => ['label' => 'Communication','perm' => 'communication.view', 'icon' => 'M21 11.5a8.38 8.38 0 01-9 8.5 8.5 8.5 0 01-4.1-1.05L3 21l1.9-4.2A8.38 8.38 0 013 11.5 8.5 8.5 0 0112 3a8.5 8.5 0 019 8.5z', 'href' => \App\Core\Route::get('communication')],
    'ai'            => ['label' => 'AI Assistant',  'perm' => 'reports.view',        'icon' => 'M12 3v3m6.364-.364l-2.121 2.121M21 12h-3m.364 6.364l-2.121-2.121M12 21v-3m-6.364.364l2.121-2.121M3 12h3m-.364-6.364l2.121 2.121M12 8a4 4 0 100 8 4 4 0 000-8z', 'href' => \App\Core\Route::get('ai')],
    'reports'       => ['label' => 'Reports',       'perm' => 'reports.view',        'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586', 'href' => \App\Core\Route::get('reports')],
    'settings'      => ['label' => 'Settings',      'perm' => 'settings.manage',     'icon' => 'M10.325 4.317a1 1 0 011.35-.936 1 1 0 011.35.936l.096.288a1 1 0 00.95.69h.303a1 1 0 01.987 1.157l-.056.337a1 1 0 00.287.885l.214.214a1 1 0 010 1.414l-.214.214a1 1 0 00-.287.885l.056.337a1 1 0 01-.987 1.157h-.303a1 1 0 00-.95.69l-.096.288a1 1 0 01-1.35.936 1 1 0 01-1.35-.936l-.096-.288a1 1 0 00-.95-.69h-.303a1 1 0 01-.987-1.157l.056-.337a1 1 0 00-.287-.885l-.214-.214a1 1 0 010-1.414l.214-.214a1 1 0 00.287-.885l-.056-.353z', 'href' => \App\Core\Route::get('settings')],
];
// Filter menu items by current user's permissions
$menu = array_filter($menu, fn($item) =>
    $item['perm'] === null || \App\Core\Auth::can($item['perm'])
);
?>
<!doctype html>
<html lang="en" class="h-full bg-mist-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($churchName ?? 'Church CMS') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Precompiled, purged Tailwind build (replaces the CDN JIT compiler for speed).
         assets/ lives at the project root, one level above public/, so this must use
         Url::to() (root-relative) rather than $B (which points at .../public). -->
    <link rel="stylesheet" href="<?= \App\Core\Url::to('assets/css/tailwind.css') ?>">
    <link rel="stylesheet" href="<?= \App\Core\Url::to('assets/css/app.css') ?>">
    <meta name="csrf-token" content="<?= htmlspecialchars(\App\Core\Auth::getCsrfToken()) ?>">
    <script>
        const BASE_URL = '<?= $B ?>';
        // Extract CSRF token from meta tag
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        // Global fetch interceptor to add CSRF token to all non-GET requests
        (function() {
            const originalFetch = window.fetch;
            window.fetch = function(url, options = {}) {
                // Only add CSRF token for state-changing requests
                if (!options.method || !['GET', 'HEAD'].includes(options.method.toUpperCase())) {
                    // Ensure headers object exists
                    if (!options.headers) {
                        options.headers = {};
                    }
                    // Only add CSRF token if not already present
                    if (!(options.headers instanceof Headers)) {
                        if (!options.headers['X-CSRF-TOKEN']) {
                            options.headers['X-CSRF-TOKEN'] = CSRF_TOKEN;
                        }
                    } else if (!options.headers.has('X-CSRF-TOKEN')) {
                        options.headers.set('X-CSRF-TOKEN', CSRF_TOKEN);
                    }
                }
                return originalFetch.call(this, url, options);
            };
        })();
    </script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; padding: 0; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Inter', system-ui, sans-serif; letter-spacing: -0.01em; }
        button svg { color: inherit; }
    </style>
</head>

<?php if ($page === 'login' || $page === 'forgot_password' || $page === 'reset_password'): ?>
<?php require __DIR__ . '/../' . $viewPath; ?>
<?php return; endif; ?>

<body class="h-full font-body text-mist-900 bg-mist-50 antialiased">
<div class="min-h-full flex">

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-mist-900 transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out flex flex-col shadow-xl shadow-mist-900/20">
        <div class="flex items-center gap-3 px-5 py-6 border-b border-white/10">
            <!-- Logo image (always present for JS updates) -->
            <img mh-logo src="<?= !empty($churchLogo) ? htmlspecialchars($baseUrl . $churchLogo) : '' ?>" alt="<?= htmlspecialchars($churchName ?? '') ?>" class="flex-shrink-0 w-16 h-16 rounded-lg object-cover shadow-md <?= empty($churchLogo) ? 'hidden' : '' ?>" style="<?= empty($churchLogo) ? 'display:none' : '' ?>">
            
            <!-- Fallback icon (show when no logo) -->
            <div mh-logo-fallback class="flex-shrink-0 w-16 h-16 rounded-lg bg-glory-400 text-mist-950 flex items-center justify-center shadow-md <?= !empty($churchLogo) ? 'hidden' : '' ?>" style="<?= !empty($churchLogo) ? 'display:none' : '' ?>">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 7h8M9 13h6"/>
                </svg>
            </div>
            
            <div>
                <h1 class="text-white font-heading font-bold text-base leading-tight truncate max-w-[150px]"><?= htmlspecialchars($churchName ?? 'Church CMS') ?></h1>
                <p class="text-white/55 text-[11px] tracking-wide">Church operations</p>
            </div>
        </div>

        <div class="px-4 py-4">
            <div class="rounded-lg bg-white/[0.06] border border-white/10 px-4 py-3 text-white/85 text-xs">
                <p class="text-[10px] uppercase tracking-widest text-glory-200 font-bold">Theme Verse</p>
                <p class="mt-1 leading-5">"<?= htmlspecialchars($themeVerseText, ENT_QUOTES, 'UTF-8') ?>"</p>
                <?php if ($themeVerseRef !== ''): ?>
                    <p class="mt-1 text-[11px] text-glory-200 font-semibold"><?= htmlspecialchars($themeVerseRef, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <nav class="flex-1 px-3 pb-4 space-y-1 overflow-y-auto">
            <?php foreach ($menu as $key => $item): ?>
                <a href="<?= $item['href'] ?>"
                   class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-150
                          <?= $page === $key
                              ? 'bg-royal-700 text-white shadow-sm'
                              : 'text-white/70 hover:bg-white/[0.07] hover:text-white' ?>">
                    <svg class="w-5 h-5 flex-shrink-0 <?= $page === $key ? 'text-royal-100' : 'text-white/40 group-hover:text-royal-200' ?>"
                         fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="<?= $item['icon'] ?>"/>
                    </svg>
                    <?= $item['label'] ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php if ($user): ?>
        <div class="px-4 py-4 border-t border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-royal-500 text-white flex items-center justify-center font-bold text-sm">
                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate"><?= htmlspecialchars($user['full_name']) ?></p>
                    <p class="text-white/60 text-xs truncate"><?= htmlspecialchars($user['role']) ?></p>
                </div>
            </div>
            <form action="<?= \App\Core\Route::get('logout') ?>" method="post" class="mt-3">
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm text-white/75 hover:bg-white/[0.07] hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign out
                </button>
            </form>
        </div>
        <?php endif; ?>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 bg-gray-900/50 z-20 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">

        <header class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b border-mist-200 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-[72px] gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-mist-600 hover:bg-mist-100 hover:text-mist-800 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="min-w-0 hidden sm:block">
                    <h2 class="text-xl font-heading font-bold text-mist-900 truncate"><?= htmlspecialchars($title) ?></h2>
                    <p class="text-xs text-mist-500 mt-0.5">Manage daily church operations with clarity.</p>
                </div>

                <div class="flex items-center gap-3 ml-auto">
                    <div class="hidden xl:flex items-center w-80 h-10 px-3 rounded-lg bg-mist-50 border border-mist-200 text-mist-400 text-sm">
                        <svg class="w-4 h-4 mr-2 text-mist-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"/>
                        </svg>
                        Search members, events, vouchers...
                    </div>
                    <div class="hidden md:flex items-center gap-2 px-3 py-2 rounded-lg bg-mist-50 border border-mist-200">
                        <span class="w-2 h-2 rounded-full bg-royal-500"></span>
                        <span class="text-sm text-mist-700 font-semibold"><?= date('D, d M Y') ?></span>
                    </div>
                    <?php if ($user): ?>
                    <div class="w-9 h-9 rounded-full bg-royal-100 text-royal-800 flex items-center justify-center font-bold text-sm ring-1 ring-royal-200">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 lg:p-7 relative" id="main-content">
            <div class="relative z-10 h-full">
            <?php require __DIR__ . '/../' . $viewPath; ?>
            </div>
        </main>

        <footer id="app-footer" class="border-t border-mist-200 px-6 py-3 text-center text-xs text-mist-500 bg-white/70">
            &copy; <?= date('Y') ?> <?= htmlspecialchars($churchName ?? 'Church CMS') ?> - Church Management Platform
        </footer>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}
</script>
</body>
</html>
