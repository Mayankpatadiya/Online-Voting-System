<?php
session_start();
require '../db.php';

// Optional: check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Validate and get election ID from query string
$election_id = isset($_GET['election']) ? intval($_GET['election']) : 0;
if ($election_id <= 0) {
    die("Invalid election ID.");
}

// Fetch election details
$stmt = $conn->prepare("SELECT name FROM elections WHERE id = ?");
$stmt->bind_param("i", $election_id);
$stmt->execute();
$stmt->bind_result($election_name);
if (!$stmt->fetch()) {
    $stmt->close();
    die("Election not found.");
}
$stmt->close();

// Fetch candidates for this election
$candidates = [];
$stmt = $conn->prepare("SELECT id, name, photo, bio FROM candidates WHERE election_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $election_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $candidates[] = $row;
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Candidates – <?= htmlspecialchars($election_name) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            min-height: 100vh;
        }
        .container {
            max-width: 980px;
            margin-top: 40px;
            background: #fff;
            border-radius: 1rem;
            padding: 2rem 2.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .candidate-photo {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">
                <i class="bi bi-person-lines-fill me-2"></i>Manage Candidates – <?= htmlspecialchars($election_name) ?>
            </h2>
            <a href="add_candidate.php?election=<?= $election_id ?>" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> Add New Candidate
            </a>
        </div>
        <?php if (empty($candidates)): ?>
            <div class="alert alert-info">No candidates found for this election.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Bio</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidates as $index => $candidate): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <img src="<?= !empty($candidate['photo']) ? '../uploads/' . htmlspecialchars($candidate['photo']) : '../assets/user.png' ?>"
                                         alt="Candidate Photo" class="candidate-photo">
                                </td>
                                <td><?= htmlspecialchars($candidate['name']) ?></td>
                                <td><?= nl2br(htmlspecialchars($candidate['bio'])) ?></td>
                                <td>
                                    <a href="edit_candidate.php?election=<?= $election_id ?>&id=<?= $candidate['id'] ?>" class="btn btn-outline-primary btn-sm" title="Edit Candidate">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete_candidate.php?election=<?= $election_id ?>&id=<?= $candidate['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this candidate?');" title="Delete Candidate">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <a href="manage_elections.php" class="btn btn-sm btn-outline-secondary mt-3">
            <i class="bi bi-arrow-left"></i> Back to Manage Elections
        </a>
    </div>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
