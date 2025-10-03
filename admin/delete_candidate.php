
<?php
session_start();
require '../db.php';
require 'functions.php';

// 1. Admin access check (adjust to your system)
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// 2. Get candidate ID from GET and validate
$candidate_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($candidate_id <= 0) {
    die("Invalid candidate ID.");
}

// 3. Fetch candidate info (to get photo filename and election_id for redirect)
$stmt = $conn->prepare("SELECT c.photo, c.election_id, c.name as candidate_name, e.name as election_name 
                       FROM candidates c 
                       JOIN elections e ON c.election_id = e.id 
                       WHERE c.id = ?");
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $stmt->close();
    die("Candidate not found.");
}
$candidate = $result->fetch_assoc();
$photo = $candidate['photo'];
$election_id = $candidate['election_id'];
$stmt->close();

// 4. Delete candidate from database
$del_stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
$del_stmt->bind_param("i", $candidate_id);

if ($del_stmt->execute()) {
    // Log the deletion
    insertAuditLog($conn, $_SESSION['admin_username'], 'DELETE_CANDIDATE', [
        'candidate_id' => $candidate_id,
        'candidate_name' => $candidate['candidate_name'],
        'election_id' => $election_id,
        'election_name' => $candidate['election_name']
    ], $_SESSION['admin_id']);

    $del_stmt->close();

    // 5. Delete photo file if exists
    if ($photo) {
        $photo_path = '../uploads/' . $photo;
        if (file_exists($photo_path)) {
            @unlink($photo_path);
        }
    }

    // 6. Redirect back to managing candidates list for that election
    header("Location: manage_candidates.php?election=" . intval($election_id) . "&deleted=1");
    exit;
} else {
    $del_stmt->close();
    die("Failed to delete candidate. Please try again.");
}
