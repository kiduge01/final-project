 <?php
/**
 * Unified Login Page - Main Entry Point for All Systems
 * Supports: Admin (all roles) + Department Heads
 * 
 * Login roles:
 * - Admin: Administrator, Pastor, Accountant, Secretary
 * - Department: Department Head
 */

$baseUrl = $baseUrl ?? (defined("BASE_URL") ? BASE_URL : "");
$churchName = htmlspecialchars($churchName ?? 'Church CMS', ENT_QUOTES, 'UTF-8');
$error = $error ?? null;
$logoUrl = $logoUrl ?? null;
$churchLogo = $churchLogo ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $churchName ?> &mdash; Sign In</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ── Reset & Tokens ── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            /* Brand */
            --gold:       #0F766E;
            --gold-light: #14B8A6;
            --gold-bg:    rgba(20,184,166,.10);
            --gold-border:rgba(20,184,166,.24);
            --gold-glow:  rgba(20,184,166,.16);

            /* Surfaces */
            --bg-page:    #0B1220;
            --bg-brand:   #0F172A;
            --bg-card:    rgba(255,255,255,.04);
            --bg-input:   rgba(255,255,255,.05);
            --bg-input-f: rgba(255,255,255,.08);

            /* Borders */
            --border:       rgba(255,255,255,.08);
            --border-focus: rgba(212,160,23,.50);

            /* Text */
            --text:     #F1F1F4;
            --text-mid: rgba(255,255,255,.65);
            --text-dim: rgba(255,255,255,.40);
            --text-inv: #FFFFFF;

            /* Danger */
            --danger-bg:     rgba(239,68,68,.10);
            --danger-border: rgba(239,68,68,.25);
            --danger-text:   #fca5a5;

            /* Spacing base */
            --sp: 8px;

            /* Radius */
            --r-sm: 8px;
            --r-md: 12px;
            --r-lg: 16px;
            --r-xl: 20px;
        }

        html, body {
            width: 100%; height: 100%;
            font-family: "Inter", system-ui, -apple-system, sans-serif;
            background: var(--bg-page); color: var(--text);
            -webkit-font-smoothing: antialiased;
            overflow: hidden; line-height: 1;
        }
        html { display: flex; }
        body { flex: 1; }

        /* ── Split Layout ── */
        .login-shell {
            display: grid;
            grid-template-columns: 2fr 3fr;   /* 40 / 60 */
            width: 100%; height: 100%;
        }

        /* ══════════════════════════════════
           LEFT: Brand Panel
           ══════════════════════════════════ */
        .brand-panel {
            position: relative; overflow: hidden;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: calc(var(--sp)*5) calc(var(--sp)*4);
            background: var(--bg-brand);
        }

        /* Soft ambient glow */
        .brand-panel::before {
            content: ''; position: absolute; pointer-events: none;
            width: 420px; height: 420px;
            top: 50%; left: 50%; transform: translate(-50%,-50%);
            background: radial-gradient(circle, rgba(20,184,166,.08) 0%, transparent 70%);
            animation: ambientPulse 8s ease-in-out infinite;
        }
        @keyframes ambientPulse { 0%,100% { opacity: .6; transform: translate(-50%,-50%) scale(1); } 50% { opacity: 1; transform: translate(-50%,-50%) scale(1.12); } }

        /* Faint cross watermark */
        .cross-watermark {
            position: absolute; pointer-events: none;
            width: 240px; height: 240px;
            top: 50%; left: 50%; transform: translate(-50%,-50%);
            opacity: .035;
        }

        .brand-inner {
            position: relative; z-index: 2;
            text-align: center; max-width: 320px;
            animation: fadeUp .7s ease-out both;
        }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(22px); } to { opacity: 1; transform: translateY(0); } }

        /* Logo */
        .brand-logo {
            width: 80px; height: 80px;
            margin: 0 auto calc(var(--sp)*3);
            position: relative;
        }
        .brand-logo::after {
            content: ''; position: absolute; inset: -6px;
            border-radius: 50%;
            border: 1px solid rgba(20,184,166,.18);
        }
        .brand-logo img,
        .brand-logo svg {
            width: 100%; height: 100%;
            object-fit: contain;
            border-radius: 50%;
            filter: drop-shadow(0 2px 12px rgba(20,184,166,.2));
        }

        /* Name */
        .brand-name {
            font-size: 1.75rem; font-weight: 700;
            letter-spacing: .3px; line-height: 1.2;
            color: var(--text);
            margin-bottom: calc(var(--sp)*1);
        }

        /* Tagline */
        .brand-tagline {
            font-size: .72rem; font-weight: 500;
            text-transform: uppercase; letter-spacing: 2.5px;
            color: var(--gold);
            margin-bottom: calc(var(--sp)*2);
        }

        /* Divider */
        .brand-divider {
            width: 40px; height: 1px; margin: 0 auto calc(var(--sp)*2.5);
            background: linear-gradient(90deg, transparent, rgba(212,160,23,.25), transparent);
        }

        /* Feature list */
        .brand-features {
            display: flex; flex-direction: column; gap: calc(var(--sp)*1.5);
            text-align: left; width: 100%;
            animation: fadeUp .7s ease-out .2s both;
        }
        .feature-row {
            display: flex; align-items: center; gap: calc(var(--sp)*1.5);
            padding: calc(var(--sp)*1) calc(var(--sp)*1.5);
            border-radius: var(--r-sm);
            transition: background .2s;
        }
        .feature-row:hover { background: rgba(255,255,255,.03); }
        .feature-icon {
            flex-shrink: 0; width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            border-radius: var(--r-sm);
            background: var(--gold-bg);
            border: 1px solid var(--gold-border);
        }
        .feature-icon svg { width: 16px; height: 16px; color: var(--gold); }
        .feature-text { font-size: .8rem; color: var(--text-mid); line-height: 1.4; }
        .feature-text strong { color: var(--text); font-weight: 600; display: block; font-size: .82rem; }

        /* Version footer */
        .brand-footer {
            position: absolute; bottom: calc(var(--sp)*3);
            left: 0; right: 0; text-align: center;
            font-size: .65rem; color: var(--text-dim); letter-spacing: .3px;
        }

        /* ══════════════════════════════════
           RIGHT: Form Panel
           ══════════════════════════════════ */
        .form-panel {
            display: flex; align-items: center; justify-content: center;
            padding: calc(var(--sp)*4);
            background: var(--bg-page);
        }

        .form-card {
            width: 100%; max-width: 400px;
            animation: cardIn .6s ease-out .1s both;
        }
        @keyframes cardIn { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

        .form-box {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--r-xl);
            padding: calc(var(--sp)*4) calc(var(--sp)*3.5);
            box-shadow: 0 4px 24px rgba(0,0,0,.25);
        }

        .form-heading {
            font-size: 1.5rem; font-weight: 700;
            margin-bottom: calc(var(--sp)*0.5);
        }
        .form-sub {
            font-size: .85rem; color: var(--text-dim);
            margin-bottom: calc(var(--sp)*3);
        }

        /* Error */
        .error-banner {
            display: flex; align-items: flex-start; gap: calc(var(--sp)*1);
            padding: calc(var(--sp)*1.5) calc(var(--sp)*2);
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            border-radius: var(--r-md);
            color: var(--danger-text);
            font-size: .82rem; line-height: 1.45;
            margin-bottom: calc(var(--sp)*2.5);
            animation: shake .4s ease-out;
        }
        .error-banner svg { flex-shrink: 0; margin-top: 1px; }
        @keyframes shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-6px); } 75% { transform: translateX(4px); } }

        /* Login method toggle */
        .login-toggle {
            display: flex;
            background: rgba(255,255,255,.03);
            border: 1px solid var(--border);
            border-radius: var(--r-md); padding: 3px;
            margin-bottom: calc(var(--sp)*2.5);
        }
        .login-toggle-btn {
            flex: 1; display: flex; align-items: center; justify-content: center;
            gap: 6px; padding: calc(var(--sp)*1) calc(var(--sp)*1);
            border: none; border-radius: calc(var(--r-md) - 2px);
            background: transparent; color: var(--text-dim);
            font-size: .78rem; font-weight: 500;
            font-family: inherit; cursor: pointer;
            transition: all .2s;
        }
        .login-toggle-btn.active {
            background: var(--gold-bg); color: var(--gold);
            border: 1px solid var(--gold-border);
        }
        .login-toggle-btn:not(.active):hover {
            color: var(--text-mid); background: rgba(255,255,255,.03);
        }
        .login-toggle-btn svg { width: 15px; height: 15px; flex-shrink: 0; }

        /* Fields */
        .field { margin-bottom: calc(var(--sp)*2); }
        .field-label {
            display: block;
            font-size: .72rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .5px;
            color: var(--text-mid);
            margin-bottom: calc(var(--sp)*0.75);
        }
        .field-wrap { position: relative; }
        .field-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            width: 18px; height: 18px;
            color: var(--text-mid); pointer-events: none;
            transition: color .2s;
        }
        .field-input {
            width: 100%;
            background: var(--bg-input);
            border: 1.5px solid var(--border);
            border-radius: var(--r-md);
            padding: calc(var(--sp)*1.5) calc(var(--sp)*1.5) calc(var(--sp)*1.5) calc(var(--sp)*5);
            color: var(--text); font-size: .88rem;
            font-family: inherit;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .field-input::placeholder { color: rgba(255,255,255,.25); }
        .field-input:focus {
            outline: none;
            border-color: var(--border-focus);
            background: var(--bg-input-f);
            box-shadow: 0 0 0 3px var(--gold-glow);
        }
        .field-wrap:focus-within .field-icon { color: var(--gold); }

        /* Password toggle */
        .pw-toggle {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; padding: 4px;
            cursor: pointer; color: #888; transition: color .2s;
            z-index: 2;
        }
        .pw-toggle:hover { color: var(--gold); }
        .pw-toggle svg { width: 18px; height: 18px; display: block; }

        /* Submit */
        .btn-submit {
            position: relative;
            display: flex; align-items: center; justify-content: center;
            width: 100%; margin-top: calc(var(--sp)*3);
            padding: calc(var(--sp)*1.5) calc(var(--sp)*2);
            background: linear-gradient(135deg, var(--gold-light), var(--gold));
            color: var(--text-inv); border: none;
            border-radius: var(--r-md);
            font-size: .9rem; font-weight: 700;
            font-family: inherit; letter-spacing: .2px;
            cursor: pointer; overflow: hidden;
            transition: transform .15s, box-shadow .15s;
            box-shadow: 0 2px 12px rgba(20,184,166,.22);
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(20,184,166,.32);
        }
        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 1px 6px rgba(20,184,166,.18);
        }
        .btn-submit:disabled { opacity: .65; cursor: not-allowed; transform: none !important; }
        .btn-submit .btn-label { transition: opacity .15s; }
        .btn-submit.is-loading .btn-label { opacity: 0; }
        .btn-submit .spinner {
            position: absolute;
            width: 18px; height: 18px;
            border: 2.5px solid rgba(15,18,37,.2);
            border-top-color: var(--text-inv);
            border-radius: 50%; opacity: 0;
            transition: opacity .15s;
        }
        .btn-submit.is-loading .spinner { opacity: 1; animation: spin .55s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Shimmer on hover */
        .btn-submit::after {
            content: ''; position: absolute; top: 0; left: -100%; width: 60%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent);
        }
        .btn-submit:hover::after { animation: shimmer .7s; }
        @keyframes shimmer { to { left: 120%; } }

        /* Forgot link */
        .forgot-link {
            display: block; text-align: center;
            margin-top: calc(var(--sp)*2);
            font-size: .8rem; color: var(--text-dim);
            text-decoration: none; transition: color .2s;
        }
        .forgot-link:hover { color: var(--gold); }

        /* Secure note */
        .secure-note {
            display: flex; align-items: center; justify-content: center;
            gap: 6px; margin-top: calc(var(--sp)*3);
            font-size: .68rem; color: var(--text-dim);
            letter-spacing: .2px;
        }
        .secure-note svg { width: 13px; height: 13px; color: var(--text-dim); }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .login-shell {
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr;
            }
            .brand-panel { padding: calc(var(--sp)*3) calc(var(--sp)*2.5); }
            .brand-logo { width: 56px; height: 56px; margin-bottom: calc(var(--sp)*2); }
            .brand-name { font-size: 1.3rem; }
            .brand-tagline { font-size: .68rem; margin-bottom: calc(var(--sp)*2); }
            .brand-divider { margin-bottom: calc(var(--sp)*2); }
            .brand-features { display: none; }
            .brand-footer { display: none; }
            .cross-watermark { display: none; }
            .form-panel { padding: 0 calc(var(--sp)*2) calc(var(--sp)*3); }
            .form-box { padding: calc(var(--sp)*3) calc(var(--sp)*2.5); }
            .form-heading { font-size: 1.3rem; }
        }
        @media (max-width: 480px) {
            .form-box { border-radius: var(--r-lg); padding: calc(var(--sp)*2.5) calc(var(--sp)*2); }
            .btn-submit { padding: calc(var(--sp)*1.75) calc(var(--sp)*2); font-size: .88rem; }
        }
    </style>
