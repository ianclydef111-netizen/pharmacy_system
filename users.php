<?php
require_once 'db.php';
requireAdmin();

$title = 'User Management';
$pdo   = connect();
$error = '';

if (isset($_POST['action']) && $_POST['action'] === 'add') {

    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $role      = $_POST['role'];

    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } else {
        $chk = $pdo->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $chk->execute([$username, $email]);

        if ($chk->rowCount() > 0) {
            $error = 'Username or email already taken.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $pdo->prepare(
                "INSERT INTO users (full_name, username, email, password, role) VALUES (?,?,?,?,?)"
            );
            $stmt->execute([$full_name, $username, $email, $hashed, $role]);
            redirect('users.php?success=User created!');
        }
    }
}


if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);

    if ($del_id === $_SESSION['user_id']) {
        redirect('users.php?error=You cannot delete your own account.');
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$del_id]);
    redirect('users.php?success=User deleted.');
}

$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE full_name LIKE ? OR username LIKE ? ORDER BY full_name");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY full_name");
}
$users = $stmt->fetchAll();

include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>User Management</h2>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">+ Add User</button>
</div>


<form method="GET" class="mb-3 d-flex gap-2">
    <input type="text" name="search" class="form-control" style="max-width:300px;"
           placeholder="Search users..." value="<?= clean($search) ?>">
    <button type="submit" class="btn btn-primary">Search</button>
    <?php if ($search): ?>
        <a href="users.php" class="btn btn-secondary">Clear</a>
    <?php endif; ?>
</form>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= clean($error) ?></div>
<?php endif; ?>

<table class="table table-bordered table-hover bg-white">
    <thead>
        <tr>
            <th>#</th>
            <th>Full Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Last Login</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $i => $u): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= clean($u['full_name']) ?></td>
            <td><?= clean($u['username']) ?></td>
            <td><?= clean($u['email']) ?></td>
            <td>
                <span class="badge <?= $u['role'] === 'admin' ? 'bg-primary' : 'bg-secondary' ?>">
                    <?= $u['role'] ?>
                </span>
            </td>
            <td><?= $u['last_login'] ? date('M j, Y', strtotime($u['last_login'])) : 'Never' ?></td>
            <td>
                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                    <a href="users.php?delete=<?= $u['id'] ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete <?= clean($u['username']) ?>?')">Delete</a>
                <?php else: ?>
                    <span class="text-muted">(You)</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="pharmacist">Pharmacist</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Create User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
