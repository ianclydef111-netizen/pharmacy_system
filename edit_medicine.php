<?php
require_once 'db.php';
requireLogin();

$title = 'Edit Medicine';
$error = '';
$pdo   = connect();

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect('medicines.php?error=Invalid ID.');

$stmt = $pdo->prepare("SELECT * FROM medicines WHERE id = ?");
$stmt->execute([$id]);
$med = $stmt->fetch();
if (!$med) redirect('medicines.php?error=Medicine not found.');

/* Handle form */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name   = trim($_POST['medicine_name']);
    $cat    = trim($_POST['category']);
    $qty    = intval($_POST['quantity']);
    $price  = floatval($_POST['price']);
    $expiry = trim($_POST['expiry_date']);

    if (empty($name) || empty($cat) || empty($expiry)) {
        $error = 'Name, category, and expiry date are required.';
    } elseif ($qty < 0) {
        $error = 'Quantity cannot be negative.';
    } elseif ($price < 0) {
        $error = 'Price cannot be negative.';
    } else {
        $stmt = $pdo->prepare(
            "UPDATE medicines SET medicine_name=?, category=?, quantity=?, price=?, expiry_date=? WHERE id=?"
        );
        $stmt->execute([$name, $cat, $qty, $price, $expiry, $id]);
        redirect('medicines.php?success=Medicine updated!');
    }

    $med['medicine_name'] = $name;
    $med['category']      = $cat;
    $med['quantity']      = $qty;
    $med['price']         = $price;
    $med['expiry_date']   = $expiry;
}

include 'header.php';
?>

<h2>Edit Medicine</h2>
<a href="medicines.php" class="btn btn-secondary mb-3">← Back</a>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= clean($error) ?></div>
<?php endif; ?>

<div class="card form-box">
    <div class="card-body">
        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Medicine Name *</label>
                <input type="text" name="medicine_name" class="form-control"
                       value="<?= clean($med['medicine_name']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Category *</label>
                <select name="category" class="form-select" required>
                    <option value="">-- Select --</option>
                    <?php
                    $cats = ['Analgesic','Antibiotic','Antihypertensive','Antidiabetic','Antihistamine','Antacid','Supplement','Other'];
                    foreach ($cats as $c): ?>
                        <option value="<?= $c ?>" <?= $med['category'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Quantity *</label>
                <input type="number" name="quantity" class="form-control"
                       value="<?= $med['quantity'] ?>" min="0" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Price (₱) *</label>
                <input type="number" name="price" class="form-control"
                       value="<?= $med['price'] ?>" min="0" step="0.01" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Expiry Date *</label>
                <input type="date" name="expiry_date" class="form-control"
                       value="<?= clean($med['expiry_date']) ?>" required>
            </div>

            <button type="submit" class="btn btn-warning">Update Medicine</button>
            <a href="medicines.php" class="btn btn-secondary">Cancel</a>

        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
