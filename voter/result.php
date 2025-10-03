<?php
session_start();
if (!isset($_SESSION['voter_id'])) {
    header("Location: login.php");
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



// Fetch all completed elections
$election_q = $conn->query("SELECT id, name FROM elections WHERE status = 'closed' ORDER BY end_date DESC");
$elections = [];
while ($row = $election_q->fetch_assoc()) {
    $elections[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Election Results - Online Voting System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        .winner { font-weight: bold; color: #128817; }
        .result-card {margin-bottom: 2.5rem;}
        .vote-bar-bg { background: #e3e9f7; border-radius: 6px; }
        .vote-bar { background: #4d84fa; height: 18px; border-radius: 6px; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="mb-4">
        <h2 class="text-center text-primary">Election Results</h2>
        <hr>
    </div>
    <?php if (empty($elections)): ?>
        <p class="text-center">No completed elections to show yet.</p>
    <?php else: ?>
        <?php foreach ($elections as $election): ?>
            <div class="card result-card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><?= htmlspecialchars($election['name']) ?></h5>
                </div>
                <div class="card-body">
                <?php
                    // Get candidates and vote counts for this election
                    $cands = [];
                    $cand_stmt = $conn->prepare("
                        SELECT c.id, c.name, 
                            (SELECT COUNT(*) FROM votes v WHERE v.election_id = ? AND v.candidate_id = c.id) as vote_count
                        FROM candidates c
                        WHERE c.election_id = ?
                    ");
                    $cand_stmt->bind_param("ii", $election['id'], $election['id']);
                    $cand_stmt->execute();
                    $cand_result = $cand_stmt->get_result();
                    $max_votes = 0;
                    while ($cand = $cand_result->fetch_assoc()) {
                        $cands[] = $cand;
                        if ($cand['vote_count'] > $max_votes) $max_votes = $cand['vote_count'];
                    }
                    $cand_stmt->close();
                    
                    if (empty($cands)) {
                        echo "<p>No candidates found for this election.</p>";
                    } else {
                        $total_votes = array_sum(array_column($cands, 'vote_count'));
                        foreach ($cands as $cand) {
                            $percent = $total_votes > 0 ? round(($cand['vote_count'] / $total_votes) * 100, 1) : 0;
                            $is_winner = ($cand['vote_count'] == $max_votes && $max_votes > 0);
                ?>
                    <div class="mb-3">
                        <span<?= $is_winner ? ' class="winner"' : '' ?>>
                            <?= htmlspecialchars($cand['name']) ?>
                            <?= $is_winner ? ' <span class="badge bg-success">Winner</span>' : '' ?>
                        </span>
                        <span class="float-end"><?= $cand['vote_count'] ?> vote<?= $cand['vote_count']==1 ? '' : 's' ?> (<?= $percent ?>%)</span>
                        <div class="vote-bar-bg mt-1">
                            <div class="vote-bar" style="width: <?= $percent ?>%"></div>
                        </div>
                    </div>
                <?php
                        }
                        echo "<div class='small text-muted'>Total votes: $total_votes</div>";
                    }
                ?>
                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
    <div class="text-center mt-4">
        <a href="../index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