</head>
<body>

<div class="login-shell">

    <!-- ══ LEFT: Brand ══ -->
    <div class="brand-panel">

        <!-- Faint cross watermark -->
        <svg class="cross-watermark" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="52" y="10" width="16" height="100" rx="8" fill="white"/>
            <rect x="20" y="36" width="80" height="16" rx="8" fill="white"/>
        </svg>

        <div class="brand-inner">
            <!-- Logo -->
            <div class="brand-logo">
                <?php if (!empty($churchLogo)): ?>
                    <img src="<?= htmlspecialchars($baseUrl . $churchLogo) ?>" alt="<?= $churchName ?>">
                <?php else: ?>
                    <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="40" cy="40" r="38" stroke="#14B8A6" stroke-width="1" opacity=".24"/>
                        <rect x="37" y="14" width="6" height="52" rx="3" fill="#14B8A6" opacity=".9"/>
                        <rect x="22" y="28" width="36" height="6" rx="3" fill="#14B8A6" opacity=".9"/>
                    </svg>
                <?php endif; ?>
            </div>

            <h1 class="brand-name"><?= $churchName ?></h1>
            <p class="brand-tagline">Manage &middot; Grow &middot; Serve</p>

            <div class="brand-divider"></div>

            <!-- Feature highlights -->
            <div class="brand-features">
                <div class="feature-row">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    </div>
                    <div class="feature-text"><strong>Member Management</strong>Track families, groups &amp; attendance</div>
                </div>
                <div class="feature-row">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    </div>
                    <div class="feature-text"><strong>Finance &amp; Budgets</strong>Income, expenses &amp; approvals</div>
                </div>
                <div class="feature-row">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div class="feature-text"><strong>Events &amp; Calendar</strong>Schedule &amp; coordinate activities</div>
                </div>
                <div class="feature-row">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    </div>
                    <div class="feature-text"><strong>Communication</strong>Messages &amp; notifications</div>
                </div>
            </div>
        </div>

        <div class="brand-footer">&copy; <?= date('Y') ?> <?= $churchName ?> &middot; v1.0</div>
    </div>

    <!-- ══ RIGHT: Form ══ -->
    <div class="form-panel">
        <div class="form-card">
            <div class="form-box">
                <h2 class="form-heading">Welcome back</h2>
                <p class="form-sub">Sign in to continue</p>

                <?php if ($error): ?>
                    <div class="error-banner">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form id="loginForm" method="POST" action="<?= \App\Core\Route::get('login') ?>" novalidate>
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(\App\Core\Auth::getCsrfToken()) ?>">

                    <!-- Login method toggle -->
                    <div class="login-toggle">
                        <button type="button" class="login-toggle-btn active" id="togEmail" onclick="switchLogin('email')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg>
                            Email
                        </button>
                        <button type="button" class="login-toggle-btn" id="togPhone" onclick="switchLogin('phone')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                            Phone
                        </button>
                    </div>

                    <!-- Email -->
                    <div class="field" id="emailField">
                        <label for="email" class="field-label">Email address</label>
                        <div class="field-wrap">
                            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/>
                            </svg>
                            <input type="email" id="email" name="email" class="field-input"
                                   placeholder="you@example.com" autocomplete="email" inputmode="email" required>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="field" id="phoneField" style="display:none">
                        <label for="phone" class="field-label">Phone number</label>
                        <div class="field-wrap">
                            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                            </svg>
                            <input type="tel" id="phone" name="phone" class="field-input"
                                   placeholder="+255 7XX XXX XXX" autocomplete="tel"
                                   inputmode="tel" pattern="[+0-9\s\-]*">
                        </div>
                    </div>

                     <!-- Password -->
                    <div class="field">
                        <label for="password" class="field-label">Password</label>
                        <div class="field-wrap">
                            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                            <input type="password" id="password" name="password" class="field-input"
                                   style="padding-right:2.8rem" placeholder="Enter your password"
                                   autocomplete="current-password" required minlength="8">
                            <button type="button" class="pw-toggle" onclick="togglePw()" aria-label="Toggle password visibility">
                                <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="btnSubmit">
                        <span class="btn-label">Sign In</span>
                        <span class="spinner"></span>
                    </button>
                </form>

                <a href="<?= \App\Core\Route::get('forgot_password') ?>" class="forgot-link">Forgot your password?</a>

                <div class="secure-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                    Secure, encrypted connection
                </div>
            </div>
        </div>
    </div>

