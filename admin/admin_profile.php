<?php
session_start();
require '../db.php';

//Sample: Get logged-in admin's info with session
if (!isset($_SESSION['admin_username'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_name = $_SESSION['admin_username'];

// Fetch admin details
$stmt = $conn->prepare("SELECT name, username, email, created_at FROM admins WHERE name = ?");
$stmt->bind_param("i", $admin_name);
$stmt->execute();
$stmt->bind_result($name, $username, $email, $created_at);
$stmt->fetch();
$stmt->close();

// Optionally handle profile update or password change by processing $_POST
// Not included here - let me know if you want that too!
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile – Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
</head>
<body>
<div class="container py-4" style="max-width: 600px;">
    <h1 class="mb-4"><i class="bi bi-person-circle me-2"></i>My Profile</h1>
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($name) ?></h5>
            <p class="mb-1"><i class="bi bi-person-badge"></i> <strong>Username:</strong> <?= htmlspecialchars($username) ?></p>
            <p class="mb-1"><i class="bi bi-envelope"></i> <strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
            <p class="mb-1"><i class="bi bi-calendar"></i> <strong>Joined:</strong> <?= date('M d, Y', strtotime($created_at)) ?></p>
            <!-- Add more fields here if your table has them (e.g. phone, role) -->
            <hr>
            <a href="change_admin_pass.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-key"></i> Change Password</a>
            <!-- Uncomment below if you add profile editing -->
            <!-- <a href="edit_profile.php" class="btn btn-outline-secondary btn-sm ms-2"><i class="bi bi-pencil-square"></i> Edit Profile</a> -->
        </div>
    </div>
    <a href="admin_dashboard.php" class="btn btn-secondary mt-4"><i class="bi bi-arrow-left me-1"></i> Back to Dashboard</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
