<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Set JSON response header
header('Content-Type: application/json');

// Include your database connection
require '../db.php';

// Check DB connection
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

// Check if voter is logged in
if (!isset($_SESSION['voter_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
    exit();
}

$session_voter_id = $_SESSION['voter_id']; // This is likely a string like 'VOTER001'
$voter_db_id = null;
$voter_check = $conn->prepare("SELECT id FROM voters WHERE voter_id = ?");
$voter_check->bind_param("s", $session_voter_id);
$voter_check->execute();
$voter_check->bind_result($voter_db_id);
if (!$voter_check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Invalid voter session.']);
    $voter_check->close();
    exit();
}
$voter_check->close();

// Now $voter_db_id is the numeric id to use for votes table

// Get candidate_id from POST
$candidate_id = isset($_POST['candidate_id']) ? intval($_POST['candidate_id']) : 0;

if ($candidate_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'No candidate selected.']);
    exit();
}

// Get position_id of the selected candidate

// Get position_id and election_id of the selected candidate
$stmt = $conn->prepare("SELECT position_id, election_id FROM candidates WHERE id = ?");
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$stmt->bind_result($position_id, $election_id);

if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Invalid candidate selected.']);
    $stmt->close();
    exit();
}
$stmt->close();



// Check if the voter has already voted in this election
$check_vote = $conn->prepare("SELECT id FROM votes WHERE voter_id = ? AND election_id = ?");
$check_vote->bind_param("ii", $voter_db_id, $election_id);
$check_vote->execute();
$check_vote->store_result();

if ($check_vote->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already voted in this election.']);
    $check_vote->close();
    exit();
}
$check_vote->close();


// Insert the vote (now including election_id)
$insert_vote = $conn->prepare("INSERT INTO votes (voter_id, candidate_id, position_id, election_id, voted_at) VALUES (?, ?, ?, ?, NOW())");
$insert_vote->bind_param("iiii", $voter_db_id, $candidate_id, $position_id, $election_id);


if ($insert_vote->execute()) {
    // Get election and candidate names for audit log
    $details_stmt = $conn->prepare("
        SELECT e.name as election_name, c.name as candidate_name 
        FROM elections e 
        JOIN candidates c ON c.election_id = e.id 
        WHERE c.id = ?
    ");
    $details_stmt->bind_param("i", $candidate_id);
    $details_stmt->execute();
    $details_result = $details_stmt->get_result();
    $details = $details_result->fetch_assoc();
    $details_stmt->close();

    // Log the vote action
    require_once '../admin/functions.php';
    insertAuditLog($conn, $session_voter_id, 'VOTE_CAST', [
        'election_name' => $details['election_name'],
        'election_id' => $election_id,
        'position_id' => $position_id
        // Note: We don't log candidate_id to maintain vote secrecy
    ], $voter_db_id);

    echo json_encode(['success' => true, 'message' => 'Your vote has been recorded.']);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to record vote.',
        'error' => $insert_vote->error
    ]);
}

$insert_vote->close();
$conn->close();
?>
