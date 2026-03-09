<?php
require_once 'db.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

// ─── Handle login form ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $pdo  = connect();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            // Save to session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];

            // Update last login
            $upd = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $upd->execute([$user['id']]);

            // Cookie: Remember Me
            if ($remember) {
                setcookie('remember_user', $username, time() + (30 * 24 * 3600), '/');
            }

            // Cookie: Last login time
            setcookie('last_login', date('M d, Y h:i A'), time() + (30 * 24 * 3600), '/');

            redirect('dashboard.php');

        } else {
            $error = 'Wrong username or password.';
        }
    }
}

$savedUsername = $_COOKIE['remember_user'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | PharmaCare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-box">
    <h2>💊 PharmaCare</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= clean($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-info"><?= clean($_GET['msg']) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control"
                   value="<?= clean($savedUsername) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="remember" id="remember" class="form-check-input"
                   <?= $savedUsername ? 'checked' : '' ?>>
            <label for="remember" class="form-check-label">Remember Me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <p class="text-center mt-3">
        No account? <a href="register.php">Register here</a>
    </p>
</div>

</body>
</html>
