<?php
// Ensure session is available for sidebar user info
require_once __DIR__ . '/session.php';

$_deptName  = htmlspecialchars($_SESSION['department_name'] ?? 'Department', ENT_QUOTES, 'UTF-8');
$_headName  = htmlspecialchars($_SESSION['head_name'] ?? $_SESSION['department_name'] ?? 'Head', ENT_QUOTES, 'UTF-8');
$_headEmail = htmlspecialchars($_SESSION['head_email'] ?? '', ENT_QUOTES, 'UTF-8');
$_initial   = strtoupper(substr($_SESSION['department_name'] ?? 'D', 0, 1));
$_pageTitle = isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') : 'Department';
$_currentPath = $_SERVER['PHP_SELF'] ?? '';

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return 'Tsh ' . number_format((float)$amount, 0, '.', ',');
    }
}

function _deptMenuActive(string $segment): string {
    global $_currentPath;
    return str_contains($_currentPath, '/' . $segment . '/') ? 'bg-royal-700 text-white shadow-sm' : 'text-white/70 hover:bg-white/[0.07] hover:text-white';
}
function _deptMenuIconActive(string $segment): string {
    global $_currentPath;
    return str_contains($_currentPath, '/' . $segment . '/') ? 'text-royal-100' : 'text-white/40 group-hover:text-royal-200';
}
?>
<!doctype html>
<html lang="en" class="h-full bg-mist-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $_pageTitle ?> - <?= $_deptName ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Precompiled, purged Tailwind build (replaces the CDN JIT compiler for speed) -->
    <link rel="stylesheet" href="<?= \App\Core\Url::to('assets/css/tailwind.css') ?>">
    <link rel="stylesheet" href="<?= \App\Core\Url::to('assets/css/app.css') ?>">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; padding: 0; }
        h1,h2,h3,h4,h5,h6 { font-family: 'Inter', system-ui, sans-serif; letter-spacing: -0.01em; }
        .dept-nav-sep { height: 1px; background: rgba(255,255,255,0.1); margin: 8px 12px; }
        .rounded-2xl,.rounded-xl { border-radius: .5rem !important; }
        input,select,textarea { border-color: #e2e8f0 !important; }
        input:focus,select:focus,textarea:focus { border-color: #0f766e !important; box-shadow: 0 0 0 4px rgba(20,184,166,.12) !important; }
        table thead { background: #f8fafc !important; }
        table tbody tr:hover { background: #f8fafc !important; }
        button,a,input,select,textarea { transition: background-color .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease, transform .15s ease; }
        button:hover { transform: translateY(-1px); }
    </style>
</head>
<body class="h-full font-body text-mist-900 bg-mist-50 antialiased">
<div class="min-h-full flex">

    <!-- ── Sidebar ── -->
    <aside id="dept-sidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-mist-900 transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out flex flex-col shadow-xl shadow-mist-900/20">

        <!-- Brand -->
        <div class="flex items-center gap-3 px-5 py-5 border-b border-white/10">
            <div class="w-10 h-10 rounded-lg bg-glory-400 text-mist-950 flex items-center justify-center shadow-md font-bold text-lg font-heading">
                <?= $_initial ?>
            </div>
            <div>
                <h1 class="text-white font-heading font-bold text-base leading-tight truncate max-w-[150px]"><?= $_deptName ?></h1>
                <p class="text-white/55 text-[11px] tracking-wide">Department Portal</p>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

            <!-- Dashboard -->
            <a href="<?= htmlspecialchars(departmentUrl('dashboard/index.php'), ENT_QUOTES, 'UTF-8') ?>"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-150 <?= str_contains($_currentPath, '/dashboard/') ? 'bg-royal-700 text-white shadow-sm' : 'text-white/70 hover:bg-white/[0.07] hover:text-white' ?>">
                <svg class="w-5 h-5 flex-shrink-0 <?= str_contains($_currentPath, '/dashboard/') ? 'text-royal-100' : 'text-white/40 group-hover:text-royal-200' ?>" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <div class="dept-nav-sep"></div>

            <a href="<?= htmlspecialchars(departmentUrl('members/view.php'), ENT_QUOTES, 'UTF-8') ?>"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-150 <?= str_contains($_currentPath, '/members/') ? 'bg-royal-700 text-white shadow-sm' : 'text-white/70 hover:bg-white/[0.07] hover:text-white' ?>">
                <svg class="w-5 h-5 flex-shrink-0 <?= _deptMenuIconActive('members') ?>" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Members
            </a>

            <div class="dept-nav-sep"></div>

            <a href="<?= htmlspecialchars(departmentUrl('leaders/view.php'), ENT_QUOTES, 'UTF-8') ?>"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-150 <?= str_contains($_currentPath, '/leaders/') ? 'bg-royal-700 text-white shadow-sm' : 'text-white/70 hover:bg-white/[0.07] hover:text-white' ?>">
                <svg class="w-5 h-5 flex-shrink-0 <?= _deptMenuIconActive('leaders') ?>" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                </svg>
                Leaders
            </a>

            <div class="dept-nav-sep"></div>

            <a href="<?= htmlspecialchars(departmentUrl('finance/view.php'), ENT_QUOTES, 'UTF-8') ?>"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-150 <?= str_contains($_currentPath, '/finance/') ? 'bg-royal-700 text-white shadow-sm' : 'text-white/70 hover:bg-white/[0.07] hover:text-white' ?>">
                <svg class="w-5 h-5 flex-shrink-0 <?= _deptMenuIconActive('finance') ?>" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Finance
            </a>

            <div class="dept-nav-sep"></div>

            <a href="<?= htmlspecialchars(departmentUrl('reports/view.php'), ENT_QUOTES, 'UTF-8') ?>"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-150 <?= str_contains($_currentPath, '/reports/') ? 'bg-royal-700 text-white shadow-sm' : 'text-white/70 hover:bg-white/[0.07] hover:text-white' ?>">
                <svg class="w-5 h-5 flex-shrink-0 <?= _deptMenuIconActive('reports') ?>" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Reports
            </a>

            <div class="dept-nav-sep"></div>

            <a href="<?= htmlspecialchars(departmentUrl('contributions/view.php'), ENT_QUOTES, 'UTF-8') ?>"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-150 <?= str_contains($_currentPath, '/contributions/') ? 'bg-royal-700 text-white shadow-sm' : 'text-white/70 hover:bg-white/[0.07] hover:text-white' ?>">
                <svg class="w-5 h-5 flex-shrink-0 <?= _deptMenuIconActive('contributions') ?>" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
                </svg>
                Contributions
            </a>

            <div class="dept-nav-sep"></div>

            <a href="<?= htmlspecialchars(departmentUrl('assets/view.php'), ENT_QUOTES, 'UTF-8') ?>"
               class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition-all duration-150 <?= str_contains($_currentPath, '/assets/') ? 'bg-royal-700 text-white shadow-sm' : 'text-white/70 hover:bg-white/[0.07] hover:text-white' ?>">
                <svg class="w-5 h-5 flex-shrink-0 <?= _deptMenuIconActive('assets') ?>" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                </svg>
                Assets
            </a>

        </nav>

        <!-- User block + logout -->
        <div class="px-4 py-4 border-t border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-royal-500 text-white flex items-center justify-center font-bold text-sm">
                    <?= $_initial ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-semibold truncate"><?= $_headName ?></p>
                    <p class="text-white/55 text-xs truncate">Department Head</p>
                </div>
            </div>
            <a href="<?= htmlspecialchars(departmentUrl('auth/logout.php'), ENT_QUOTES, 'UTF-8') ?>"
               class="mt-3 w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm text-white/75 hover:bg-white/[0.07] hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Sign out
            </a>
        </div>
    </aside>

    <!-- Sidebar overlay (mobile) -->
    <div id="dept-sidebar-overlay" class="fixed inset-0 bg-gray-900/50 z-20 hidden lg:hidden" onclick="document.getElementById('dept-sidebar').classList.add('-translate-x-full');this.classList.add('hidden')"></div>

    <!-- ── Main content area ── -->
    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">

        <!-- Top bar -->
        <header class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b border-mist-200 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-[72px] gap-4">
                <!-- Mobile menu toggle -->
                <button onclick="document.getElementById('dept-sidebar').classList.toggle('-translate-x-full');document.getElementById('dept-sidebar-overlay').classList.toggle('hidden')"
                    class="lg:hidden p-2 rounded-lg text-mist-600 hover:bg-mist-100 hover:text-mist-800 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="hidden sm:block">
                    <h2 class="text-xl font-heading font-bold text-mist-900"><?= $_pageTitle ?></h2>
                    <p class="text-xs text-mist-500 mt-0.5">Focused workspace for department operations.</p>
                </div>
                <div class="flex items-center gap-3 ml-auto">
                    <div class="hidden md:flex items-center gap-2 px-3 py-2 rounded-lg bg-mist-50 border border-mist-200">
                        <span class="w-2 h-2 rounded-full bg-royal-500"></span>
                        <span class="text-sm text-mist-700 font-semibold"><?= date('D, d M Y') ?></span>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-royal-100 text-royal-800 flex items-center justify-center font-bold text-sm ring-1 ring-royal-200">
                        <?= $_initial ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-7 relative">
            <div class="relative z-10">
