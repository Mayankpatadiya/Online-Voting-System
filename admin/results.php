<?php
session_start();
require '../db.php';

// Optional: Check admin session or user session for authorization if needed
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require 'functions.php';

// Get election id from URL
$election_id = isset($_GET['election']) ? intval($_GET['election']) : 0;
if ($election_id <= 0) {
    die("Invalid election ID.");
}

// Fetch election details
$stmt = $conn->prepare("SELECT name, start_date, end_date, status FROM elections WHERE id = ?");
$stmt->bind_param("i", $election_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    die("Election not found.");
}
$election = $result->fetch_assoc();
$stmt->close();

// Log the results view
insertAuditLog($conn, $_SESSION['admin_username'], 'VIEW_RESULTS', [
    'election_name' => $election['name'],
    'election_id' => $election_id,
    'election_status' => $election['status']
], $_SESSION['admin_id']);

// Fetch candidates and their vote counts for this election
// We count votes from the votes table grouped by candidate
$sql = "
    SELECT c.id, c.name, c.photo, COUNT(v.id) AS votes
    FROM candidates c
    LEFT JOIN votes v ON v.candidate_id = c.id AND v.election_id = c.election_id
    WHERE c.election_id = ?
    GROUP BY c.id, c.name, c.photo
    ORDER BY votes DESC, c.name ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $election_id);
$stmt->execute();
$candidates_result = $stmt->get_result();

$candidates = [];
while ($row = $candidates_result->fetch_assoc()) {
    $candidates[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Results for <?= htmlspecialchars($election['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <style>
        body { background: linear-gradient(135deg, #f5f7fa, #c3cfe2); min-height: 100vh; }
        .results-card {
            max-width: 900px;
            background: #fff;
            margin: 40px auto;
            border-radius: 1rem;
            padding: 2rem 2.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .candidate-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        .votes-badge {
            font-size: 1.25rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="results-card">
        <h2 class="mb-4 text-primary"><i class="bi bi-bar-chart-line-fill me-2"></i>Election Results</h2>
        
        <h4><?= htmlspecialchars($election['name']) ?></h4>
        <p class="text-muted mb-4">
            From <?= date('d M Y', strtotime($election['start_date'])) ?> 
            To <?= date('d M Y', strtotime($election['end_date'])) ?> —
            Status: <span class="badge bg-<?= $election['status'] == 'active' ? 'success' : ($election['status'] == 'closed' ? 'secondary' : 'warning') ?>">
                <?= ucfirst(htmlspecialchars($election['status'])) ?>
            </span>
        </p>

        <?php if (empty($candidates)): ?>
            <div class="alert alert-info">No candidates found for this election.</div>
        <?php else: ?>
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Candidate</th>
                        <th>Photo</th>
                        <th>Votes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($candidates as $i => $cand): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($cand['name']) ?></td>
                        <td>
                            <?php if (!empty($cand['photo'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($cand['photo']) ?>" alt="Photo" class="candidate-photo" />
                            <?php else: ?>
                                <img src="../assets/user.png" alt="No photo" class="candidate-photo" />
                            <?php endif; ?>
                        </td>
                        <td class="votes-badge text-center"><?= intval($cand['votes']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="manage_elections.php" class="btn btn-secondary mt-3"><i class="bi bi-arrow-left"></i> Back to Manage Elections</a><br>
        <a href="admin_dashboard.php" class="btn btn-secondary my-3 "><i class="bi bi-arrow-left"></i> Back to dashboard</a>

        
    </div>
    
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
