<?php
require_once 'db.php';
requireLogin();

$title = 'Add Medicine';
$error = '';

/*Handle form */
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
        $pdo  = connect();
        $stmt = $pdo->prepare(
            "INSERT INTO medicines (medicine_name, category, quantity, price, expiry_date) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $cat, $qty, $price, $expiry]);
        redirect('medicines.php?success=Medicine added successfully!');
    }
}

include 'header.php';
?>

<h2>Add New Medicine</h2>
<a href="medicines.php" class="btn btn-secondary mb-3">← Back</a>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= clean($error) ?></div>
<?php endif; ?>

<div class="card form-box">
    <div class="card-body">
        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Medicine Name *</label>
                <input type="text" name="medicine_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Category *</label>
                <select name="category" class="form-select" required>
                    <option value="">-- Select --</option>
                    <option>Analgesic</option>
                    <option>Antibiotic</option>
                    <option>Antihypertensive</option>
                    <option>Antidiabetic</option>
                    <option>Antihistamine</option>
                    <option>Antacid</option>
                    <option>Supplement</option>
                    <option>Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Quantity *</label>
                <input type="number" name="quantity" class="form-control" min="0" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Price (₱) *</label>
                <input type="number" name="price" class="form-control" min="0" step="0.01" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Expiry Date *</label>
                <input type="date" name="expiry_date" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success">Save Medicine</button>
            <a href="medicines.php" class="btn btn-secondary">Cancel</a>

        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
