<?php
session_start();
require '../db.php';
require 'functions.php';

$session_voter_id = $_SESSION['admin_id'] ?? null;
 
// Log the logout action before destroying the session
if (isset($_SESSION['admin_username']) && isset($_SESSION['admin_id'])) {
    insertAuditLog($conn, $_SESSION['admin_username'], 'ADMIN_LOGOUT', [
        'status' => 'success'
    ], $_SESSION['admin_id']);
}



   // Log the vote action
    require_once '../admin/functions.php';
    insertAuditLog($conn, $session_voter_id, 'VOTE_CAST', [
        'election_name' => $details['election_name'],
        'election_id' => $election_id,
        'position_id' => $position_id
        // Note: We don't log candidate_id to maintain vote secrecy
    ], $voter_db_id);
    
session_destroy();            // Destroy the session
header("Location: admin_login.php"); // Redirect to login page
exit();

// Close database connection
$conn->close();
?>