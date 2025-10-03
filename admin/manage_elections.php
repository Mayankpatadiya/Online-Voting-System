<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// 2. Fetch all elections
$elections = [];
$sql = "SELECT id, name, start_date, end_date, status FROM elections ORDER BY start_date DESC";
$res = $conn->query($sql);
if ($res && $res->num_rows) {
    while ($row = $res->fetch_assoc()) {
        $elections[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Elections – Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa, #c3cfe2); min-height: 100vh; }
        .election-card { border-radius: 0.9rem; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 2rem 2.5rem; max-width: 1050px; margin-top: 40px; }
        .election-header { display: flex; justify-content: space-between; align-items: center; }
        .table-responsive { margin-top: 1.2em; }
        .status-badge { text-transform: capitalize; padding: 0.3em 0.75em; font-size: 0.92em;}
    </style>
</head>
<body>
    <div class="container">
        <div class="election-card mx-auto">
            <div class="election-header mb-4">
                <div>
                    <h2 class="mb-0 text-primary"><i class="bi bi-list-task me-1"></i>Manage Elections</h2>
                    <div class="text-muted small">View, create, and update all elections in the system.</div>
                </div>
                
                <a href="create_election.php" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i> New Election
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Total Candidates</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($elections)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No elections found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($elections as $election): ?>
                                <tr>
                                    <td><?= htmlspecialchars($election['name']) ?></td>
                                    <td><?= date('Y-m-d', strtotime($election['start_date'])) ?></td>
                                    <td><?= date('Y-m-d', strtotime($election['end_date'])) ?></td>
                                    <td>
                                        <?php
                                        
// 1. Automatic election status update
$now = date('Y-m-d H:i:s');
// To "active"
$conn->query("UPDATE elections SET status = 'active'
    WHERE start_date <= '$now' AND end_date > '$now' AND status != 'closed'");
// To "closed"
$conn->query("UPDATE elections SET status = 'closed'
    WHERE end_date <= '$now' AND status <> 'closed'");


                                        $badgeClass = 'warning';
                                        if ($election['status'] === 'active') $badgeClass = 'success';
                                        else if ($election['status'] === 'closed') $badgeClass = 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?> status-badge">
                                            <?= htmlspecialchars(ucfirst($election['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $cid = $election['id'];
                                        $q2 = $conn->prepare("SELECT COUNT(*) as count FROM candidates WHERE election_id = ?");
                                        $q2->bind_param("i", $cid);
                                        $q2->execute();
                                        $count = $q2->get_result()->fetch_assoc()['count'];
                                        $q2->close();
                                        echo $count;
                                        ?>
                                    </td>
                                    <td>
                                        <a href="edit_election.php?id=<?= $cid ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <a href="candidates.php?election=<?= $cid ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-person-lines-fill"></i></a>
                                        <a href="results.php?election=<?= $cid ?>" class="btn btn-sm btn-outline-success"><i class="bi bi-bar-chart"></i></a>
                                        <a href="delete_election.php?id=<?= $cid ?>" onclick="return confirm('Delete this election?')" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>        
                    </tbody>
                </table>
                                    <a href="admin_dashboard.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to dashboard</a>

            </div>
        </div>
    </div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
