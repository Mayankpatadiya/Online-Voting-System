<?php
session_start();
require '../db.php';
require 'functions.php';

// Admin access check (adjust session variable as per your login system)

// Get election ID from URL and validate
$election_id = isset($_GET['election']) ? intval($_GET['election']) : 0;
if ($election_id <= 0) {
    die("Invalid election selected.");
}

// Fetch election name for display
$stmt = $conn->prepare("SELECT name FROM elections WHERE id = ?");
$stmt->bind_param("i", $election_id);
$stmt->execute();
$stmt->bind_result($election_name);
if (!$stmt->fetch()) {
    $stmt->close();
    die("Election not found.");
}
$stmt->close();


// Fetch positions for election
$positions = [];
$pos_stmt = $conn->prepare("SELECT id, name FROM positions");
$pos_stmt->execute();
$pos_result = $pos_stmt->get_result();
while ($pos = $pos_result->fetch_assoc()) {
    $positions[] = $pos;
}
$pos_stmt->close();



$error = '';
$success = '';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
      $position_id = isset($_POST['position_id']) ? intval($_POST['position_id']) : 0;

    // Basic validation
    if (empty($name)) {
        $error = "Candidate name is required.";
    } elseif (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = "Candidate photo is required.";
    } else {
        // Handle file upload
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
            $error = "Invalid photo format. Allowed: JPG, PNG, GIF.";
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $error = "Photo size must be less than 2MB.";
        } else {
            // Upload folder
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $fileExt = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            // Create unique filename
            $filename = uniqid('candidate_', true) . '.' . $fileExt;

            $destination = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                // Insert candidate into DB
             $stmt = $conn->prepare("INSERT INTO candidates (election_id, position_id, name, photo, bio) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $election_id, $position_id, $name, $filename, $bio);

                if ($stmt->execute()) {
                    $candidate_id = $stmt->insert_id;

                    // Get position name for audit log
                    $pos_name = '';
                    foreach ($positions as $pos) {
                        if ($pos['id'] === $position_id) {
                            $pos_name = $pos['name'];
                            break;
                        }
                    }

                    // Log the candidate addition
                    insertAuditLog($conn, $_SESSION['admin_username'], 'ADD_CANDIDATE', [
                        'candidate_id' => $candidate_id,
                        'candidate_name' => $name,
                        'election_id' => $election_id,
                        'election_name' => $election_name,
                        'position' => $pos_name,
                        'has_photo' => true
                    ], $_SESSION['admin_id']);

                    $success = "Candidate added successfully!";
                    header("Location: manage_candidates.php?election=$election_id&added=1");
                    // Clear form fields
                    $name = $bio = '';
                } else {
                    $error = "Database error. Please try again.";
                    // Remove uploaded file if DB insert failwed
                    unlink($destination);
                }
                $stmt->close();
            } else {
                $error = "Failed to upload photo. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Candidate – <?= htmlspecialchars($election_name) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            min-height: 100vh;
        }
        .add-candidate-card {
            max-width: 600px;
            background: #fff;
            margin: 40px auto;
            padding: 2rem 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="add-candidate-card">
        <h3 class="mb-4 text-primary">
            <i class="bi bi-plus-circle me-2"></i>Add Candidate to <?= htmlspecialchars($election_name) ?>
        </h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label">Candidate Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required
                    value="<?= htmlspecialchars($name ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="bio" class="form-label">Candidate Bio / Platform</label>
                <textarea class="form-control" id="bio" name="bio" rows="4"><?= htmlspecialchars($bio ?? '') ?></textarea>
            </div>
            <div class="mb-3">
    <label for="position_id" class="form-label">Position <span class="text-danger">*</span></label>
    <select class="form-select" id="position_id" name="position_id" required>
        <option value="">Select Position</option>
        <?php foreach ($positions as $pos): ?>
            <option value="<?= $pos['id'] ?>" <?= (isset($_POST['position_id']) && $_POST['position_id'] == $pos['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($pos['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

            <div class="mb-3">
                <label for="photo" class="form-label">Candidate Photo <span class="text-danger">*</span></label>
                <input type="file" class="form-control" id="photo" name="photo" accept="image/*" required>
                <div class="form-text">Allowed formats: JPG, PNG, GIF. Max size: 2MB.</div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="manage_candidates.php?election=<?= $election_id ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Candidates
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Add Candidate
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
