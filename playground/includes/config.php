<?php
// =============================================================
// PLAYGROUND - Configuration File
// =============================================================
// update these values before deploy

// --- Database Configuration ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'playground_db');
define('DB_USER', 'root');       
define('DB_PASS', '');           
define('DB_CHARSET', 'utf8mb4');

// --- PayPal Configuration ---
define('PAYPAL_MODE', 'sandbox'); // 'sandbox' for testing, 'live' for production
define('PAYPAL_CLIENT_ID', 'AUY0MWhXmLMHH40oI3nLL7FuFq0hOqymPbvNJ6ELMiFjypawa74XNN4cxNSV7-Gvl5SlQUbQFHLMphYq');
define('PAYPAL_CLIENT_SECRET', 'ECdKdcPmE4BvP34a8D4H49mZjRfsmpVPNoJ3_Qvyg2smWuwFC3_ZiLXLzb4oQRppejiBsJ_dkrjeM3fu');

// PayPal API URLs
if (PAYPAL_MODE === 'live') {
    define('PAYPAL_API_URL', 'https://api-m.paypal.com');
} else {
    define('PAYPAL_API_URL', 'https://api-m.sandbox.paypal.com');
}

// --- Site Configuration ---
define('SITE_NAME', 'Playground');
define('SITE_URL', 'http://localhost/playground'); // Change to your domain
define('CURRENCY', 'MYR');
define('CURRENCY_SYMBOL', 'RM');

// --- Session Configuration ---
define('SESSION_LIFETIME', 3600); // 1 hour

// =============================================================
// Database Connection (PDO)
// =============================================================
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// =============================================================
// Session & Auth Helpers
// =============================================================
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    startSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

function getCurrentUser() {
    startSession();
    if (!isLoggedIn()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['role'],
    ];
}
