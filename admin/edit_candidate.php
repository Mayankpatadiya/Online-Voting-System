<?php
session_start();
require '../db.php';

// Admin access check (adjust according to your system)
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Get candidate ID to edit from GET
$candidate_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($candidate_id <= 0) {
    die("Invalid candidate ID.");
}

// Fetch candidate info including election_id (needed to fetch positions & for update)
$stmt = $conn->prepare("SELECT election_id, position_id, name, photo, bio FROM candidates WHERE id = ?");
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$stmt->bind_result($election_id, $position_id, $name, $photo, $bio);
if (!$stmt->fetch()) {
    $stmt->close();
    die("Candidate not found.");
}
$stmt->close();

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

// Fetch positions for the election (assuming all positions shown; adapt if positions vary per election)
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $new_position_id = isset($_POST['position_id']) ? intval($_POST['position_id']) : 0;

    // Validate name
    if (empty($name)) {
        $error = "Candidate name is required.";
    }

    // Validate position
    $position_ids = array_column($positions, 'id');
    if (!in_array($new_position_id, $position_ids)) {
        $error = "Invalid position selected.";
    }

    // Handle photo upload if new photo provided
    $new_photo_filename = $photo; // default to old photo if no upload

    if (!$error && isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
            $error = "Invalid photo format. Allowed: JPG, PNG, GIF.";
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $error = "Photo size must be less than 2MB.";
        } else {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $image_info = @getimagesize($_FILES['photo']['tmp_name']);
            if ($image_info === false) {
                $error = "Uploaded file is not a valid image.";
            }

            if (!$error) {
                $fileExt = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $new_photo_filename = uniqid('candidate_', true) . '.' . $fileExt;
                $destination = $uploadDir . $new_photo_filename;

                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                    $error = "Failed to upload photo. Please try again.";
                } else {
                    // Delete old photo file if it exists and is different from new
                    if ($photo && file_exists($uploadDir . $photo) && $photo !== $new_photo_filename) {
                        @unlink($uploadDir . $photo);
                    }
                }
            }
        }
    }

    // If no error so far, update candidate info in DB
    if (!$error) {
        $update_stmt = $conn->prepare("UPDATE candidates SET position_id = ?, name = ?, photo = ?, bio = ? WHERE id = ?");
        $update_stmt->bind_param("isssi", $new_position_id, $name, $new_photo_filename, $bio, $candidate_id);
        if ($update_stmt->execute()) {
            $success = "Candidate updated successfully.";
            // Update current values for form display
            $position_id = $new_position_id;
            $photo = $new_photo_filename;
        } else {
            $error = "Database error. Please try again.";
            // If new photo was uploaded but DB update fails, delete the new photo file to avoid orphaned files
            if ($new_photo_filename !== $photo && file_exists($uploadDir . $new_photo_filename)) {
                @unlink($uploadDir . $new_photo_filename);
            }
        }
        $update_stmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Candidate – <?= htmlspecialchars($name) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            min-height: 100vh;
        }
        .edit-candidate-card {
            max-width: 600px;
            background: #fff;
            margin: 40px auto;
            padding: 2rem 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }
        .candidate-photo {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            object-fit: cover;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="edit-candidate-card">
        <h3 class="mb-4 text-primary">
            <i class="bi bi-pencil-square me-2"></i>Edit Candidate: <?= htmlspecialchars($name) ?>
        </h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" novalidate>
            <div class="mb-3">
                <label for="name" class="form-label">Candidate Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($name) ?>">
            </div>

            <div class="mb-3">
                <label for="bio" class="form-label">Candidate Bio / Platform</label>
                <textarea class="form-control" id="bio" name="bio" rows="4"><?= htmlspecialchars($bio) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="position_id" class="form-label">Position <span class="text-danger">*</span></label>
                <select class="form-select" id="position_id" name="position_id" required>
                    <option value="">Select Position</option>
                    <?php foreach ($positions as $pos): ?>
                        <option value="<?= $pos['id'] ?>" <?= ($position_id == $pos['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pos['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Current Photo</label><br>
                <?php if ($photo && file_exists('../uploads/' . $photo)): ?>
                    <img src="../uploads/<?= htmlspecialchars($photo) ?>" alt="Candidate Photo" class="candidate-photo" />
                <?php else: ?>
                    <p>No photo available.</p>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="photo" class="form-label">Change Photo (optional)</label>
                <input type="file" class="form-control" id="photo" name="photo" accept="image/jpeg,image/png,image/gif">
                <div class="form-text">Allowed formats: JPG, PNG, GIF. Max size: 2MB. Leave empty to keep current photo.</div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="manage_candidates.php?election=<?= $election_id ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Candidates
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Update Candidate
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
