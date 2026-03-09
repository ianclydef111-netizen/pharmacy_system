<?php
require_once 'db.php';
requireLogin();

$title = 'Dashboard';
$pdo   = connect();

/* Summary counts */
$totalMedicines = $pdo->query("SELECT COUNT(*) FROM medicines")->fetchColumn();
$totalStock     = $pdo->query("SELECT SUM(quantity) FROM medicines")->fetchColumn() ?? 0;
$lowStock       = $pdo->query("SELECT COUNT(*) FROM medicines WHERE quantity > 0 AND quantity <= 10")->fetchColumn();
$outOfStock     = $pdo->query("SELECT COUNT(*) FROM medicines WHERE quantity = 0")->fetchColumn();
$totalSales     = $pdo->query("SELECT SUM(total_amount) FROM transactions")->fetchColumn() ?? 0;
$totalTxn       = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();

/* Recent transactions */
$recent = $pdo->query("
    SELECT t.*, m.medicine_name
    FROM transactions t
    JOIN medicines m ON t.medicine_id = m.id
    ORDER BY t.transaction_date DESC
    LIMIT 5
")->fetchAll();

include 'header.php';
?>

<h2>Dashboard</h2>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center p-3">
            <h5>Total Medicines</h5>
            <h2 class="text-primary"><?= $totalMedicines ?></h2>
            <small class="text-muted"><?= number_format($totalStock) ?> units in stock</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3">
            <h5>Low Stock</h5>
            <h2 class="text-warning"><?= $lowStock ?></h2>
            <small class="text-muted">10 or fewer units left</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3">
            <h5>Out of Stock</h5>
            <h2 class="text-danger"><?= $outOfStock ?></h2>
            <small class="text-muted">Zero quantity</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3">
            <h5>Total Sales</h5>
            <h2 class="text-success">₱<?= number_format($totalSales, 2) ?></h2>
            <small class="text-muted"><?= $totalTxn ?> transactions</small>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><strong>Recent Transactions</strong></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Customer</th>
                    <th>Qty Sold</th>
                    <th>Total</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent)): ?>
                    <tr><td colspan="5" class="text-center py-3">No transactions yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($recent as $row): ?>
                    <tr>
                        <td><?= clean($row['medicine_name']) ?></td>
                        <td><?= clean($row['customer_name'] ?: '—') ?></td>
                        <td><?= $row['quantity_sold'] ?></td>
                        <td>₱<?= number_format($row['total_amount'], 2) ?></td>
                        <td><?= date('M j, Y', strtotime($row['transaction_date'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
