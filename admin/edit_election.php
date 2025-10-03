<?php
session_start();
require '../db.php';



$election_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($election_id <= 0) {
    die("Invalid election.");
}

// Fetch election details
$stmt = $conn->prepare("SELECT * FROM elections WHERE id = ?");
$stmt->bind_param("i", $election_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    die("Election not found.");
}
$election = $result->fetch_assoc();
$stmt->close();

$success = '';
$error = '';

// On form submit: update election
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $status = $_POST['status'];
    
    // Validate dates and fields (simple checks)
    if (empty($name) || empty($start_date) || empty($end_date) || empty($status)) {
        $error = "All fields are required.";
    } elseif ($start_date > $end_date) {
        $error = "Start date cannot be after end date.";
    } else {
        $stmt = $conn->prepare("UPDATE elections SET name = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $start_date, $end_date, $status, $election_id);
        if ($stmt->execute()) {
            $success = "Election updated successfully.";
            // Refresh $election data
            $stmt = $conn->prepare("SELECT * FROM elections WHERE id = ?");
            $stmt->bind_param("i", $election_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $election = $result->fetch_assoc();
            $stmt->close();
        } else {
            $error = "Error updating election. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Election – Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa, #c3cfe2); min-height: 100vh; }
        .edit-card { border-radius: .9rem; background: #fff; box-shadow: 0 2px 15px rgba(0,0,0,0.08); padding: 2.5rem 2rem; max-width: 650px; margin-top: 40px; }
    </style>
</head>
<body>
<div class="container d-flex justify-content-center align-items-center">
    <div class="edit-card w-100 mt-4">
        <div class="mb-3">
            <a href="manage_elections.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Elections</a>
        </div>
        <h3 class="mb-4 text-primary"><i class="bi bi-pencil"></i> Edit Election</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" class="row g-3">
            <div class="col-12">
                <label for="name" class="form-label">Election Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($election['name']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="datetime-local" class="form-control" id="start_date" name="start_date"
                    value="<?= date('Y-m-d\TH:i', strtotime($election['start_date'])) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="end_date" class="form-label">End Date</label>
                <input type="datetime-local" class="form-control" id="end_date" name="end_date"
                    value="<?= date('Y-m-d\TH:i', strtotime($election['end_date'])) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="pending" <?= $election['status']=='pending'?'selected':''; ?>>Pending</option>
                    <option value="active" <?= $election['status']=='active'?'selected':''; ?>>Active</option>
                    <option value="closed" <?= $election['status']=='closed'?'selected':''; ?>>Closed</option>
                </select>
            </div>
            <div class="col-12 mt-2 text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
