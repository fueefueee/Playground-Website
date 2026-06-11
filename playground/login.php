<?php
require_once __DIR__ . '/includes/config.php';
startSession();
$pageTitle = 'Login';

// Already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$error = '';
$redirect = $_GET['redirect'] ?? (SITE_URL . '/index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role']       = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: ' . SITE_URL . '/admin/dashboard.php');
            } else {
                header('Location: ' . htmlspecialchars($redirect));
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <span class="logo-play">PLAY</span><span class="logo-ground">GROUND</span>
        </div>
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to your account to continue shopping.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="margin-top:0.5rem;">Sign In</button>
        </form>

        <div class="divider">or</div>

        <div class="auth-switch">
            Don't have an account? <a href="<?= SITE_URL ?>/signup.php">Create one</a>
        </div>

        <div style="margin-top:1.25rem;padding:1rem;background:rgba(200,169,110,0.08);border:1px solid rgba(200,169,110,0.2);border-radius:4px;">
            <p style="font-size:0.75rem;color:var(--muted);text-align:center;letter-spacing:0.04em;">
                <strong style="color:var(--accent);">Admin demo:</strong> admin@playground.com / pass1234
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
