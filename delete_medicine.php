<?php
require_once 'db.php';
requireLogin();

$pdo = connect();
$id  = intval($_GET['id'] ?? 0);

if (!$id) redirect('medicines.php?error=Invalid ID.');

$stmt = $pdo->prepare("DELETE FROM medicines WHERE id = ?");
$stmt->execute([$id]);

redirect('medicines.php?success=Medicine deleted.');
?>
