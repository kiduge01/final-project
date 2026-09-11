<?php
/** @var string $token */
/** @var string|null $error */
$B  = $baseUrl ?? (defined("BASE_URL") ? BASE_URL : "");
$cn = htmlspecialchars($churchName ?? 'Church CMS', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?= $cn ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --gold: #FACC15; --gold-glow: rgba(250,204,21,.18);
            --surface: rgba(255,255,255,.06); --border: rgba(255,255,255,.10); --border-focus: rgba(250,204,21,.50);
            --text: #fff; --text-dim: rgba(255,255,255,.55); --text-mid: rgba(255,255,255,.75);
            --danger-bg: rgba(239,68,68,.12); --danger-border: rgba(239,68,68,.28); --danger-text: #fca5a5;
        }
        html, body { width: 100%; height: 100%; font-family: "Inter", system-ui, sans-serif; background: #0c0c1d; color: var(--text); overflow: hidden; }
        .bg-wrap { position: fixed; inset: 0; z-index: 0; overflow: hidden; }
        .bg-gradient { position: absolute; inset: -50%; width: 200%; height: 200%;
            background: conic-gradient(from 160deg at 55% 45%, #4C1D95 0deg, #1e3a8a 120deg, #3b0764 240deg, #4C1D95 360deg);
            animation: bgSpin 30s linear infinite; filter: blur(100px); opacity: .7; }
        @keyframes bgSpin { to { transform: rotate(360deg); } }
        .bg-noise { position: absolute; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            background-repeat: repeat; background-size: 200px; opacity: .5; }
        .page-center { position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; padding: 2rem; }
        .card { width: 100%; max-width: 420px; background: var(--surface); backdrop-filter: blur(24px) saturate(1.4); border: 1px solid var(--border); border-radius: 20px; padding: 2.5rem 2rem; box-shadow: 0 8px 40px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.06); animation: cardIn .7s ease-out both; }
        @keyframes cardIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .card-heading { font-family: "Playfair Display", serif; font-size: 1.65rem; font-weight: 700; margin-bottom: .25rem; }
        .card-sub { font-size: .85rem; color: var(--text-dim); margin-bottom: 1.5rem; }
        .alert { display: flex; align-items: center; gap: .6rem; padding: .7rem 1rem; border-radius: 10px; font-size: .8rem; margin-bottom: 1.25rem; }
        .alert-error { background: var(--danger-bg); border: 1px solid var(--danger-border); color: var(--danger-text); animation: shake .45s ease-out; }
        @keyframes shake { 0%,100% { transform: translateX(0); } 20% { transform: translateX(-8px); } 60% { transform: translateX(6px); } }
        .field { margin-bottom: 1.25rem; }
        .field-label { display: block; font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .6px; color: var(--text-mid); margin-bottom: .45rem; }
        .field-input-wrap { position: relative; }
        .field-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 17px; height: 17px; color: rgba(250,204,21,.35); pointer-events: none; transition: color .25s; }
        .field-input { width: 100%; background: rgba(255,255,255,.04); border: 1.5px solid var(--border); border-radius: 10px; padding: .8rem .85rem .8rem 2.7rem; color: var(--text); font-size: .88rem; font-family: inherit; transition: border-color .25s, box-shadow .25s, background .25s; }
        .field-input::placeholder { color: rgba(255,255,255,.3); }
        .field-input:focus { outline: none; border-color: var(--border-focus); background: rgba(255,255,255,.07); box-shadow: 0 0 0 3px var(--gold-glow); }
        .field-input-wrap:focus-within .field-icon { color: var(--gold); }
        .toggle-pw { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: rgba(255,255,255,.35); cursor: pointer; padding: 2px; transition: color .2s; }
        .toggle-pw:hover { color: rgba(255,255,255,.7); }
        .btn-submit { display: flex; align-items: center; justify-content: center; gap: .5rem; width: 100%; margin-top: 1.5rem; padding: .85rem 1.5rem; background: linear-gradient(135deg, #FACC15, #d4a017); color: #1a1a2e; border: none; border-radius: 11px; font-size: .92rem; font-weight: 700; font-family: inherit; cursor: pointer; transition: transform .2s, box-shadow .2s; box-shadow: 0 4px 18px rgba(250,204,21,.25); }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(250,204,21,.35); }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit:disabled { opacity: .7; cursor: not-allowed; transform: none !important; }
        .btn-submit .btn-text { transition: opacity .2s; }
        .btn-submit.is-loading .btn-text { opacity: 0; }
        .btn-submit .spinner { position: absolute; width: 20px; height: 20px; border: 2.5px solid rgba(26,26,46,.2); border-top-color: #1a1a2e; border-radius: 50%; opacity: 0; transition: opacity .2s; }
        .btn-submit.is-loading .spinner { opacity: 1; animation: spin .6s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .pw-hint { font-size: .72rem; color: var(--text-dim); margin-top: .35rem; }
        .back-link { display: flex; align-items: center; justify-content: center; gap: .4rem; margin-top: 1.25rem; font-size: .8rem; color: var(--text-dim); text-decoration: none; transition: color .2s; }
        .back-link:hover { color: var(--text); }
        @media (max-width: 480px) { .card { padding: 2rem 1.25rem; border-radius: 16px; } }
    </style>
</head>
<body>
    <div class="bg-wrap"><div class="bg-gradient"></div><div class="bg-noise"></div></div>
    <div class="page-center">
        <div class="card">
            <h1 class="card-heading">Reset Password</h1>
            <p class="card-sub">Choose a new password for your account</p>
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <form id="rpForm" method="POST" action="<?= htmlspecialchars($B . '/reset-password') ?>">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(\App\Core\Auth::getCsrfToken()) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                <div class="field">
                    <label for="password" class="field-label">New Password</label>
                    <div class="field-input-wrap">
                        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <input type="password" id="password" name="password" class="field-input" placeholder="Minimum 8 characters" minlength="8" required>
                        <button type="button" class="toggle-pw" data-target="password" aria-label="Toggle password visibility">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <p class="pw-hint">Must be at least 8 characters</p>
                </div>
                <div class="field">
                    <label for="password_confirm" class="field-label">Confirm Password</label>
                    <div class="field-input-wrap">
                        <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <input type="password" id="password_confirm" name="password_confirm" class="field-input" placeholder="Re-enter your password" minlength="8" required>
                        <button type="button" class="toggle-pw" data-target="password_confirm" aria-label="Toggle password visibility">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-submit" id="btnRp" style="position:relative">
                    <span class="btn-text">Reset Password</span>
                    <span class="spinner"></span>
                </button>
            </form>
            <a href="<?= htmlspecialchars($B . '/login') ?>" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back to Sign In
            </a>
        </div>
    </div>
    <script>
    document.querySelectorAll('.toggle-pw').forEach(btn => {
        btn.addEventListener('click', () => {
            const inp = document.getElementById(btn.dataset.target);
            inp.type = inp.type === 'password' ? 'text' : 'password';
        });
    });
    document.getElementById('rpForm').addEventListener('submit', function(e) {
        const pw = document.getElementById('password').value;
        const pc = document.getElementById('password_confirm').value;
        if (pw !== pc) { e.preventDefault(); alert('Passwords do not match'); return; }
        const btn = document.getElementById('btnRp');
        btn.classList.add('is-loading');
        btn.disabled = true;
    });
    </script>
</body>
</html>
