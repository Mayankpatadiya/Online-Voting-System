<?php
session_start();

require '../db.php';

if (!isset($_SESSION['admin_username'])) {
    header('Location: admin_login.php');
    exit();
}

// Fetch election data

$activeElections = [];

//election status update
$now = date('Y-m-d H:i:s');
//Activate elections that should be live
$conn->query("UPDATE elections SET status = 'active'
               WHERE start_date <= '$now' AND end_date > '$now' AND status = 'closed'"); // error solve: status != 'closed' to status = 'closed' on 20 July 2025
// End elections whose end_date has passed
$conn->query("UPDATE elections SET status = 'closed'
               WHERE end_date <= '$now' AND status != 'closed'");
// Fetch active elections
$activeElections = $conn->query("SELECT * FROM elections ORDER BY start_date DESC");
// Fetch total counts for dashboard
$totalVoters = $conn->query("SELECT COUNT(*) FROM voters")->fetch_row()[0];
$totalCandidates = $conn->query("SELECT COUNT(*) FROM candidates")->fetch_row()[0];
$totalElections = $conn->query("SELECT COUNT(*) FROM elections")->fetch_row()[0];
$ended_elections = $conn->query("SELECT COUNT(*) FROM elections WHERE status = 'closed'")->fetch_row()[0];
$totalEndedElections = $conn->query("SELECT COUNT(*) FROM elections WHERE status = 'closed'")->fetch_row()[0];
// Fetch latest activity logs
// $activityLogs = $conn->query("SELECT * FROM activity_log ORDER BY timestamp DESC LIMIT 5");
// Fetch latest announcements
// $announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5");
// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard – Online Voting System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS + Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa, #c3cfe2); min-height: 100vh; }
        .dashboard-card { border-radius: .75rem; background: #fff; box-shadow: 0 2px 16px rgba(0,0,0,0.07); padding: 2rem 2.5rem; margin-top: 36px; }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; }
        .dashboard-header h2 { margin-bottom: 0; }
        .admin-nav { border-radius: 8px; margin-bottom: 2rem; background: #f6f7fa; padding: 0.7rem 1rem;}
        .stat-card { display: flex; align-items: center; border-radius: 12px; padding: 1.3em 1.5em; background: #f8faff; margin-bottom: 1rem; box-shadow: 0 1px 8px rgba(52,104,173,0.03);}
        .stat-card .icon { font-size: 2.2rem; margin-right: 1.2em; color: #4e7fff;}
        .table-responsive { margin-top: 1.2em;}
    </style>
</head>
<body>
<div class="container">
    <div class="dashboard-card shadow-lg mx-auto" style="max-width: 1050px;">
        <div class="dashboard-header mb-4">
            <h2><i class="bi bi-speedometer2 text-primary me-2"></i>Admin Dashboard</h2>
            <a href="logout.php" class="btn btn-outline-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>
        <div class="admin-nav d-flex flex-wrap gap-2 mb-4">
            <a class="btn btn-outline-primary" href="manage_elections.php"><i class="bi bi-list-task me-1"></i>Manage Elections</a>
<div class="btn-group">
    <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-people-fill me-1"></i> Manage Candidates
    </button>
    <ul class="dropdown-menu">
        <?php foreach ($activeElections as $election): ?>
            <li><a class="dropdown-item" href="manage_candidates.php?election=<?= $election['id'] ?>">
                <?= htmlspecialchars($election['name']) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
            <a class="btn btn-outline-primary" href="manage_voters.php"><i class="bi bi-person-lines-fill me-1"></i>Manage Voters</a>
            <a class="btn btn-outline-primary" href="../voter/result.php"><i class="bi bi-bar-chart-fill me-1"></i>Results</a>
            <a class="btn btn-outline-primary" href="audit_log.php"><i class="bi bi-clipboard-data me-1"></i>Audit Log</a>
            <a class="btn btn-outline-primary" href="admin_profile.php"><i class="bi bi-person-circle me-1"></i>My Profile</a>
        </div>

        <!-- Statistics -->
        <div class="row mb-3">
            <div class="col-md-3 col-6">
                <div class="stat-card">

                    <span class="icon"><i class="bi bi-bar-chart"></i></span>
                    <div>

                        <div class="fw-bold h5 mb-0"><?= $totalElections ?></div>
                        <div class="text-muted small">Active Elections</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <span class="icon"><i class="bi bi-person-check"></i></span>
                    <div>
                        <div class="fw-bold h5 mb-0"><?=$totalVoters ?></div>
                        <div class="text-muted small">Voters</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <span class="icon"><i class="bi bi-person-square"></i></span>
                    <div>
                        <div class="fw-bold h5 mb-0"><?= $totalCandidates?></div>
                        <div class="text-muted small">Candidates</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card">
                    <span class="icon"><i class="bi bi-check2-circle"></i></span>
                    <div>
                        <div class="fw-bold h5 mb-0"><?= $ended_elections?></div>
                        <div class="text-muted small">Elections Ended</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Active Elections Table -->
        <h5 class="mt-3 mb-3 text-primary">Active Elections</h5>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Election</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                    <!-- Example static rows, to be populated from backend -->
                    <?php if (empty($activeElections)): ?>
   <tr><td colspan="5" class="text-center text-muted">No active elections at this time.</td></tr>
<?php else: ?>
   <?php foreach ($activeElections as $election): ?>
   <tr>
        <td><?= htmlspecialchars($election['name']) ?></td>
        <td><?= date('Y-m-d', strtotime($election['start_date'])) ?></td>
        <td><?= date('Y-m-d', strtotime($election['end_date'])) ?></td>
        <td><span class=""><?= htmlspecialchars($election['status']) ?></span></td>
        <td>
            <a href="results.php?election=<?= $election['id'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-bar-chart-fill"></i> Results</a>
            <a href="delete_election.php?id=<?= $election['id'] ?>" onclick="return confirm('Delete this election?')" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete</a>
        </td>
   </tr>
   <?php endforeach; ?>
<?php endif; ?>

                </tbody>
            </table>
                    
        </div>

        <!-- Announcements / Logs Section -->
        <div class="mt-4">
            <h5 class="text-primary mb-2"><i class="bi bi-megaphone me-2"></i>Latest Activity & Announcements</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item px-0">Election "Union Chairperson 2025" is now active.</li>
                <li class="list-group-item px-0">New candidate registered for "College Treasurer 2025".</li>
                <li class="list-group-item px-0">Voting turnout report generated on 2025-07-16.</li>
            </ul>
        </div>
    </div>
</div>
<!-- Optional Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
