<?php
session_start();
if (!isset($_SESSION['voter_id'])) {
    header("Location: voter/login.php");
    exit;   
}
require '../db.php';

$voter_id_string = $_SESSION['voter_id']; // voter_id public string

// Fetch voter details, including profile_photo:
$stmt = $conn->prepare("SELECT name, email, dob, registered_at, profile_photo FROM voters WHERE voter_id = ?");
$stmt->bind_param("s", $voter_id_string);
$stmt->execute();
$stmt->bind_result($name, $email, $dob, $registered_at, $profile_photo);
$stmt->fetch();
$stmt->close();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_email = trim($_POST['email']);
    $profile_photo_path = $profile_photo; // Default: existing photo

    // Handle the upload if a new photo is provided
    if (!empty($_FILES['profile_photo']['name'])) {
        $target_dir = "../uploads/profile_photos/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_tmp = $_FILES['profile_photo']['tmp_name'];
        $file_name = basename($_FILES['profile_photo']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_types = ['jpg','jpeg','png','gif'];
        if (in_array($file_ext, $allowed_types)) {
            $new_file_name = "profile_" . $voter_id_string . "_" . time() . "." . $file_ext;
            $target_file = $target_dir . $new_file_name;
            if (move_uploaded_file($file_tmp, $target_file)) {
                // Store the relative path in the database
                $profile_photo_path = "uploads/profile_photos/" . $new_file_name;
            } else {
                $error = "Photo upload failed.";
            }
        } else {
            $error = "Only jpg, jpeg, png, gif files are allowed.";
        }
    }

    // Update in DB only if no upload error
    if (!$error) {
        $stmt = $conn->prepare("UPDATE voters SET email = ?, profile_photo = ? WHERE voter_id = ?");
        $stmt->bind_param("sss", $new_email, $profile_photo_path, $voter_id_string);
        if ($stmt->execute()) {
            $success = "Profile updated successfully.";
            $email = $new_email;
            $profile_photo = $profile_photo_path;
        } else {
            $error = "Update failed. Please try again.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - Online Voting System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa, #c3cfe2); min-height: 100vh; }
        .main-content { max-width: 450px; margin: 40px auto; }
        .profile-form-card { border-radius: 1rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 2rem; background: #fff; }
        .profile-avatar { width: 80px; height: 80px; margin: 0 auto 12px; background: #e0e0e0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; }
        .back-link { text-decoration: none; margin-bottom: 1em; display: inline-block;}
    </style>
</head>
<body>
<div class="container main-content">
    <a href="profile.php" class="back-link"><i class="bi bi-arrow-left"></i> Back to Profile</a>
    <div class="profile-form-card">
        <h2 class="mb-3">Edit Profile</h2>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3 text-center">
                <div class="profile-avatar">
                    <?php if (!empty($profile_photo) && file_exists("../" . $profile_photo)): ?>
                        <img src="<?= htmlspecialchars("../" . $profile_photo) ?>" alt="Profile Photo" class="rounded-circle" width="80" height="80">
                    <?php else: ?>
                        <img src="../assets/pro.png" alt="Default Profile" class="rounded-circle" width="80" height="80">
                    <?php endif; ?>
                </div>
                <small class="text-muted">Recommended: square image, ≤ 2 MB.</small>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label"><strong>Email address</strong></label>
                <input type="email" class="form-control" name="email" id="email"
                       value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="mb-3">
                <label for="profile_photo" class="form-label"><strong>Profile Photo</strong></label>
                <input type="file" class="form-control" name="profile_photo" id="profile_photo" accept="image/*">
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i>Save Changes</button>
        </form>
        <div class="mt-4 text-center small text-muted">
            <strong>Name:</strong> <?= htmlspecialchars($name) ?><br>
            <strong>Voter ID:</strong> <?= htmlspecialchars($voter_id_string) ?><br>
            <strong>DOB:</strong> <?= htmlspecialchars($dob) ?><br>
            <strong>Member Since:</strong> <?= date('d M Y', strtotime($registered_at)) ?>
        </div>
    </div>
</div>
</body>
</html>
