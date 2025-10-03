<?php
session_start();
require '../db.php';

// Uncomment to protect with admin session
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Check if voter ID is provided via GET
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: manage_voters.php?msg=invalidid');
    exit();
}

$voter_id_param = (int)$_GET['id'];

// Initialize variables
$name = $voter_id = $dob = $email = "";
$errors = [];
$success = "";

// Fetch existing voter details
$stmt = $conn->prepare("SELECT name, voter_id, dob, email FROM voters WHERE id = ?");
$stmt->bind_param("i", $voter_id_param);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    header('Location: manage_voters.php?msg=notfound');
    exit();
}

$stmt->bind_result($name, $voter_id, $dob, $email);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_new = trim($_POST['name'] ?? '');
    $voter_id_new = trim($_POST['voter_id'] ?? '');
    $dob_new = trim($_POST['dob'] ?? '');
    $email_new = trim($_POST['email'] ?? '');
    $password_new = $_POST['password'] ?? '';
    $confirm_password_new = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($name_new)) {
        $errors[] = "Full Name is required.";
    }

    if (empty($voter_id_new)) {
        $errors[] = "Voter ID is required.";
    }

    if (empty($dob_new)) {
        $errors[] = "Date of Birth is required.";
    } elseif (!DateTime::createFromFormat('Y-m-d', $dob_new)) {
        $errors[] = "Date of Birth must be in YYYY-MM-DD format.";
    }

    if (empty($email_new)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email_new, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!empty($password_new)) {
        if (strlen($password_new) < 6) {
            $errors[] = "Password must be at least 6 characters.";
        }
        if ($password_new !== $confirm_password_new) {
            $errors[] = "Passwords do not match.";
        }
    }

    // Check uniqueness of voter_id and email excluding current voter
    if (empty($errors)) {
        // Check voter_id uniqueness
        $stmt = $conn->prepare("SELECT id FROM voters WHERE voter_id = ? AND id != ?");
        $stmt->bind_param('si', $voter_id_new, $voter_id_param);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Voter ID already exists.";
        }
        $stmt->close();

        // Check email uniqueness
        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id FROM voters WHERE email = ? AND id != ?");
            $stmt->bind_param('si', $email_new, $voter_id_param);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = "Email already exists.";
            }
            $stmt->close();
        }
    }

    // If no errors, update the voter
    if (empty($errors)) {
        if (!empty($password_new)) {
            // Hash new password
            $hashed_password = password_hash($password_new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE voters SET name = ?, voter_id = ?, dob = ?, email = ?, password = ? WHERE id = ?");
            $stmt->bind_param('sssssi', $name_new, $voter_id_new, $dob_new, $email_new, $hashed_password, $voter_id_param);
        } else {
            // Update without changing password
            $stmt = $conn->prepare("UPDATE voters SET name = ?, voter_id = ?, dob = ?, email = ? WHERE id = ?");
            $stmt->bind_param('ssssi', $name_new, $voter_id_new, $dob_new, $email_new, $voter_id_param);
        }

        if ($stmt->execute()) {
            $stmt->close();
            // Redirect with success
            header('Location: manage_voters.php?msg=updated');
            exit();
        } else {
            $errors[] = "Database error: Could not update voter.";
            $stmt->close();
        }
    }

    // Populate form fields with submitted data on error
    $name = $name_new;
    $voter_id = $voter_id_new;
    $dob = $dob_new;
    $email = $email_new;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Voter – Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
</head>
<body>
<div class="container py-4" style="max-width: 600px;">
    <h1 class="mb-4"><i class="bi bi-pencil-square me-2"></i>Edit Voter</h1>

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
            <input type="text" name="name" id="name" class="form-control" required value="<?= htmlspecialchars($name) ?>" />
        </div>

        <div class="mb-3">
            <label for="voter_id" class="form-label">Voter ID <span class="text-danger">*</span></label>
            <input type="text" name="voter_id" id="voter_id" class="form-control" required value="<?= htmlspecialchars($voter_id) ?>" />
        </div>

        <div class="mb-3">
            <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" name="dob" id="dob" class="form-control" required value="<?= htmlspecialchars($dob) ?>" />
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($email) ?>" />
        </div>

        <hr />
        <p class="text-muted">Leave password fields blank if you do not want to change the password.</p>

        <div class="mb-3">
            <label for="password" class="form-label">Password (Optional)</label>
            <input type="password" name="password" id="password" class="form-control" minlength="6" />
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" minlength="6" />
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i> Update Voter
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
