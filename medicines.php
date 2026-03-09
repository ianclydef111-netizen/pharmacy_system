<?php
require_once 'db.php';
requireLogin();

$title   = 'Medicines';
$pdo     = connect();
$search  = trim($_GET['search'] ?? '');
$page    = max(1, intval($_GET['page'] ?? 1));
$perPage = 8;
$offset  = ($page - 1) * $perPage;

// ─── Count and fetch with optional search ────────────────────────
if ($search) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM medicines WHERE medicine_name LIKE ? OR category LIKE ?");
    $countStmt->execute(["%$search%", "%$search%"]);
    $total = $countStmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT * FROM medicines WHERE medicine_name LIKE ? OR category LIKE ? ORDER BY medicine_name LIMIT ? OFFSET ?");
    $stmt->bindValue(1, "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(2, "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(3, $perPage,    PDO::PARAM_INT);
    $stmt->bindValue(4, $offset,     PDO::PARAM_INT);
    $stmt->execute();
} else {
    $total = $pdo->query("SELECT COUNT(*) FROM medicines")->fetchColumn();

    $stmt = $pdo->prepare("SELECT * FROM medicines ORDER BY medicine_name LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset,  PDO::PARAM_INT);
    $stmt->execute();
}

$medicines  = $stmt->fetchAll();
$totalPages = ceil($total / $perPage);

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Medicines</h2>
    <a href="add_medicine.php" class="btn btn-success">+ Add Medicine</a>
</div>

<!-- ─── Search ───────────────────────────────────────────────────── -->
<form method="GET" class="mb-3 d-flex gap-2">
    <input type="text" name="search" class="form-control" style="max-width:300px;"
           placeholder="Search by name or category..."
           value="<?= clean($search) ?>">
    <button type="submit" class="btn btn-primary">Search</button>
    <?php if ($search): ?>
        <a href="medicines.php" class="btn btn-secondary">Clear</a>
    <?php endif; ?>
</form>

<!-- ─── Table ────────────────────────────────────────────────────── -->
<table class="table table-bordered table-hover bg-white">
    <thead>
        <tr>
            <th>#</th>
            <th>Medicine Name</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Expiry Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($medicines)): ?>
            <tr><td colspan="8" class="text-center">No medicines found.</td></tr>
        <?php else: ?>
            <?php foreach ($medicines as $i => $m): ?>
            <tr>
                <td><?= $offset + $i + 1 ?></td>
                <td><?= clean($m['medicine_name']) ?></td>
                <td><?= clean($m['category']) ?></td>
                <td><?= $m['quantity'] ?></td>
                <td>₱<?= number_format($m['price'], 2) ?></td>
                <td><?= date('M j, Y', strtotime($m['expiry_date'])) ?></td>
                <td>
                    <?php if ($m['quantity'] == 0): ?>
                        <span class="badge badge-out">Out of Stock</span>
                    <?php elseif ($m['quantity'] <= 10): ?>
                        <span class="badge badge-low">Low Stock</span>
                    <?php else: ?>
                        <span class="badge badge-ok">In Stock</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit_medicine.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="delete_medicine.php?id=<?= $m['id'] ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this medicine?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- ─── Pagination ───────────────────────────────────────────────── -->
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
