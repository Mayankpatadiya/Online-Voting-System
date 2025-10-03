<?php
session_start();
require '../db.php';
require 'functions.php';

// (Optional, but recommended) Admin access check
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];

    // Basic validation
    if (empty($name) || empty($start_date) || empty($end_date) || empty($status)) {
        $error = "All fields are required.";
    } elseif ($start_date > $end_date) {
        $error = "Start date cannot be after End date.";
    } else {
        // Check for same election name with overlapping dates
$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM elections
     WHERE name = ?
     AND (
         (start_date <= ? AND end_date >= ?) -- Overlaps with new start
         OR (start_date <= ? AND end_date >= ?) -- Overlaps with new end
         OR (? <= start_date AND ? >= end_date) -- New election fully covers an existing one
     )"
);
$stmt->bind_param(
    "sssssss",
    $name,
    $start_date, $start_date,
    $end_date, $end_date,
    $start_date, $end_date
);
$stmt->execute();
$stmt->bind_result($duplicate_count);
$stmt->fetch();
$stmt->close();

if ($duplicate_count > 0) {
    $error = "An election with the same name and overlapping dates already exists.";
}else {
    // Duplicate validation logic goes here (see above)
    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM elections
         WHERE name = ?
         AND (
             (start_date <= ? AND end_date >= ?)
             OR (start_date <= ? AND end_date >= ?)
             OR (? <= start_date AND ? >= end_date)
         )"
    );
    $stmt->bind_param(
        "sssssss",
        $name,
        $start_date, $start_date,
        $end_date, $end_date,
        $start_date, $end_date
    );
    $stmt->execute();
    $stmt->bind_result($duplicate_count);
    $stmt->fetch();
    $stmt->close();

    if ($duplicate_count > 0) {
        $error = "An election with the same name and overlapping dates already exists.";
    } else {
        $stmt = $conn->prepare("INSERT INTO elections (name, start_date, end_date, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $start_date, $end_date, $status);
        if ($stmt->execute()) {
            $election_id = $stmt->insert_id;
            
            // Log the creation
            insertAuditLog($conn, $_SESSION['admin_username'], 'CREATE_ELECTION', [
                'election_id' => $election_id,
                'name' => $name,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => $status
            ], $_SESSION['admin_id']);

            $success = "Election created successfully!";
            $name = $start_date = $end_date = $status = '';
        } else {
            $error = "Failed to create election. Database error.";
        }
        $stmt->close();
    }
}
    }
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Election – Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa, #c3cfe2); min-height: 100vh; }
        .create-card { border-radius: .9rem; background: #fff; box-shadow: 0 2px 15px rgba(0,0,0,0.08); padding: 2.2rem 2rem; max-width: 650px; margin-top: 40px; }
    </style>
</head>
<body>
    <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="container d-flex justify-content-center align-items-center">
    <div class="create-card w-100 mt-4">
        <div class="mb-3">
            <a href="manage_elections.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Elections</a>
            
        </div>
        <h3 class="mb-4 text-primary"><i class="bi bi-plus-circle"></i> Create New Election</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" class="row g-3">
            <div class="col-12">
                <label for="name" class="form-label">Election Name</label>
                <input type="text" class="form-control" id="name" name="name" required value="<?= isset($name) ? htmlspecialchars($name) : '' ?>">
            </div>
            <div class="col-md-6">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="datetime-local" class="form-control" id="start_date" name="start_date" required value="<?= isset($start_date) ? htmlspecialchars($start_date) : '' ?>">
            </div>
            <div class="col-md-6">
                <label for="end_date" class="form-label">End Date</label>
                <input type="datetime-local" class="form-control" id="end_date" name="end_date" required value="<?= isset($end_date) ? htmlspecialchars($end_date) : '' ?>">
            </div>
            <div class="col-md-6">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="">Select status</option>
                    <option value="pending" <?= (isset($status) && $status=='pending' ? "selected" : "") ?>>Pending</option>
                    <option value="active" <?= (isset($status) && $status=='active' ? "selected" : "") ?>>Active</option>
                    <option value="closed" <?= (isset($status) && $status=='closed' ? "selected" : "") ?>>Closed</option>
                </select>
            </div>
            <div class="col-12 mt-2 text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Create Election
                </button>
                
            </div>
        </form>
        
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
