<?php
require_once 'db.php';
requireLogin();

$title   = 'Transactions';
$pdo     = connect();
$search  = trim($_GET['search'] ?? '');
$page    = max(1, intval($_GET['page'] ?? 1));
$perPage = 8;
$offset  = ($page - 1) * $perPage;

if ($search) {
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) FROM transactions t
        JOIN medicines m ON t.medicine_id = m.id
        WHERE t.customer_name LIKE ? OR m.medicine_name LIKE ?
    ");
    $countStmt->execute(["%$search%", "%$search%"]);
    $total = $countStmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT t.*, m.medicine_name FROM transactions t
        JOIN medicines m ON t.medicine_id = m.id
        WHERE t.customer_name LIKE ? OR m.medicine_name LIKE ?
        ORDER BY t.transaction_date DESC LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(2, "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(3, $perPage,    PDO::PARAM_INT);
    $stmt->bindValue(4, $offset,     PDO::PARAM_INT);
    $stmt->execute();
} else {
    $total = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT t.*, m.medicine_name FROM transactions t
        JOIN medicines m ON t.medicine_id = m.id
        ORDER BY t.transaction_date DESC LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset,  PDO::PARAM_INT);
    $stmt->execute();
}

$transactions = $stmt->fetchAll();
$totalPages   = ceil($total / $perPage);

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Transactions</h2>
    <a href="add_transaction.php" class="btn btn-success">+ New Sale</a>
</div>


<form method="GET" class="mb-3 d-flex gap-2">
    <input type="text" name="search" class="form-control" style="max-width:300px;"
           placeholder="Search customer or medicine..."
           value="<?= clean($search) ?>">
    <button type="submit" class="btn btn-primary">Search</button>
    <?php if ($search): ?>
        <a href="transactions.php" class="btn btn-secondary">Clear</a>
    <?php endif; ?>
</form>

<table class="table table-bordered table-hover bg-white">
    <thead>
        <tr>
            <th>#</th>
            <th>Medicine</th>
            <th>Customer</th>
            <th>Qty Sold</th>
            <th>Total</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($transactions)): ?>
            <tr><td colspan="7" class="text-center">No transactions found.</td></tr>
        <?php else: ?>
            <?php foreach ($transactions as $i => $t): ?>
            <tr>
                <td><?= $offset + $i + 1 ?></td>
                <td><?= clean($t['medicine_name']) ?></td>
                <td><?= clean($t['customer_name'] ?: '—') ?></td>
                <td><?= $t['quantity_sold'] ?></td>
                <td>₱<?= number_format($t['total_amount'], 2) ?></td>
                <td><?= date('M j, Y', strtotime($t['transaction_date'])) ?></td>
                <td>
                    <a href="edit_transaction.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="delete_transaction.php?id=<?= $t['id'] ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this transaction?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($totalPages > 1): ?>
<nav>
    <ul class="pagination">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <li class="page-item <?= $p == $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php include 'footer.php'; ?>
