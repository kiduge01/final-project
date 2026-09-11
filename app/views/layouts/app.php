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
    'events'        => ['label' => 'Events / Ibada', 'perm' => 'events.view',        'icon' => 'M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z', 'href' => \App\Core\Route::get('events')],
    'attendance'    => ['label' => 'Attendance',    'perm' => 'attendance.view',     'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'href' => \App\Core\Route::get('attendance')],
    'finance'       => ['label' => 'Church Giving', 'perm' => 'finance.view',        'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'href' => \App\Core\Route::get('finance')],
    'assets'        => ['label' => 'Assets',        'perm' => 'assets.view',         'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2', 'href' => \App\Core\Route::get('asset-center')],
    'communication' => ['label' => 'Communication','perm' => 'communication.view', 'icon' => 'M21 11.5a8.38 8.38 0 01-9 8.5 8.5 8.5 0 01-4.1-1.05L3 21l1.9-4.2A8.38 8.38 0 013 11.5 8.5 8.5 0 0112 3a8.5 8.5 0 019 8.5z', 'href' => \App\Core\Route::get('communication')],
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
    <link rel="stylesheet" href="<?= \App\Core\Url::to('assets/css/tailwind.css') ?>?v=<?= @filemtime(__DIR__ . '/../../../assets/css/tailwind.css') ?: time() ?>">
    <link rel="stylesheet" href="<?= \App\Core\Url::to('assets/css/app.css') ?>?v=<?= @filemtime(__DIR__ . '/../../../assets/css/app.css') ?: time() ?>">
    <link rel="stylesheet" href="<?= \App\Core\Url::to('assets/css/professional-ui.css') ?>?v=<?= @filemtime(__DIR__ . '/../../../assets/css/professional-ui.css') ?: time() ?>">
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
                        Search members, guests, attendance, giving, assets...
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

        <?php if ($user): ?>
        <div id="ai-chat-widget" class="fixed right-5 bottom-5 sm:right-7 sm:bottom-7 z-40">
            <section id="ai-chat-panel" class="hidden mb-3 w-[min(380px,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-mist-200 bg-white shadow-2xl shadow-mist-900/20">
                <div class="flex items-center justify-between bg-royal-800 px-4 py-3 text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white/15">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3m6.364-.364-2.121 2.121M21 12h-3m.364 6.364-2.121-2.121M12 21v-3m-6.364.364 2.121-2.121M3 12h3m-.364-6.364 2.121 2.121M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/></svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold">Church Assistant</h2>
                            <p class="text-[11px] text-white/70">Ask questions or perform approved tasks</p>
                        </div>
                    </div>
                    <button type="button" id="ai-chat-close" class="rounded-lg p-1.5 text-white/75 hover:bg-white/10 hover:text-white" aria-label="Close assistant">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>
                <div id="ai-chat-messages" class="flex flex-1 flex-col gap-3 overflow-y-auto bg-mist-50 p-4" aria-live="polite">
                    <div class="max-w-[88%] rounded-2xl rounded-tl-md bg-white px-3.5 py-2.5 text-sm leading-6 text-mist-700 shadow-sm">
                        Hello. Ask me about church data or tell me an approved task to perform.
                    </div>
                </div>
                <div class="border-t border-mist-200 bg-white p-3">
                    <form id="ai-chat-form" class="flex items-end gap-2">
                        <textarea id="ai-chat-input" rows="1" maxlength="500" required placeholder="Type your question..." class="min-h-11 max-h-28 flex-1 resize-none rounded-xl border border-mist-200 px-3 py-2.5 text-sm outline-none focus:border-royal-400 focus:ring-2 focus:ring-royal-100"></textarea>
                        <button type="submit" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-royal-600 text-white transition hover:bg-royal-700" aria-label="Send question">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 14-7-3 7 3 7-14-7Zm0 0h9"/></svg>
                        </button>
                    </form>
                    <button type="button" id="ai-chat-summary" class="mt-2 text-xs font-semibold text-royal-700 hover:underline">Generate church summary</button>
                </div>
            </section>
            <button type="button" id="ai-chat-toggle" class="flex h-14 w-14 items-center justify-center rounded-full bg-royal-700 text-white shadow-lg shadow-royal-900/25 transition hover:scale-105 hover:bg-royal-800 focus:outline-none focus:ring-4 focus:ring-royal-200" aria-label="Open church assistant" aria-expanded="false">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5m7-2a8 8 0 0 1-8 8 8.5 8.5 0 0 1-4.1-1.05L3 21l1.9-4.2A8.38 8.38 0 0 1 3 12a8.5 8.5 0 0 1 17 0Z"/></svg>
            </button>
        </div>
        <?php endif; ?>

        <footer id="app-footer" class="border-t border-mist-200 px-6 py-3 text-center text-xs text-mist-500 bg-white/70">
            &copy; <?= date('Y') ?> <?= htmlspecialchars($churchName ?? 'Church CMS') ?> - Church Management Platform
        </footer>
    </div>
</div>

<script>
// Responsive table adapter: on phones each row is rendered as a labelled card.
// Labels are copied from the table header, including rows inserted later by AJAX.
function applyResponsiveTableLabels(root = document) {
    root.querySelectorAll('table').forEach(table => {
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
        if (!headers.length) return;
        table.querySelectorAll('tbody tr').forEach(row => {
            Array.from(row.children).forEach((cell, index) => {
                if (cell.tagName === 'TD' && !cell.hasAttribute('colspan')) {
                    cell.dataset.label = headers[index] || '';
                }
            });
        });
    });
}
document.addEventListener('DOMContentLoaded', () => {
    applyResponsiveTableLabels();
    const main = document.getElementById('main-content');
    if (main) {
        new MutationObserver(() => applyResponsiveTableLabels(main)).observe(main, {childList:true, subtree:true});
    }
});

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
    document.body.classList.toggle('sidebar-open', !sidebar.classList.contains('-translate-x-full'));
}

document.querySelectorAll('#sidebar nav a').forEach(link => link.addEventListener('click', () => {
    if (window.innerWidth < 1024) {
        document.getElementById('sidebar')?.classList.add('-translate-x-full');
        document.getElementById('sidebar-overlay')?.classList.add('hidden');
        document.body.classList.remove('sidebar-open');
    }
}));

const aiChatToggle = document.getElementById('ai-chat-toggle');
const aiChatPanel = document.getElementById('ai-chat-panel');
const aiChatMessages = document.getElementById('ai-chat-messages');
const aiChatInput = document.getElementById('ai-chat-input');
const aiChatUrl = (path) => `${BASE_URL}/api/v1/${path}`;

function toggleAIChat(open) {
    if (!aiChatPanel || !aiChatToggle) return;
    const shouldOpen = typeof open === 'boolean' ? open : aiChatPanel.classList.contains('hidden');
    aiChatPanel.classList.toggle('hidden', !shouldOpen);
    aiChatToggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    if (shouldOpen) aiChatInput?.focus();
}

function addAIChatMessage(text, sender = 'assistant') {
    const message = document.createElement('div');
    message.className = sender === 'user'
        ? 'ml-auto max-w-[88%] rounded-2xl rounded-tr-md bg-royal-600 px-3.5 py-2.5 text-sm leading-6 text-white'
        : 'max-w-[88%] rounded-2xl rounded-tl-md bg-white px-3.5 py-2.5 text-sm leading-6 text-mist-700 shadow-sm';
    message.textContent = text;
    aiChatMessages.appendChild(message);
    aiChatMessages.scrollTop = aiChatMessages.scrollHeight;
    return message;
}

const aiEsc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
const aiMoney = value => 'TZS ' + Number(value || 0).toLocaleString(undefined, {maximumFractionDigits: 0});
const aiPctText = change => change.percent === null ? 'New activity' : `${change.percent >= 0 ? '+' : ''}${Number(change.percent).toFixed(1)}%`;
const aiSessionLabel = value => ({main_service:'Main / Single Service',first_sermon:'First Sermon',second_sermon:'Second Sermon',third_sermon:'Third Sermon',other:'Other Session'})[value] || value || '-';

function renderAIReport(report, downloadUrl, reportType = 'Overview') {
    const current = report.current || {};
    const changes = report.changes || {};
    const rows = current.attendance_rows || [];
    const box = document.createElement('div');
    box.className = 'mt-3 space-y-3 rounded-xl border border-mist-200 bg-mist-50 p-3';
    const card = (label, value, note) => `
        <div class="rounded-lg border border-mist-200 bg-white p-2.5">
            <div class="text-[10px] font-bold uppercase text-mist-500">${aiEsc(label)}</div>
            <div class="mt-1 text-base font-bold text-mist-900">${aiEsc(value)}</div>
            <div class="mt-1 text-[11px] text-mist-500">${aiEsc(note)} vs previous period</div>
        </div>`;
    box.innerHTML = `
        <div class="grid grid-cols-2 gap-2">
            ${card('Attendance', Number(current.attendance || 0).toLocaleString(), changes.attendance ? aiPctText(changes.attendance) : '0.0%')}
            ${card('Average / Ibada', Number(current.attendance_average || 0).toFixed(1), changes.attendance_average ? aiPctText(changes.attendance_average) : '0.0%')}
            ${card('Guest visits', Number(current.guests_period || 0).toLocaleString(), changes.guests_period ? aiPctText(changes.guests_period) : '0.0%')}
            ${card('Church Giving', aiMoney(current.giving), changes.giving ? aiPctText(changes.giving) : '0.0%')}
        </div>
        <div class="rounded-lg border border-indigo-100 bg-white p-3">
            <div class="text-[10px] font-bold uppercase text-indigo-700">AI trend interpretation</div>
            <div class="mt-1 text-xs leading-5 text-mist-700">${aiEsc(report.ai_trend_summary || report.trend_summary || '')}</div>
        </div>
        <div class="overflow-x-auto rounded-lg border border-mist-200 bg-white">
            <table class="min-w-full text-left text-[11px]">
                <thead class="bg-mist-100 text-mist-600"><tr><th class="px-2 py-1.5">Ibada / Event</th><th class="px-2 py-1.5">Date</th><th class="px-2 py-1.5">Type</th><th class="px-2 py-1.5">Total</th></tr></thead>
                <tbody>
                    ${rows.slice(0, 5).map(row => `<tr class="border-t border-mist-100"><td class="px-2 py-1.5 font-semibold">${aiEsc(row.service_name)}</td><td class="px-2 py-1.5">${aiEsc(row.service_date)}</td><td class="px-2 py-1.5">${aiEsc(aiSessionLabel(row.session_type))}</td><td class="px-2 py-1.5 font-semibold text-royal-700">${aiEsc(row.total_count)}</td></tr>`).join('') || '<tr><td colspan="4" class="px-2 py-2 text-mist-500">No attendance records in this period.</td></tr>'}
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="rounded-xl bg-royal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-royal-700" href="${aiEsc(downloadUrl)}">Download PDF</a>
            <a class="rounded-xl border border-mist-200 bg-white px-3 py-2 text-xs font-semibold text-mist-700 hover:bg-mist-50" href="${BASE_URL}/reports">Open Reports Center</a>
        </div>
    `;
    return box;
}

async function askAIChat(question) {
    const cleanQuestion = question.trim();
    if (!cleanQuestion) return;
    addAIChatMessage(cleanQuestion, 'user');
    aiChatInput.value = '';
    aiChatInput.disabled = true;
    const pending = addAIChatMessage('Following the approved workflow...');
    try {
        const response = await fetch(aiChatUrl('ai/query'), {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({question: cleanQuestion})
        });
        const data = await response.json();
        pending.textContent = data?.data?.answer || data?.message || 'No response available.';
        const action = data?.data?.action;
        if (action?.type === 'report' && action?.report) {
            pending.appendChild(renderAIReport(action.report, action.download_url, action.report_type));
        }
        if (action?.type === 'confirm' && action?.token) {
            const actions = document.createElement('div');
            actions.className = 'mt-3 flex flex-wrap gap-2';
            const confirm = document.createElement('button');
            confirm.type = 'button';
            confirm.textContent = action.label || 'Confirm action';
            confirm.className = 'rounded-xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700';
            const cancel = document.createElement('button');
            cancel.type = 'button';
            cancel.textContent = 'Cancel';
            cancel.className = 'rounded-xl border border-mist-200 bg-white px-3 py-2 text-xs font-semibold text-mist-700 hover:bg-mist-50';
            confirm.addEventListener('click', async () => {
                confirm.disabled = true; cancel.disabled = true; confirm.textContent = 'Working…';
                try {
                    const r = await fetch(aiChatUrl('ai/confirm'), {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({token:action.token})});
                    const j = await r.json();
                    addAIChatMessage(j?.message || j?.data?.message || (r.ok ? 'Task completed.' : 'Task could not be completed.'));
                } catch (e) { addAIChatMessage('The task could not be completed right now.'); }
                actions.remove();
            });
            cancel.addEventListener('click', async () => {
                try { await fetch(aiChatUrl('ai/cancel'), {method:'POST'}); } catch(e) {}
                addAIChatMessage('Pending task cancelled.'); actions.remove();
            });
            actions.append(confirm,cancel); pending.appendChild(actions);
        }
    } catch (error) {
        pending.textContent = 'Unable to contact the assistant right now.';
    } finally {
        aiChatInput.disabled = false;
        aiChatInput.focus();
    }
}

if (aiChatToggle) {
    aiChatToggle.addEventListener('click', () => toggleAIChat());
    document.getElementById('ai-chat-close').addEventListener('click', () => toggleAIChat(false));
    document.getElementById('ai-chat-form').addEventListener('submit', event => {
        event.preventDefault();
        askAIChat(aiChatInput.value);
    });
    aiChatInput.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            if (!aiChatInput.disabled) askAIChat(aiChatInput.value);
        }
    });
    document.getElementById('ai-chat-summary').addEventListener('click', async () => {
        aiChatInput.disabled = true;
        const pending = addAIChatMessage('Following the approved workflow...');
        try {
            const response = await fetch(aiChatUrl('ai/summary'));
            const data = await response.json();
            pending.textContent = data?.data?.answer || data?.message || 'No summary available.';
        } catch (error) {
            pending.textContent = 'Unable to generate the summary right now.';
        } finally {
            aiChatInput.disabled = false;
            aiChatInput.focus();
        }
    });
}
</script>
<style>
#ai-chat-widget { position: fixed; right: 1.75rem; bottom: 1.75rem; z-index: 9999; max-width: calc(100vw - 2rem); }
#ai-chat-toggle { width: 3.5rem; height: 3.5rem; border: 0; border-radius: 9999px; background: #3344a5; color: #fff; cursor: pointer; box-shadow: 0 10px 25px rgba(15, 23, 42, .25); }
#ai-chat-toggle:hover { background: #27358f; }
#ai-chat-panel { width: min(380px, calc(100vw - 2rem)); max-height: calc(100vh - 7rem); display: flex; flex-direction: column; }
#ai-chat-panel.hidden { display: none; }
#ai-chat-messages { min-height: 12rem; max-height: none; overscroll-behavior: contain; }
@media (max-width: 640px) {
    #ai-chat-widget { right: 1rem; bottom: 1rem; }
    #ai-chat-panel { max-height: calc(100vh - 6rem); }
    #ai-chat-messages { min-height: 10rem; }
}
</style>
</body>
</html>
