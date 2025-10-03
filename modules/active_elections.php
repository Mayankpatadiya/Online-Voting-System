<?php
require '../db.php';
session_start();


//election status update
$now = date('Y-m-d H:i:s');

//Activate elections that should be live
$conn->query("UPDATE elections SET status = 'active'
               WHERE start_date <= '$now' AND end_date > '$now' AND status = 'closed'"); // error solve: status != 'closed' to status = 'closed' on 20 July 2025

// End elections whose end_date has passed
$conn->query("UPDATE elections SET status = 'closed'
               WHERE end_date <= '$now' AND status != 'closed'");


// 1. Authentication
if (!isset($_SESSION['voter_id'])) {
    header('Location: login.php');
    exit;
}

$voterId = $_SESSION['voter_id'];

// 2. Fetch active elections
$electionsSql = "SELECT * FROM elections WHERE NOW() BETWEEN start_date AND end_date";
$elections = $conn->query($electionsSql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Active Elections</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h2 class="mb-3 text-primary">Active Elections</h2>
    <?php if ($elections->num_rows === 0): ?>
        <div class="alert alert-info">No active elections at this time.</div>
    <?php else: ?>
        <?php while ($election = $elections->fetch_assoc()): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?= htmlspecialchars($election['name']) ?></h4>
                    <small><?= date('d M Y, h:i A', strtotime($election['start_date'])) ?> - <?= date('d M Y, h:i A', strtotime($election['end_date'])) ?></small>
                </div>
                <div class="card-body">
                    <?php if (!empty($election['description'])): ?>
                        <p><?= nl2br(htmlspecialchars($election['description'])) ?></p>
                    <?php endif; ?>

                     <?php
                    // Fetch positions for this election
                   $positionsSql = "SELECT * FROM positions WHERE election_id = ?";
                    $stmtPos = $conn->prepare($positionsSql);
                    $stmtPos->bind_param("i", $election['id']);
                    $stmtPos->execute();
                    $positions = $stmtPos->get_result();
                    $stmtPos->close();

                    ?> 

                    <?php if ($positions->num_rows === 0): ?>
                        <div class="alert alert-warning">No positions found for this election.</div>
                    <?php else: ?>
                        <form method="POST" action="submit_vote.php">
                            <input type="hidden" name="election_id" value="<?= $election['id'] ?>">
                            <?php while ($position = $positions->fetch_assoc()): ?>
                                <div class="mb-4">
                                    <h5>
                                        <span class="badge bg-info text-dark"><?= htmlspecialchars($position['name']) ?></span>
                                    </h5>
                                    <?php
                                    // Check if user already voted for this position in this election
                                    $voteChkSql = "SELECT votes.id, candidates.name AS candidate_name 
                                                    FROM votes 
                                                    JOIN candidates ON votes.candidate_id = candidates.id
                                                    WHERE votes.voter_id=? AND votes.election_id=? AND votes.position_id=?";
                                    $stmtV = $conn->prepare($voteChkSql);
                                    $stmtV->bind_param("iii", $voterId, $election['id'], $position['id']);
                                    $stmtV->execute();
                                    $voteRes = $stmtV->get_result();
                                    $alreadyVoted = $voteRes->num_rows > 0;
                                    $votedCandidate = $alreadyVoted ? $voteRes->fetch_assoc()['candidate_name'] : null;

                                    if ($alreadyVoted): ?>
                                        <div class="alert alert-success py-2">
                                            You have already voted for <b><?= htmlspecialchars($position['name']) ?></b>
                                            <?php if ($votedCandidate): ?>: <span class="text-primary"><?= htmlspecialchars($votedCandidate) ?></span><?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="row">
                                            <?php
                                            // List candidates for this position
                                            $candsSql = "SELECT * FROM candidates WHERE election_id=? AND position_id=?";
                                            $stmtC = $conn->prepare($candsSql);
                                            $stmtC->bind_param("ii", $election['id'], $position['id']);
                                            $stmtC->execute();
                                            $cands = $stmtC->get_result();

                                            if ($cands->num_rows === 0): ?>
                                                <div class="col-12">
                                                    <span class="text-muted">No candidates for this position.</span>
                                                </div>
                                            <?php else: ?>
                                                <!-- <?php while ($cand = $cands->fetch_assoc()): ?>
                                                    <div class="col-md-4 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" 
                                                                name="vote[<?= $position['id'] ?>]" 
                                                                id="cand_<?= $position['id'] ?>_<?= $cand['id'] ?>"
                                                                value="<?= $cand['id'] ?>" required>
                                                            <label class="form-check-label" for="cand_<?= $position['id'] ?>_<?= $cand['id'] ?>">
                                                                <?= htmlspecialchars($cand['name']) ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endwhile; ?> -->
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <hr>
                            <?php endwhile; ?>

                            <?php
                            // Check if there's at least one position left to vote for
                            $positions->data_seek(0); // rewind
                            $canVote = false;
                            while ($posCheck = $positions->fetch_assoc()) {
                                $stmtV->bind_param("iii", $voterId, $election['id'], $posCheck['id']);
                                $stmtV->execute();
                                $voteRes = $stmtV->get_result();
                                if ($voteRes->num_rows === 0) {
                                    $canVote = true;
                                    break;
                                }
                            }
                            $positions->data_seek(0); // reset again for future possible use
                            ?>
                            <?php if ($canVote): ?>
                                <button type="submit" class="btn btn-success">Submit Vote</button>
                            <?php else: ?>
                                <div class="alert alert-info">You have already voted for all available positions in this election.</div>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
    <a href="../index.php" class="btn btn-outline-primary mt-2">Back to Dashboard</a>
</div>
</body>
</html>
