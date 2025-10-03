<?php
session_start();
require '../db.php';

// Check login
if (!isset($_SESSION['admin_username'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($current) || empty($new) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (strlen($new) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        // Fetch current password (plain text)
        $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $stmt->bind_result($db_password);
        $found = $stmt->fetch();
        $stmt->close();

        if (!$found) {
            session_destroy();
            header('Location: admin_login.php');
            exit();
        }

        // Simple string comparison with DB password
        if ($current !== $db_password) {
            $error = 'Current password is incorrect.';
        } else {
            // Update to new password (plain text)
            $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new, $admin_id);
            if ($stmt->execute()) {
                $success = 'Password changed successfully.';
            } else {
                $error = 'Could not update password. Try again.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password – Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
</head>
<body>
<div class="container py-4" style="max-width: 500px;">
    <h1 class="mb-4"><i class="bi bi-key me-2"></i>Change Password</h1>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="card shadow-sm p-4">
        <div class="mb-3">
            <label for="current_password" class="form-label">Current Password</label>
            <input type="password" name="current_password" id="current_password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="new_password" class="form-label">New Password</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6">
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Change Password</button>
        <a href="admin_profile.php" class="btn btn-outline-secondary ms-2"><i class="bi bi-arrow-left"></i> Cancel</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