</div>

<script>
/* ── Toggle login mode (Email / Phone) ── */
let loginMode = 'email';
function switchLogin(mode) {
    loginMode = mode;
    const eF = document.getElementById('emailField');
    const pF = document.getElementById('phoneField');
    const tE = document.getElementById('togEmail');
    const tP = document.getElementById('togPhone');
    const eI = document.getElementById('email');
    const pI = document.getElementById('phone');
    if (mode === 'email') {
        eF.style.display = ''; pF.style.display = 'none';
        tE.classList.add('active'); tP.classList.remove('active');
        eI.required = true; pI.required = false; pI.value = '';
        eI.focus();
    } else {
        eF.style.display = 'none'; pF.style.display = '';
        tP.classList.add('active'); tE.classList.remove('active');
        pI.required = true; eI.required = false; eI.value = '';
        pI.focus();
    }
}
switchLogin('email');

/* ── Phone field: allow only digits, +, space, dash ── */
document.getElementById('phone').addEventListener('input', function () {
    this.value = this.value.replace(/[^0-9+\s\-]/g, '');
});

/* ── Show / Hide password ── */
function togglePw() {
    const inp = document.getElementById('password');
    const ico = document.getElementById('eyeIcon');
    const show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    ico.innerHTML = show
        ? '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>'
        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/>';
}

/* ── Loading state on submit ── */
document.getElementById('loginForm').addEventListener('submit', function (e) {
    if (loginMode === 'email' && !document.getElementById('email').value.trim()) {
        e.preventDefault(); document.getElementById('email').focus(); return;
    }
    if (loginMode === 'phone' && !document.getElementById('phone').value.trim()) {
        e.preventDefault(); document.getElementById('phone').focus(); return;
    }
    if (!document.getElementById('password').value) {
        e.preventDefault(); document.getElementById('password').focus(); return;
    }
    const btn = document.getElementById('btnSubmit');
    btn.classList.add('is-loading');
    btn.disabled = true;
});
</script>
</body>
</html>

