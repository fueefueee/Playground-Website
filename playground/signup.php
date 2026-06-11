<?php
require_once __DIR__ . '/includes/config.php';
startSession();
$pageTitle = 'Create Account';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!$name || !$email || !$password || !$confirm) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
            $stmt->execute([$name, $email, $hash]);
            $userId = $db->lastInsertId();

            $_SESSION['user_id']    = $userId;
            $_SESSION['user_name']  = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['role']       = 'customer';

            header('Location: ' . SITE_URL . '/index.php');
            exit;
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
        <h2>Create Account</h2>
        <p class="subtitle">Join Playground and start shopping.</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Your name"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Min. 6 characters" required>
            </div>
            <div class="form-group">
                <label for="confirm">Confirm Password</label>
                <input type="password" id="confirm" name="confirm" placeholder="Repeat password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="margin-top:0.5rem;">Create Account</button>
        </form>

        <div class="divider">or</div>

        <div class="auth-switch">
            Already have an account? <a href="<?= SITE_URL ?>/login.php">Sign in</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
