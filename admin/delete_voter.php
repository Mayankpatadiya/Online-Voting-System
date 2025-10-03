<?php
session_start();

require '../db.php';

// Uncomment below if you want to protect this page by admin login session
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Check if 'id' is passed via GET and is a positive integer
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $voterId = (int)$_GET['id'];

    // Optional: Verify voter exists before deleting (good practice)
    $stmt = $conn->prepare("SELECT id FROM voters WHERE id = ?");
    $stmt->bind_param("i", $voterId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Voter exists, proceed with deletion
        $stmt->close();

        $deleteStmt = $conn->prepare("DELETE FROM voters WHERE id = ?");
        $deleteStmt->bind_param("i", $voterId);

        if ($deleteStmt->execute()) {
            $deleteStmt->close();
            $conn->close();
            // Redirect with success message
            header("Location: manage_voters.php?msg=deleted");
            exit();
        } else {
            $deleteStmt->close();
            $conn->close();
            // Redirect with error message
            header("Location: manage_voters.php?msg=error");
            exit();
        }
    } else {
        $stmt->close();
        $conn->close();
        // Voter not found
        header("Location: manage_voters.php?msg=notfound");
        exit();
    }
} else {
    // Invalid or no ID provided
    $conn->close();
    header("Location: manage_voters.php?msg=invalidid");
    exit();
}
