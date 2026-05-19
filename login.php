<?php
/**
 * login.php
 * AdHub – Login page
 */

session_start();

define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // GET FORM VALUES
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // VALIDATION
    if ($email === '' || $password === '') {

        $error = 'Please enter your email and password.';

    } else {

        // DATABASE CONNECTION
        $db = getDB();

        // PREPARED STATEMENT
        $stmt = $db->prepare("
            SELECT id, name, email, password, role
            FROM users
            WHERE email = ?
            AND is_active = 1
            LIMIT 1
        ");

        $stmt->execute([$email]);

        // FETCH USER
        $user = $stmt->fetch();

        // VERIFY USER + PASSWORD
        if ($user && password_verify($password, $user['password'])) {

            // SECURITY
            session_regenerate_id(true);

            // STORE SESSION
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // REDIRECT BASED ON ROLE
            if ($user['role'] === 'admin') {

                header('Location: ' . BASE_URL . '/admin/dashboard.php');

            } else {

                header('Location: ' . BASE_URL . '/client/campaigns.php');

            }

            exit;

        } else {

            $error = 'Invalid email address or password.';

        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – AdHub</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <style>
        body { background: var(--bg-dark); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 16px; padding: 2.5rem; width: 100%; max-width: 420px; box-shadow: 0 8px 40px rgba(0,0,0,.18); }
        .login-logo { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.6rem; color: var(--primary); letter-spacing: -0.5px; margin-bottom: .25rem; }
        .login-subtitle { color: var(--text-muted); font-size: .85rem; margin-bottom: 2rem; }
        .form-label { font-size: .82rem; font-weight: 500; color: var(--text-secondary); letter-spacing: .4px; text-transform: uppercase; }
        .form-control { background: var(--input-bg); border: 1px solid var(--border); color: var(--text-primary); border-radius: 8px; padding: .65rem 1rem; font-size: .9rem; }
        .form-control:focus { background: var(--input-bg); border-color: var(--primary); color: var(--text-primary); box-shadow: 0 0 0 3px rgba(67,97,238,.18); }
        .btn-login { background: var(--primary); border: none; border-radius: 8px; padding: .7rem; font-weight: 600; letter-spacing: .3px; font-size: .92rem; color: #fff; width: 100%; transition: background .2s; }
        .btn-login:hover { background: var(--primary-dark); color: #fff; }
        .demo-box { background: rgba(67,97,238,.08); border: 1px solid rgba(67,97,238,.2); border-radius: 8px; padding: .85rem 1rem; font-size: .8rem; color: var(--text-secondary); }
        .demo-box code { color: var(--primary); font-size: .78rem; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-logo"><i class="bi bi-layers-fill me-2"></i>AdHub</div>
    <p class="login-subtitle">Agency–Client Campaign Management Platform</p>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:.85rem;border-radius:8px;">
            <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="you@company.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-login">Sign In <i class="bi bi-arrow-right ms-1"></i></button>
    </form>

    <div class="demo-box mt-4">
        <strong>Demo Credentials</strong><br>
        Admin &nbsp;→ <code>admin@adhub.com</code> / <code>Admin@1234</code><br>
        Client → <code>marcus@techcorp.com</code> / <code>Client@1234</code>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>