<?php
session_start();
if (!isset($_SESSION['voter_id'])) {
    header("Location: voter/login.php");
    exit;   
}
require 'db.php';
$name = $_SESSION['name'];
$voter_id = $_SESSION['voter_id'];

// election status update
$now = date('Y-m-d H:i:s');

// Activate elections that should be live
$conn->query("UPDATE elections 
              SET status = 'active'
              WHERE start_date <= '$now' 
              AND end_date > '$now' 
              AND status = 'pending'");

// End elections whose end_date has passed
$conn->query("UPDATE elections 
              SET status = 'closed'
              WHERE end_date <= '$now' 
              AND status != 'closed'");

// Set elections to pending if they haven’t started yet
$conn->query("UPDATE elections 
              SET status = 'pending'
              WHERE start_date > '$now' 
              AND status != 'pending'");



// Get voter's numeric id
$voter_db_id = null;
$stmt = $conn->prepare("SELECT id, profile_photo FROM voters WHERE voter_id = ?");
$stmt->bind_param("s", $voter_id);
$stmt->execute();
$stmt->bind_result($voter_db_id,$profile_photo);
$stmt->fetch();
$stmt->close();


// Example active elections data for dashboard
$sql = "SELECT id, name, status from elections where status = 'active' ORDER BY start_date DESC";
$rs = $conn->query($sql);
$active_elections = [];
if($rs && $rs->num_rows > 0)
{   while($row = $rs->fetch_assoc())
    {
        $active_elections[] = $row;
    }
}

// ...existing code...
foreach ($active_elections as &$election) {
 $stmt = $conn->prepare("SELECT COUNT(*) FROM votes WHERE voter_id = ? AND election_id = ?");
$stmt->bind_param("ii", $voter_db_id, $election['id']);

    $stmt->execute();
    $stmt->bind_result($vote_count);
    $stmt->fetch();
    $stmt->close();
    $election['has_voted'] = ($vote_count > 0);
}
unset($election); // Remove reference to last element
// Now $active_elections contains has_voted for each election
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Dashboard - Online Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding-top: 35px;
                        height: 100vh;

        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            margin: 6px 0;
            border-radius: 8px;
        }
        .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.18);
            color: #fff;
        }
        .user-profile {
            text-align: center;
            margin-bottom: 2rem;
        }
        .profile-avatar {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 2.5rem;
        }
        .main-content {
            padding: 2rem 1rem;
        }
        .dashboard-card {
            border-radius: 1rem;
            box-shadow: 0 4px 18px rgba(0,0,0,0.06);
            border: none;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: #fff;
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        .btn-vote {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            color: #fff;
            border-radius: 25px;
            padding: 10px 30px;
            font-weight: 600;
        }
        @media (max-width: 991.98px) {
            .sidebar {
                min-height: auto;
                padding-top: 10px;
            }
            .main-content {
                padding: 1rem 0.5rem;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <!-- Sidebar -->
        <nav class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar d-flex flex-column">
            <div class="user-profile mb-3">
                <div class="profile-avatar mb-2">
                    <?php if (!empty($profile_photo) && file_exists($profile_photo)): ?>
                        <img src="<?= htmlspecialchars($profile_photo) ?>" alt="Profile Photo" class="rounded-circle" width="80" height="80">
                    <?php else: ?>
                        <img src="assets/pro.png" alt="Default Profile" class="rounded-circle" width="80" height="80">
                    <?php endif; ?>
                </div>
                <div><strong><?= htmlspecialchars($name) ?></strong></div>
                <small>ID: <?= htmlspecialchars($voter_id) ?></small>
            </div>
            <ul class="nav nav-pills flex-column mb-auto">
                <li><a class="nav-link active" href="#"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li><a class="nav-link" href="modules/active_elections.php"><i class="bi bi-ballot me-2"></i>Active Elections</a></li>
                <li><a class="nav-link" href="modules/voting_history.php"><i class="bi bi-clock-history me-2"></i>Voting History</a></li>
                <li><a class="nav-link" href="voter/result.php"><i class="bi bi-bar-chart-line-fill me-2"></i>View Results</a></li>
                <li><a class="nav-link" href="modules/profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
            </ul>
            <div class="mt-auto mb-3">
                <a href="voter/logout.php" class="btn btn-outline-light w-100"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </div> 
        </nav>
        <!-- Main Content -->
        <main class="col py-3 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Dashboard Overview</h2>
            </div>
            <?php
                $votes_cast = 0;
                foreach ($active_elections as $election) {
                    if ($election['has_voted']) $votes_cast++;
                }
            ?>
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        <div class="fw-bold fs-3"><?= $votes_cast ?></div>
                        <div class="text-muted">Votes Cast</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="bi bi-calendar-event text-primary" style="font-size: 2rem;"></i>
                         <div class="fw-bold fs-3"><?= count($active_elections) ?></div>
                        <div class="text-muted">Active Elections</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="bi bi-trophy text-warning" style="font-size: 2rem;"></i>
                        <div class="fw-bold fs-3">85%</div>
                        <div class="text-muted">Participation</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="bi bi-people text-info" style="font-size: 2rem;"></i>
                        <div class="fw-bold fs-5">Jan 2024</div>
                        <div class="text-muted">Member Since</div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-lg-8 mb-3">
                    <div class="card dashboard-card h-100">
                        <div class="card-header bg-transparent">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body text-center py-4">
                            <?php
                            $next_election = null;
                            foreach ($active_elections as $election) {
                                if (!$election['has_voted']) {
                                    $next_election = $election;
                                    break;
                                }
                            }
                            ?>
                            <?php if ($next_election): ?>
                                <i class="bi bi-ballot text-primary mb-3" style="font-size: 2.5rem;"></i>
                                <h4>Ready to Vote?</h4>
                                <p class="text-muted mb-4">Cast your vote in the next available election</p>
                                <a href="voter/vote.php?election_id=<?= $next_election['id'] ?>" class="btn btn-vote">
                                    <i class="bi bi-check-circle me-2"></i> Go to Vote
                                </a>
                            <?php else: ?>
                                <i class="bi bi-check-circle-fill text-success mb-3" style="font-size: 2.5rem;"></i>
                                <h4>Thank You!</h4>
                                <p class="text-muted mb-4">You have voted in all available elections</p>
                                <button class="btn btn-secondary" disabled>
                                    <i class="bi bi-check-circle me-2"></i> Already Voted
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php
// echo '<pre>'; print_r($active_elections); echo '</pre>';?>
                <div class="col-lg-4 mb-3">
                    <div class="card dashboard-card h-100">
                        <div class="card-header bg-transparent">
                            <h6 class="mb-0">Voting Status</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                               <?php
                                $shown_ids = [];
                                foreach ($active_elections as $election):
                                    if (in_array($election['id'], $shown_ids)) contiue;
                                    $shown_ids[] = $election['id'];
                                ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= htmlspecialchars($election['name']) ?>
                                        <?php if ($election['has_voted']): ?>
                                            <span class="badge bg-success">Voted</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                </div>
                <!-- You can add more dashboard cards here if needed -->
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
