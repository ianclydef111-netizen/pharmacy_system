<?php
require_once 'db.php';
requireLogin();

$title     = 'New Sale';
$error     = '';
$pdo       = connect();
$medicines = $pdo->query("SELECT * FROM medicines WHERE quantity > 0 ORDER BY medicine_name")->fetchAll();

/*Handle form FOR TRANSACTIONS*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $medicine_id   = intval($_POST['medicine_id']);
    $customer_name = trim($_POST['customer_name']);
    $qty_sold      = intval($_POST['quantity_sold']);

    if (!$medicine_id) {
        $error = 'Please select a medicine.';
    } elseif ($qty_sold <= 0) {
        $error = 'Quantity must be at least 1.';
    } else {
        $mStmt = $pdo->prepare("SELECT * FROM medicines WHERE id = ?");
        $mStmt->execute([$medicine_id]);
        $med = $mStmt->fetch();

        if ($qty_sold > $med['quantity']) {
            $error = "Not enough stock. Only {$med['quantity']} available.";
        } else {
            $total = $med['price'] * $qty_sold;

            /* Insert transaction */
            $ins = $pdo->prepare(
                "INSERT INTO transactions (medicine_id, customer_name, quantity_sold, total_amount) VALUES (?, ?, ?, ?)"
            );
            $ins->execute([$medicine_id, $customer_name, $qty_sold, $total]);

            /* Update stock */
            $upd = $pdo->prepare("UPDATE medicines SET quantity = quantity - ? WHERE id = ?");
            $upd->execute([$qty_sold, $medicine_id]);

            redirect('transactions.php?success=Sale recorded! Total: ₱' . number_format($total, 2));
        }
    }
}

include 'header.php';
?>

<h2>New Sale</h2>
<a href="transactions.php" class="btn btn-secondary mb-3">← Back</a>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= clean($error) ?></div>
<?php endif; ?>

<div class="card form-box">
    <div class="card-body">
        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Medicine *</label>
                <select name="medicine_id" id="medSelect" class="form-select" required onchange="showPrice()">
                    <option value="">-- Select Medicine --</option>
                    <?php foreach ($medicines as $m): ?>
                        <option value="<?= $m['id'] ?>"
                                data-price="<?= $m['price'] ?>"
                                data-stock="<?= $m['quantity'] ?>">
                            <?= clean($m['medicine_name']) ?> (<?= $m['quantity'] ?> left)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small id="priceInfo" class="text-muted"></small>
            </div>

            <div class="mb-3">
                <label class="form-label">Customer Name</label>
                <input type="text" name="customer_name" class="form-control" placeholder="Optional">
            </div>

            <div class="mb-3">
                <label class="form-label">Quantity to Sell *</label>
                <input type="number" name="quantity_sold" id="qtyInput"
                       class="form-control" min="1" required oninput="calcTotal()">
            </div>

            <div class="mb-3">
                <strong>Total: <span id="totalDisplay" class="text-success">₱0.00</span></strong>
            </div>

            <button type="submit" class="btn btn-success">Record Sale</button>
            <a href="transactions.php" class="btn btn-secondary">Cancel</a>

        </form>
    </div>
</div>

<script>
function showPrice() {
    const sel   = document.getElementById('medSelect');
    const opt   = sel.options[sel.selectedIndex];
    const price = opt.getAttribute('data-price') || 0;
    document.getElementById('priceInfo').textContent = price ? 'Price: ₱' + parseFloat(price).toFixed(2) + ' each' : '';
    calcTotal();
}

function calcTotal() {
    const sel   = document.getElementById('medSelect');
    const opt   = sel.options[sel.selectedIndex];
    const price = parseFloat(opt.getAttribute('data-price') || 0);
    const qty   = parseInt(document.getElementById('qtyInput').value || 0);
    document.getElementById('totalDisplay').textContent = '₱' + (price * qty).toFixed(2);
}
</script>

<?php include 'footer.php'; ?>
