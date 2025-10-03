<?php
session_start();
require '../db.php';
require 'functions.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
// Initialize variables and errors
$name = $voter_id = $dob = $email = $password = $confirm_password = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
$voter_id = generateVoterId($conn);
    $dob = trim($_POST['dob'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($name)) {
        $errors[] = "Full Name is required.";
    }

    if (empty($voter_id)) {
        $errors[] = "Voter ID is required.";
    }

    if (empty($dob)) {
        $errors[] = "Date of Birth is required.";
    } elseif (!DateTime::createFromFormat('Y-m-d', $dob)) {
        $errors[] = "Date of Birth must be in YYYY-MM-DD format.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check uniqueness of voter_id and email
    if (empty($errors)) {
        // Check voter_id exists
        $stmt = $conn->prepare("SELECT id FROM voters WHERE voter_id = ?");
        $stmt->bind_param('s', $voter_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Voter ID already exists.";
        }
        $stmt->close();

        // Check email exists
        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id FROM voters WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = "Email already exists.";
            }
            $stmt->close();
        }
    }

    if (empty($errors)) {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new voter
        $stmt = $conn->prepare("INSERT INTO voters (name, voter_id, dob, email, password, registered_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssss", $name, $voter_id, $dob, $email, $password_hash);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: manage_voters.php?msg=added");
            exit();
        } else {
            $errors[] = "Database error: Could not add voter.";
            $stmt->close();
        }
    }
}
function generateVoterId($conn) {
    $prefix = 'VTR';
    $year = date('Y');
    
    do {
        $randomPart = strtoupper(substr(bin2hex(random_bytes(3)), 0, 3));
        $voterId = $prefix . $year . $randomPart;

        $stmt = $conn->prepare("SELECT id FROM voters WHERE voter_id = ?");
        $stmt->bind_param("s", $voterId);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
    } while ($exists);

    return $voterId;
}





?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Add New Voter – Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <!-- Bootstrap CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
</head>
<body>
<div class="container py-4" style="max-width: 600px;">
    <h1 class="mb-4"><i class="bi bi-plus-circle me-2"></i>Add New Voter</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="mb-3">
            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($name) ?>"/>
        </div>
        <div class="mb-3">
            <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" name="dob" id="dob" class="form-control" required value="<?= htmlspecialchars($dob) ?>"/>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($email) ?>"/>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" name="password" id="password" class="form-control" required/>
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required/>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i> Add Voter
        </button>
        <a href="manage_voters.php" class="btn btn-secondary ms-2">
            <i class="bi bi-arrow-left-circle me-1"></i> Cancel
        </a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
