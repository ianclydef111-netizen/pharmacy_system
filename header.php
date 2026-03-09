<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'PharmaCare' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">

        <a class="navbar-brand fw-bold" href="dashboard.php">💊 PharmaCare</a>

        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="medicines.php">Medicines</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="transactions.php">Transactions</a>
                </li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">Users</a>
                </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item">
                    <span class="nav-link text-white-50">
                        👤 <?= clean($_SESSION['full_name'] ?? '') ?>
                        (<?= clean($_SESSION['role'] ?? '') ?>)
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>

    </div>
</nav>

<div class="container mt-4">

    <?php if (isset($_COOKIE['last_login'])): ?>
    <div class="alert alert-info py-2">
        🕐 Last login: <?= clean($_COOKIE['last_login']) ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?= clean($_GET['success']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= clean($_GET['error']) ?></div>
    <?php endif; ?>
