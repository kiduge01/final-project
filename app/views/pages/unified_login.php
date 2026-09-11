<?php
/**
 * Unified Login Page for All Systems
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Reset & Tokens ── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            /* Brand */
            --gold:       #D4A017;
            --gold-light: #FACC15;
            --gold-bg:    rgba(212,160,23,.08);
            --gold-border:rgba(212,160,23,.22);
            --gold-glow:  rgba(212,160,23,.15);

            /* Surfaces */
            --bg-page:    #0B0E1A;
            --bg-brand:   #0F1225;
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
            --text-inv: #0F1225;

            /* Danger */
            --danger-bg:     rgba(239,68,68,.10);
            --danger-border: rgba(239,68,68,.25);
            --danger-text:   #fca5a5;

            /* Success */
            --success-bg:     rgba(34,197,94,.10);
            --success-border: rgba(34,197,94,.25);
            --success-text:   #86efac;

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
            grid-template-columns: 2fr 3fr;
            width: 100%; height: 100%;
        }

        /* LEFT: Brand Panel */
        .brand-panel {
            position: relative; overflow: hidden;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: calc(var(--sp)*5) calc(var(--sp)*4);
            background: var(--bg-brand);
        }

        .brand-panel::before {
            content: ''; position: absolute; pointer-events: none;
            width: 420px; height: 420px;
            top: 50%; left: 50%; transform: translate(-50%,-50%);
            background: radial-gradient(circle, rgba(212,160,23,.06) 0%, transparent 70%);
            animation: ambientPulse 8s ease-in-out infinite;
        }
        @keyframes ambientPulse {
            0%,100% { opacity: .6; transform: translate(-50%,-50%) scale(1); }
            50% { opacity: 1; transform: translate(-50%,-50%) scale(1.12); }
        }

        .cross-watermark {
            position: absolute; pointer-events: none;
            width: 240px; height: 240px;
            top: 50%; left: 50%; transform: translate(-50%,-50%);
            opacity: .08;
            font-size: 180px;
            font-weight: 700;
            color: var(--gold);
            display: flex; align-items: center; justify-content: center;
        }

        .brand-content {
            position: relative; z-index: 2;
            text-align: center;
        }

        .logo-circle {
            width: 120px; height: 120px;
            margin: 0 auto calc(var(--sp)*3);
            border-radius: 50%;
            background: var(--gold-bg);
            border: 2px solid var(--gold-border);
            display: flex; align-items: center; justify-content: center;
            font-size: 64px;
            color: var(--gold);
            box-shadow: 0 0 30px var(--gold-glow);
        }

        .logo-circle img {
            width: 100%; height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .brand-title {
            font-size: 32px; font-weight: 700;
            margin-bottom: calc(var(--sp)*1);
            color: var(--text);
        }

        .brand-subtitle {
            font-size: 14px; color: var(--text-mid);
            max-width: 320px; line-height: 1.6;
        }

        /* RIGHT: Form Panel */
        .form-panel {
            display: flex; flex-direction: column;
            padding: calc(var(--sp)*5) calc(var(--sp)*6);
            background: var(--bg-page);
            justify-content: center;
        }

        .form-container {
            max-width: 420px;
            margin: 0 auto;
            width: 100%;
        }

        .form-header {
            margin-bottom: calc(var(--sp)*5);
        }

        .form-header h1 {
            font-size: 28px; font-weight: 700;
            margin-bottom: calc(var(--sp)*1);
            color: var(--text);
        }

        .form-header p {
            font-size: 14px; color: var(--text-dim);
        }

        /* Form Group */
        .form-group {
            margin-bottom: calc(var(--sp)*4);
        }

        .form-group label {
            display: block;
            font-size: 13px; font-weight: 600;
            color: var(--text-mid);
            margin-bottom: calc(var(--sp)*1.5);
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: calc(var(--sp)*2.5) calc(var(--sp)*3);
            font-size: 14px; color: var(--text);
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            font-family: inherit;
            transition: all 200ms ease;
        }

        .form-group input:hover,
        .form-group select:hover {
            background: var(--bg-input-f);
            border-color: rgba(212,160,23,.35);
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            background: var(--bg-input-f);
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--gold-glow);
        }

        /* Password toggle wrapper */
        .pw-wrap {
            position: relative;
        }
        .pw-wrap input {
            padding-right: calc(var(--sp)*8) !important;
        }
        .pw-toggle {
            position: absolute;
            right: calc(var(--sp)*2.5);
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-mid);
            padding: 4px;
            line-height: 1;
            opacity: .6;
            transition: opacity 150ms;
        }
        .pw-toggle:hover { opacity: 1; }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='rgba(241,241,244,.65)' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right calc(var(--sp)*2.5) center;
            padding-right: calc(var(--sp)*4.5);
        }

        /* Error Alert */
        .alert {
            padding: calc(var(--sp)*2.5) calc(var(--sp)*3);
            border-radius: var(--r-md);
            margin-bottom: calc(var(--sp)*4);
            display: flex; gap: calc(var(--sp)*2);
            font-size: 13px;
            align-items: flex-start;
        }

        .alert.error {
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: var(--danger-text);
        }

        .alert.success {
            background: var(--success-bg);
            border: 1px solid var(--success-border);
            color: var(--success-text);
        }

        .alert-icon {
            flex-shrink: 0; margin-top: 2px;
        }

        /* Buttons */
        .btn {
            padding: calc(var(--sp)*2.5) calc(var(--sp)*3);
            border-radius: var(--r-md);
            font-size: 14px; font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 200ms ease;
            font-family: inherit;
            text-transform: uppercase; letter-spacing: 1px;
            display: inline-flex; align-items: center; justify-content: center;
            gap: calc(var(--sp)*1.5);
        }

        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
            color: var(--text-inv);
        }

        .btn-primary:hover:not(:disabled) {
            box-shadow: 0 8px 24px rgba(212,160,23,.30);
            transform: translateY(-2px);
        }

        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            opacity: 0.5; cursor: not-allowed;
        }

        /* Forgot Password Link */
        .form-footer {
            margin-top: calc(var(--sp)*4);
            text-align: center;
        }

        .form-footer a {
            font-size: 13px; color: var(--gold);
            text-decoration: none;
            transition: color 200ms ease;
        }

        .form-footer a:hover {
            color: var(--gold-light);
        }

        /* Role Badge */
        .role-badge {
            display: inline-block;
            padding: calc(var(--sp)*1) calc(var(--sp)*2);
            background: var(--gold-bg);
            border: 1px solid var(--gold-border);
            border-radius: calc(var(--r-sm) - 2px);
            font-size: 11px; font-weight: 600;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: calc(var(--sp)*1);
        }

        /* Loading spinner */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .btn-primary:disabled::after {
            content: '';
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(15,18,37,.3);
            border-top-color: var(--text-inv);
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .brand-panel {
                display: none;
            }

            .form-panel {
                padding: calc(var(--sp)*4) calc(var(--sp)*3);
            }

            .form-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <!-- LEFT: Brand Panel -->
        <div class="brand-panel">
            <div class="cross-watermark">+</div>
            <div class="brand-content">
                <div class="logo-circle">
                    <?php if ($logoUrl): ?>
                        <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Logo">
                    <?php else: ?>
                        <i class="fas fa-church"></i>
                    <?php endif; ?>
                </div>
                <h2 class="brand-title"><?= $churchName ?></h2>
                <p class="brand-subtitle">Unified Management System</p>
            </div>
        </div>

        <!-- RIGHT: Form Panel -->
        <div class="form-panel">
            <div class="form-container">
                <div class="form-header">
                    <h1>Sign In</h1>
                    <p>Enter your credentials to access the system</p>
                </div>

                <!-- Error/Success Alerts -->
                <?php if ($error): ?>
                    <div class="alert error">
                        <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                        <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" action="<?= $baseUrl ?>/api/v1/unified-login" id="loginForm">
                    <!-- Role Selection -->
                    <div class="form-group">
                        <label for="role">
                            <i class="fas fa-shield-alt"></i> Account Type
                        </label>
                        <select id="role" name="role" required onchange="updateRoleInfo()">
                            <option value="">-- Select your role --</option>
                            <optgroup label="Admin System">
                                <option value="admin">Administrator</option>
                                <option value="pastor">Pastor</option>
                                <option value="accountant">Accountant</option>
                                <option value="secretary">Secretary</option>
                            </optgroup>
                            <optgroup label="Department">
                                <option value="department">Department Head</option>
                            </optgroup>
                        </select>
                    </div>

                    <!-- Email/Username -->
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="your.email@church.org"
                            required
                            autocomplete="email"
                        >
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <div class="pw-wrap">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="pw-toggle" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">
                                <i class="fas fa-eye" id="password-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>

                    <!-- Forgot Password Link -->
                    <div class="form-footer">
                        <a href="<?= $baseUrl ?>/forgot-password">Forgot your password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const role = document.getElementById('role').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!role) {
                alert('Please select your account type');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;

            try {
                const response = await fetch('<?= $baseUrl ?>/api/v1/unified-login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        role,
                        email,
                        password
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Store session/token if needed
                    if (data.data?.redirect) {
                        window.location.href = data.data.redirect;
                    } else {
                        window.location.href = '<?= $baseUrl ?>/dashboard';
                    }
                } else {
                    // Show error
                    document.querySelector('.form-container').innerHTML = `
                        <div class="alert error">
                            <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                            <div>${data.message || 'Login failed'}</div>
                        </div>
                    ` + document.querySelector('.form-container').innerHTML;
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Login error:', error);
                alert('An error occurred. Please try again.');
                submitBtn.disabled = false;
            }
        });

        function updateRoleInfo() {
            const role = document.getElementById('role').value;
            // Can add role-specific hints here if needed
        }

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</body>
</html>
