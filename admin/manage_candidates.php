<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch elections for dropdown
$elections = [];
$res = $conn->query("SELECT id, name FROM elections ORDER BY start_date DESC");
while ($row = $res->fetch_assoc()) {
    $elections[] = $row;
}

// Election selected by admin
$election_id = isset($_GET['election']) ? intval($_GET['election']) : 0;
$candidates = [];
$election_name = '';
if ($election_id > 0) {
    // Get election name
    $stmt = $conn->prepare("SELECT name FROM elections WHERE id = ?");
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $stmt->bind_result($election_name);
    $stmt->fetch();
    $stmt->close();

    // Fetch candidates for this election
    $stmt = $conn->prepare("SELECT id, name, photo, bio FROM candidates WHERE election_id = ? ORDER BY id ASC");
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Candidates – Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa, #c3cfe2); min-height: 100vh;}
        .candidates-card { border-radius: .9rem; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,0.07); padding: 2rem 2.5rem; max-width: 975px; margin-top: 40px;}
        .candidate-photo { width: 50px; height: 50px; border-radius: 50%; object-fit: cover;}
    </style>
</head>
<body>
<div class="container">
    <div class="candidates-card mx-auto">
        <h2 class="text-primary mb-3"><i class="bi bi-person-lines-fill me-1"></i>Manage Candidates</h2>
            <?php if ($election_id > 0): ?>
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <div>
                    <strong>Election:</strong> <?= htmlspecialchars($election_name) ?>
                </div>
                <a href="add_candidate.php?election=<?= $election_id ?>" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Add Candidate
                </a>
            </div>
            <div class="table-responsive mb-3">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Bio</th>
                            <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($candidates)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No candidates found for this election.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($candidates as $idx => $cand): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td>
                                        <img src="<?= !empty($cand['photo']) ? '../uploads/' . htmlspecialchars($cand['photo']) : '../assets/user.png' ?>"
                                             alt="Photo" class="candidate-photo">
                                    </td>
                                    <td><?= htmlspecialchars($cand['name']) ?></td>
                                    <td><?= htmlspecialchars($cand['bio']) ?></td>
                                    <td>
                                        <a href="edit_candidate.php?election=<?= $election_id ?>&id=<?= $cand['id'] ?>"
                                           class="btn btn-outline-primary btn-sm" title="Edit">
                                           <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="delete_candidate.php?election=<?= $election_id ?>&id=<?= $cand['id'] ?>"
                                            class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('Delete this candidate?')"
                                            title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <a class="btn btn-outline-secondary" href="manage_elections.php">
                <i class="bi bi-arrow-left"></i> Back to Elections
            </a>
        <?php else: ?>
            <div class="alert alert-info mt-3">Please select an election to manage its candidates.</div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
