<?php
require_once 'db.php';
requireLogin();

$title = 'Edit Transaction';
$error = '';
$pdo   = connect();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('transactions.php?error=Invalid ID.');

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
$stmt->execute([$id]);
$txn = $stmt->fetch();
if (!$txn) redirect('transactions.php?error=Transaction not found.');

$medicines = $pdo->query("SELECT * FROM medicines ORDER BY medicine_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $medicine_id   = intval($_POST['medicine_id']);
    $customer_name = trim($_POST['customer_name']);
    $qty_sold      = intval($_POST['quantity_sold']);

    if (!$medicine_id || $qty_sold <= 0) {
        $error = 'Please fill in all required fields correctly.';
    } else {
        $mStmt = $pdo->prepare("SELECT price FROM medicines WHERE id = ?");
        $mStmt->execute([$medicine_id]);
        $med   = $mStmt->fetch();
        $total = $med['price'] * $qty_sold;

        $stmt = $pdo->prepare(
            "UPDATE transactions SET medicine_id=?, customer_name=?, quantity_sold=?, total_amount=? WHERE id=?"
        );
        $stmt->execute([$medicine_id, $customer_name, $qty_sold, $total, $id]);
        redirect('transactions.php?success=Transaction updated!');
    }

    $txn['medicine_id']   = $medicine_id;
    $txn['customer_name'] = $customer_name;
    $txn['quantity_sold'] = $qty_sold;
}

include 'header.php';
?>

<h2>Edit Transaction</h2>
<a href="transactions.php" class="btn btn-secondary mb-3">← Back</a>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= clean($error) ?></div>
<?php endif; ?>

<div class="card form-box">
    <div class="card-body">
        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Medicine *</label>
                <select name="medicine_id" class="form-select" required>
                    <option value="">-- Select Medicine --</option>
                    <?php foreach ($medicines as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= $txn['medicine_id'] == $m['id'] ? 'selected' : '' ?>>
                            <?= clean($m['medicine_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Customer Name</label>
                <input type="text" name="customer_name" class="form-control"
                       value="<?= clean($txn['customer_name']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Quantity Sold *</label>
                <input type="number" name="quantity_sold" class="form-control"
                       value="<?= $txn['quantity_sold'] ?>" min="1" required>
            </div>

            <button type="submit" class="btn btn-warning">Update Transaction</button>
            <a href="transactions.php" class="btn btn-secondary">Cancel</a>

        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
