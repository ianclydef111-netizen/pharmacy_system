<?php
require_once 'db.php';
requireLogin();

$pdo = connect();
$id  = intval($_GET['id'] ?? 0);

if (!$id) redirect('transactions.php?error=Invalid ID.');

$stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
$stmt->execute([$id]);

redirect('transactions.php?success=Transaction deleted.');
?>
