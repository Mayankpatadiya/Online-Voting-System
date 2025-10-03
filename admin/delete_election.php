<?php
session_start();
require '../db.php';
require 'functions.php';

// 1. Admin access check: make sure only admins can delete
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
// 2. Get and validate election ID from URL
$election_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($election_id <= 0) {
    die("Invalid election ID.");
}

// 3. Optionally, check if the election exists
$stmt = $conn->prepare("SELECT name FROM elections WHERE id = ?");
$stmt->bind_param("i", $election_id);
$stmt->execute();
$stmt->bind_result($election_name);
if (!$stmt->fetch()) {
    $stmt->close();
    die("Election not found.");
}
$stmt->close();

// 4. Delete related candidates first (due to foreign keys, avoid orphan records)
$del_candidates_stmt = $conn->prepare("DELETE FROM candidates WHERE election_id = ?");
$del_candidates_stmt->bind_param("i", $election_id);
$del_candidates_stmt->execute();
$del_candidates_stmt->close();

// 5. Delete the election
$del_election_stmt = $conn->prepare("DELETE FROM elections WHERE id = ?");
$del_election_stmt->bind_param("i", $election_id);
if ($del_election_stmt->execute()) {
    // Log the deletion
    insertAuditLog($conn, $_SESSION['admin_username'], 'DELETE_ELECTION', [
        'election_id' => $election_id,
        'election_name' => $election_name
    ], $_SESSION['admin_id']);
    
    $del_election_stmt->close();
    // Redirect to elections list or show success message
    header("Location: manage_elections.php?deleted=1");
    exit;
} else {
    $error = "Failed to delete the election. Please try again.";
    $del_election_stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Delete Election</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <a href="manage_elections.php" class="btn btn-secondary">Back to Elections</a>
    <?php endif; ?>
</div>
</body>
</html>
