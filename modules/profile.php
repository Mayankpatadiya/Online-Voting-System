<?php
session_start();
if (!isset($_SESSION['voter_id'])) {
    header("Location: ../voter/login.php");
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



$voter_id_string = $_SESSION['voter_id']; //  voter id

// Get numeric voter db id (if needed)
$stmt = $conn->prepare("SELECT id, name, email, dob, profile_photo, registered_at FROM voters WHERE voter_id = ?");
$stmt->bind_param("s", $voter_id_string);
$stmt->execute();
$stmt->bind_result($voter_db_id, $name, $email, $dob,$profile_photo, $registered_at);
$stmt->fetch();
$stmt->close();

// Fetch all elections
$electionsQuery = "SELECT id, name, status FROM elections ORDER BY start_date DESC";
$electionsRs = $conn->query($electionsQuery);

$voting_status = [];
if ($electionsRs && $electionsRs->num_rows > 0) {
    while ($election = $electionsRs->fetch_assoc()) {
        // Use numeric $voter_db_id for votes table!
        $stmt = $conn->prepare("SELECT COUNT(*) FROM votes WHERE voter_id = ? AND election_id = ?");
        $stmt->bind_param("ii", $voter_db_id, $election['id']);
        $stmt->execute();
        $stmt->bind_result($vote_count);
        $stmt->fetch();
        $stmt->close();
        $status = ($vote_count > 0) ? 'Voted' : 'Not Voted';
        $voting_status[] = [
            'name' => $election['name'],
            'status' => $election['status'],
            'voting_status' => $status,
        ];
    }
}
// ----- Remove Profile Photo -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_photo'])) {
    // Remove the photo file from disk if it exists
    if (!empty($profile_photo) && file_exists("../" . $profile_photo)) {
        unlink("../" . $profile_photo);
    }
    // Set profile_photo to NULL in the DB
    $stmt = $conn->prepare("UPDATE voters SET profile_photo = NULL WHERE voter_id = ?");
    $stmt->bind_param("s", $voter_id_string);
    $stmt->execute();
    $stmt->close();
    // Set a session message to indicate success
    $_SESSION['profile_msg'] = "Profile photo removed successfully.";
    header("Location: profile.php");
    exit();
    // Optionally, set a success flag/session message and reload the 
}
// Display message if set
if (isset($_SESSION['profile_msg'])) {
    echo '<div class="alert alert-success mt-2">' . htmlspecialchars($_SESSION['profile_msg']) . '</div>';
    unset($_SESSION['profile_msg']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Profile - Online Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
       .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding-top: 35px;
                        height: 100vh;

        }
        .sidebar .nav-link {
            color: #ddd;
              margin: 6px 0;
            border-radius: 8px;
        }
       .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.18);
            color: #fff;
        }
         .main-content {
            padding: 2rem 1rem;
        }
        .profile-card {
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 2rem;
            
            background-color: #ffffff;
        }
        .profile-avatar {
            width: 75px;
            height: 75px;
            margin: 0 auto 10px;

            background-color: #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row flex-nowrap">
        <!-- Sidebar -->
        <nav class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 sidebar d-flex flex-column">
            <div class="user-profile text-center mb-3 mt-3">
                <div class="profile-avatar mx-auto mb-2">
                    <?php if (!empty($profile_photo) && file_exists("../" . $profile_photo)): ?>
                        <img src="<?= htmlspecialchars("../" . $profile_photo) ?>" width="75" height="75" class="rounded-circle" alt="Profile">
                    <?php else: ?>
                        <img src="../assets/pro.png" width="75" height="75" class="rounded-circle" alt="Default Profile">
                    <?php endif; ?>
                </div>
                <div><strong><?= htmlspecialchars($name) ?></strong></div>
                <small>ID: <?= htmlspecialchars($voter_id_string) ?></small>
            </div>
            <ul class="nav nav-pills flex-column mb-auto">
                <li><a class="nav-link" href="../index.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li><a class="nav-link" href="active_elections.php"><i class="bi bi-ballot me-2"></i>Active Elections</a></li>
                <li><a class="nav-link" href="voting_history.php"><i class="bi bi-clock-history me-2"></i>Voting History</a></li>
                <li><a class="nav-link" href="../voter/result.php"><i class="bi bi-bar-chart-line-fill me-2"></i>View Results</a></li>

                <li><a class="nav-link active" href="#"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
            </ul>
            <div class="mt-auto mb-3 px-2">
                <a href="../voter/logout.php" class="btn btn-outline-light w-100"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </div>
        </nav>
        <!-- Content -->
        <main class="col py-3 main-content">
            <h2 class="mb-4">My Profile</h2>
            <div class="profile-card">
                <div class="row">
                    <div class="col-md-4 text-center mb-3">
                        <div class="profile-avatar mx-auto mb-2">
                            <?php if (!empty($profile_photo) && file_exists("../" . $profile_photo)): ?>
                                <img src="<?= htmlspecialchars("../" . $profile_photo) ?>" width="75" height="75" class="rounded-circle" alt="Profile">
                            <?php else: ?>
                                <img src="../assets/pro.png" width="75" height="75" class="rounded-circle" alt="Default Profile">
                            <?php endif; ?>
                        </div>
                    

        <?php if (!empty($profile_photo)): ?>
    <form method="post" style="display:inline;">
        <input type="hidden" name="remove_photo" value="1">
        <button type="submit" class="btn btn-danger btn-sm mt-2" onclick="return confirm('Are you sure you want to remove your profile photo?');">
            <i class="bi bi-trash"></i> Remove Photo
        </button>
    </form>
<?php endif; ?>
                        <h4 class="mt-2"><?= htmlspecialchars($name) ?></h4>
                        
                        <p class="text-muted mb-1">Voter ID: <?= htmlspecialchars($voter_id_string) ?></p>
                        <p class="text-muted">Member Since: <?= date('F Y', strtotime($registered_at)) ?></p>
                    </div>
                    
                    <div class="col-md-8">
                        <h5 class="mb-3">Personal Information</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Name:</strong> <?= htmlspecialchars($name) ?></li>
                            <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($email) ?></li>
                            <li class="list-group-item"><strong>DOB:</strong> <?= htmlspecialchars($dob) ?></li>
                            <li class="list-group-item"><strong>Registered On:</strong> <?= date('d M Y', strtotime($registered_at)) ?></li>
                        </ul>
                        <div class="text-end mt-3">
    <a href="edit_profile.php" class="btn btn-outline-primary">
        <i class="bi bi-pencil"></i> Edit Profile
    </a>
</div>

                    </div>
                    
                </div>
            </div>
          
    <table class="table my-4">
        <thead>
            <tr>
                <th>Election</th>
                <th>Status</th>
                <th>Voting Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($voting_status as $row): ?>
            <tr>
             <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
              <td>
                <?php if ($row['voting_status'] === 'Voted'): ?>
                    <span class="badge bg-success">Voted</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">Not Voted</span>
                <?php endif; ?>
            </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
        </main>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_email = trim($_POST['email']);
    $profile_photo_path = $profile_photo; // existing, in case user doesn't upload a new one

    // File upload handling
    if (!empty($_FILES['profile_photo']['name'])) {
        $target_dir = "uploads/profile_photos/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $file_tmp = $_FILES['profile_photo']['tmp_name'];
        $file_name = basename($_FILES['profile_photo']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_types = ['jpg','jpeg','png','gif'];

        if (in_array($file_ext, $allowed_types)) {
            $new_file_name = "profile_" . $voter_id . "_" . time() . "." . $file_ext;
            $target_file = $target_dir . $new_file_name;
            if (move_uploaded_file($file_tmp, $target_file)) {
                $profile_photo_path = $target_file;
            }
        }
    }

    // Update in DB
    $stmt = $conn->prepare("UPDATE voters SET email = ?, profile_photo = ? WHERE voter_id = ?");
    $stmt->bind_param("sss", $new_email, $profile_photo_path, $voter_id);
    if ($stmt->execute()) {
        // Optionally, refresh values to show updated content
        $email = $new_email;
        $profile_photo = $profile_photo_path;
        echo '<div class="alert alert-success mt-2">Profile updated.</div>';
    } else {
        echo '<div class="alert alert-danger mt-2">Update failed.</div>';
    }
    $stmt->close();
}
?>