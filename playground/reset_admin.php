<?php
// ============================================================
// Playground — Admin Password Reset
// ============================================================
// INSTRUCTIONS:
// 1. Place this file in your /playground/ folder
// 2. Visit http://localhost/playground/reset_admin.php
// 3. Set your new password
// 4. DELETE this file immediately after use!
// ============================================================

require_once __DIR__ . '/includes/config.php';

$message = '';
$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = trim($_POST['email'] ?? '');
    $newPass    = $_POST['new_password'] ?? '';
    $confirmPass= $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($newPass) || empty($confirmPass)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($newPass) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($newPass !== $confirmPass) {
        $error = 'Passwords do not match. Please try again.';
    } else {
        try {
            $db   = getDB();
            $hash = password_hash($newPass, PASSWORD_DEFAULT);

            // Check if user exists
            $stmt = $db->prepare("SELECT id, name, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                if ($user['role'] !== 'admin') {
                    $error = 'This account does not have admin privileges.';
                } else {
                    $db->prepare("UPDATE users SET password = ? WHERE email = ?")
                       ->execute([$hash, $email]);
                    $message = 'Password successfully updated for <strong>' . htmlspecialchars($user['name']) . '</strong> (' . htmlspecialchars($email) . ').';
                    $success = true;
                }
            } else {
                $error = 'No account found with that email address.';
            }

        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Password — Playground</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #0a0a0a;
            color: #f0ece4;
            font-family: 'DM Sans', sans-serif;
            font-weight: 300;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .card {
            background: #111111;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 2.5rem;
            width: 100%;
            max-width: 440px;
        }

        /* Logo */
        .logo {
            font-family: 'Playfair Display', serif;
            font-weight: 900;
            font-size: 1.3rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 2rem;
            text-align: center;
        }
        .logo .play   { color: #ffffff; }
        .logo .ground { color: #c8a96e; }

        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.4rem;
        }

        .subtitle {
            font-size: 0.88rem;
            color: #666666;
            margin-bottom: 1.75rem;
            line-height: 1.6;
        }

        /* Alerts */
        .alert {
            padding: 0.9rem 1.1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            border-left: 3px solid;
            line-height: 1.6;
        }
        .alert-warning {
            background: rgba(200,169,110,0.08);
            border-color: #c8a96e;
            color: #c8a96e;
        }
        .alert-error {
            background: rgba(224,82,82,0.08);
            border-color: #e05252;
            color: #e05252;
        }
        .alert-success {
            background: rgba(82,201,122,0.08);
            border-color: #52c97a;
            color: #52c97a;
        }

        /* Form */
        .form-group {
            margin-bottom: 1.1rem;
        }

        label {
            display: block;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #888888;
            margin-bottom: 0.45rem;
        }

        input {
            width: 100%;
            background: #1c1c1c;
            border: 1px solid #2a2a2a;
            border-radius: 4px;
            padding: 0.78rem 1rem;
            color: #f0ece4;
            font-size: 0.95rem;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s ease;
        }
        input:focus { border-color: #c8a96e; }
        input::placeholder { color: #444444; }

        /* Password strength indicator */
        .strength-bar {
            height: 3px;
            border-radius: 2px;
            background: #2a2a2a;
            margin-top: 0.4rem;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            border-radius: 2px;
            width: 0%;
            transition: width 0.3s ease, background 0.3s ease;
        }
        .strength-label {
            font-size: 0.7rem;
            color: #666;
            margin-top: 0.25rem;
        }

        /* Show/hide password toggle */
        .input-wrap { position: relative; }
        .toggle-pw {
            position: absolute;
            right: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
            padding: 0;
            transition: color 0.2s;
        }
        .toggle-pw:hover { color: #c8a96e; }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 0.85rem;
            background: #c8a96e;
            color: #0a0a0a;
            border: none;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            font-family: inherit;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 0.75rem;
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .btn-submit:hover  { background: #f0ece4; }
        .btn-submit:active { transform: scale(0.99); }
        .btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Links */
        .links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .links a {
            font-size: 0.82rem;
            color: #c8a96e;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .links a:hover { opacity: 0.75; text-decoration: underline; }

        /* Security note */
        .security-note {
            margin-top: 1.75rem;
            padding: 0.85rem 1rem;
            background: rgba(255,255,255,0.03);
            border: 1px solid #2a2a2a;
            border-radius: 6px;
            font-size: 0.74rem;
            color: #555555;
            text-align: center;
            line-height: 1.7;
        }
        .security-note code {
            background: #1c1c1c;
            padding: 0.1rem 0.35rem;
            border-radius: 3px;
            font-family: monospace;
            color: #888;
        }

        /* Success state */
        .success-icon {
            font-size: 3rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        .success-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        .btn-primary {
            display: block;
            text-align: center;
            padding: 0.85rem;
            background: #c8a96e;
            color: #0a0a0a;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn-primary:hover { background: #f0ece4; }
        .btn-outline {
            display: block;
            text-align: center;
            padding: 0.85rem;
            background: transparent;
            color: #f0ece4;
            border: 1px solid #2a2a2a;
            border-radius: 4px;
            font-size: 0.85rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            text-decoration: none;
            transition: border-color 0.2s, color 0.2s;
        }
        .btn-outline:hover { border-color: #c8a96e; color: #c8a96e; }

        .divider {
            height: 1px;
            background: #2a2a2a;
            margin: 1.5rem 0;
        }
    </style>
</head>
<body>
<div class="card">

    <div class="logo">
        <span class="play">PLAY</span><span class="ground">GROUND</span>
    </div>

    <?php if ($success): ?>
    <!-- ── Success state ── -->
    <div class="success-icon">✅</div>
    <h2 style="text-align:center;">Password Updated</h2>
    <p class="subtitle" style="text-align:center;">Your admin password has been changed successfully. You can now log in with your new credentials.</p>

    <div class="alert alert-success"><?= $message ?></div>

    <div class="success-actions">
        <a href="<?= SITE_URL ?>/login.php" class="btn-primary">Go to Login</a>
        <a href="<?= SITE_URL ?>/index.php" class="btn-outline">Back to Store</a>
    </div>

    <div class="divider"></div>

    <div class="security-note">
        🔒 <strong>Important:</strong> Delete <code>reset_admin.php</code> from your server now to prevent unauthorised access.
    </div>

    <?php else: ?>
    <!-- ── Form state ── -->
    <h2>Reset Admin Password</h2>
    <p class="subtitle">Enter your admin email and choose a new password below.</p>

    <div class="alert alert-warning">
        ⚠ <strong>Security notice:</strong> Delete this file immediately after resetting your password.
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error">✕ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="email">Admin Email</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="admin@playground.com"
                value="<?= htmlspecialchars($_POST['email'] ?? 'admin@playground.com') ?>"
                required
                autocomplete="email">
        </div>

        <div class="form-group">
            <label for="new_password">New Password</label>
            <div class="input-wrap">
                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    placeholder="Min. 6 characters"
                    required
                    autocomplete="new-password"
                    oninput="checkStrength(this.value)">
                <button type="button" class="toggle-pw" onclick="toggleVisibility('new_password', this)">Show</button>
            </div>
            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
            <div class="strength-label" id="strengthLabel"></div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <div class="input-wrap">
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Repeat your password"
                    required
                    autocomplete="new-password">
                <button type="button" class="toggle-pw" onclick="toggleVisibility('confirm_password', this)">Show</button>
            </div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">Reset Password</button>
    </form>

    <div class="links">
        <a href="<?= SITE_URL ?>/login.php">← Back to Login</a>
        <a href="<?= SITE_URL ?>/index.php">View Store</a>
    </div>

    <div class="security-note">
        📁 File path: <code>/playground/reset_admin.php</code><br>
        Delete this file from your server after use.
    </div>

    <?php endif; ?>
</div>

<script>
// Toggle password visibility
function toggleVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Hide';
    } else {
        input.type = 'password';
        btn.textContent = 'Show';
    }
}

// Password strength checker
function checkStrength(password) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');

    if (!password) {
        fill.style.width = '0%';
        label.textContent = '';
        return;
    }

    let score = 0;
    if (password.length >= 6)  score++;
    if (password.length >= 10) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    const levels = [
        { width: '20%', color: '#e05252', text: 'Very weak'  },
        { width: '40%', color: '#e07852', text: 'Weak'       },
        { width: '60%', color: '#e0c052', text: 'Fair'       },
        { width: '80%', color: '#a0c952', text: 'Strong'     },
        { width: '100%',color: '#52c97a', text: 'Very strong'},
    ];

    const level = levels[Math.min(score - 1, 4)];
    fill.style.width      = level.width;
    fill.style.background = level.color;
    label.textContent     = level.text;
    label.style.color     = level.color;
}

// Validate before submit
function validateForm() {
    const newPw  = document.getElementById('new_password').value;
    const confPw = document.getElementById('confirm_password').value;
    const btn    = document.getElementById('submitBtn');

    if (newPw.length < 6) {
        alert('Password must be at least 6 characters.');
        return false;
    }
    if (newPw !== confPw) {
        alert('Passwords do not match.');
        return false;
    }

    btn.disabled     = true;
    btn.textContent  = 'Resetting...';
    return true;
}
</script>

</body>
</html>