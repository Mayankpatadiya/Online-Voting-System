<?php
session_start();
if (!isset($_SESSION['voter_id'])) {
    header("Location: voter/login.php");
    exit;   
}
require '../db.php';

//election status update
$now = date('Y-m-d H:i:s');

//Activate elections that should be live
$conn->query("UPDATE elections SET status = 'active'
               WHERE start_date <= '$now' AND end_date > '$now' AND status = 'closed'"); // error solve: status != 'closed' to status = 'closed' on 20 July 2025

// End elections whose end_date has passed
$conn->query("UPDATE elections SET status = 'closed'
               WHERE end_date <= '$now' AND status != 'closed'");


$voter_id_string = $_SESSION['voter_id']; // voter id
// Get numeric voter db id (if needed)
$stmt = $conn->prepare("SELECT id FROM voters WHERE voter_id = ?");
$stmt->bind_param("s", $voter_id_string);
$stmt->execute();
$stmt->bind_result($voter_db_id);
$stmt->fetch();
$stmt->close();
// Fetch voting history
$historyQuery = "SELECT e.name AS election_name, e.start_date, e.end_date, c.name AS candidate_name, v.voted_at 
                 FROM votes v 
                 JOIN elections e ON v.election_id = e.id 
                 JOIN candidates c ON v.candidate_id = c.id 
                 WHERE v.voter_id = ? 
                 ORDER BY v.voted_at DESC";

$stmt = $conn->prepare($historyQuery);
$stmt->bind_param("i", $voter_db_id);
$stmt->execute();

$result = $stmt->get_result();

$history = [];

while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}
$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voting History - Online Voting System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 CSS + Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            min-height: 100vh;
        }
        .history-card {
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            background: #fff;
            padding: 2rem 2.5rem;
            margin-top: 45px;
            max-width: 900px;
        }
        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }
        .status-badge {
            text-transform: capitalize;
            font-size: 0.9rem;
            padding: 0.2em 0.6em;
        }
        @media (max-width: 700px) {
            .history-card { padding: 1rem 0.5rem; }
        }
    </style>
</head>
<body>
    <div class="container d-flex flex-column align-items-center">
        <div class="history-card w-100">
            <div class="history-header mb-4">
                <div>
                    <h2 class="mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Voting History</h2>
                    <p class="text-muted mb-0 small">A record of your votes in all past elections.</p>
                </div>
                <a href="profile.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Profile
                </a>
            </div>
            <div class="mb-3">
                <!-- Use alert for status messages, or hide as needed -->
                <!-- <div class="alert alert-info">You haven't voted in any elections yet.</div> -->
                <?php if (empty($history)): ?>
                    <div class="alert alert-warning">You haven't voted in any elections yet.</div>
                
                <?php endif; ?>
                <?php if (count($history) > 0): ?>
                    <div class="alert alert-info">You have voted in <?= count($history) ?> elections.</div>
                <?php endif; ?>

            </div>
            <div class="table-responsive">
                <table class="table align-middle table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Election</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Candidate</th>
                            <th>Voted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loop through history and display each entry -->
                        <?php if (empty($history)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No voting history found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($history as $entry): ?>
                                <tr>
                                    <td><?= htmlspecialchars($entry['election_name']) ?></td>
                                    <td><?= date('d M Y', strtotime($entry['start_date'])) ?> - <?= date('d M Y', strtotime($entry['end_date'])) ?></td>
                                    <td>
                                        <span class="badge bg-success status-badge">Voted</span>
                                    </td>
                                    <td><?= htmlspecialchars($entry['candidate_name']) ?></td>
                                    <td><?= date('d M Y H:i', strtotime($entry['voted_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (count($history) > 0): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Total <?= count($history) ?> elections voted.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<!-- Bootstrap JS (optional, for interactivity) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
