<?php
session_start();


if (!isset($_SESSION['voter_id'])) {
  header("Location:login.php");
  exit();
}


require '../db.php';

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


// Get election_id from GET or SESSION
$voter_id =$_SESSION['voter_id'];
$election_id = isset($_GET['election_id']) ? intval($_GET['election_id']) : 0;
// Fetch elections for dropdown (if needed)
$elections = [];
$election_sql = "SELECT id, name FROM elections WHERE status = 'active' ORDER BY start_date DESC";
$election_result = $conn->query($election_sql);
if ($election_result && $election_result->num_rows > 0) {
    while ($row = $election_result->fetch_assoc()) {
        $elections[] = $row;
    }
}

// Fetch candidates for the selected election
$candidates = [];
if ($election_id) {
$stmt = $conn->prepare("SELECT id, name, photo, bio FROM candidates WHERE election_id = ?");
    $stmt->bind_param("i", $election_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }
    $stmt->close();
}

$has_voted = false;
if ($election_id && isset($_SESSION['voter_id'])) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM votes WHERE voter_id = ? AND position_id = ?");
    $stmt->bind_param("ii", $voter_id, $election_id);
    $stmt->execute();
    $stmt->bind_result($vote_count);
    $stmt->fetch();
    $stmt->close();
    $has_voted = ($vote_count > 0);
}
$name = $_SESSION['name'];
$voter_id = $_SESSION['voter_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vote - Online Voting System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    body { background: linear-gradient(120deg, #e0e7ff 0%, #fff 100%); min-height: 100vh; }
    .fixed-header, .fixed-footer { background: linear-gradient(90deg, #6a82fb 0%, #fc5c7d 100%); color: #fff; padding: 1.2rem 1rem; text-align: center; position: sticky; z-index: 100; }
    .fixed-header { top: 0; border-bottom-left-radius: 1.2rem; border-bottom-right-radius: 1.2rem; }
    .fixed-footer { bottom: 0; border-top-left-radius: 1.2rem; border-top-right-radius: 1.2rem; }
    .candidate-card.selected { border: 2px solid #6a82fb !important; box-shadow: 0 0 0 4px #e0e7ff; }
    .candidate-check { position: absolute; top: 12px; right: 16px; font-size: 1.7rem; color: #38d39f; display: none; }
    .candidate-card.selected .candidate-check { display: block; }
    .vote-btn { background: linear-gradient(90deg, #6a82fb 0%, #fc5c7d 100%); border: none; color: #fff; border-radius: 25px; padding: 0.7rem 2.5rem; font-weight: 600; font-size: 1.1rem; margin-top: 1.5rem; box-shadow: 0 2px 10px rgba(106,130,251,0.12); transition: background 0.2s; }
    .vote-btn:active, .vote-btn:focus { background: #fc5c7d; color: #fff; }
    .back-btn { margin-left: 1rem; border-radius: 25px; }
  </style>
</head>
<body>
  <div class="fixed-header">
    <h2 class="mb-0 fw-semibold">Cast Your Vote</h2>
    <div class="fs-6">Welcome, <?= htmlspecialchars($name) ?> (ID: <?= htmlspecialchars($voter_id) ?>)</div>
    <div class="fs-6">Select your preferred candidate below and submit your vote. You can vote only once.</div>
  </div>

  <div class="container py-4">
    <!-- Election selection dropdown -->
    <form method="get" class="mb-4 text-center">
      <label for="election_id" class="form-label fw-semibold">Select Election:</label>
      <select name="election_id" id="election_id" class="form-select d-inline-block w-auto" onchange="this.form.submit()" required>
        <option value="">-- Select --</option>
        <?php foreach ($elections as $election): ?>
          <option value="<?= $election['id'] ?>" <?= $election['id'] == $election_id ? 'selected' : '' ?>>
            <?= htmlspecialchars($election['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>

    <?php if ($election_id): ?>
    <form id="voteForm" autocomplete="off">
<input type="hidden" name="election_id" value="<?= htmlspecialchars($election_id) ?>">
      <div class="row g-4 justify-content-center">
        <?php foreach ($candidates as $candidate): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
          <label class="card candidate-card h-100 shadow-sm position-relative p-3 w-100" tabindex="0" style="cursor:pointer;">
            <input type="radio" name="candidate_id" value="<?= $candidate['id'] ?>" class="d-none candidate-radio" required>
            <img src="../uploads/<?= htmlspecialchars($candidate['photo']) ?>" alt="Photo" class="card-img-top rounded-circle mx-auto d-block mt-2" style="width:90px;height:90px;object-fit:cover;border:3px solid #6a82fb;">
            <div class="card-body text-center">
              <h5 class="card-title mb-2"><?= htmlspecialchars($candidate['name']) ?></h5>
              <p class="card-title mb-2"><?= htmlspecialchars($candidate['bio']) ?></p>
            </div>
            <span class="candidate-check"><i class="bi bi-check-circle-fill"></i></span>
          </label>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="text-center">
        <div id="voteMsg" class="mb-3 text-danger"></div>
        <button type="submit" class="btn vote-btn">Submit Vote</button>
        <a href="../index.php" class="btn btn-outline-secondary back-btn">Back to Dashboard</a>
      </div>
    </form>
    <?php elseif (!empty($elections)): ?>
      <div class="alert alert-info text-center">Please select an election to view candidates.</div>
    <?php else: ?>
      <div class="alert alert-warning text-center">No elections available.</div>
    <?php endif; ?>
  </div>

  <div class="fixed-footer">
    <span class="fs-6">Your vote is confidential and will be counted securely.</span>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
        // AJAX vote submission
   $('#voteForm').on('submit', function(e) {
  e.preventDefault();
  $.ajax({
    url: 'submit_vote.php',
    type: 'POST',
    data: $(this).serialize(),
    dataType: 'json',
    success: function(response) {
      if (response.success) {
        alert(response.message);
        window.location.href = '../index.php'; // or any redirect
      } else {
        $('#voteMsg').text(response.message);
      }
    }
  });
});


    // Card selection effect
    $(document).on('change', '.candidate-radio', function() {
      $('.candidate-card').removeClass('selected');
      $(this).closest('.candidate-card').addClass('selected');
    });
    // Also allow clicking anywhere on the card
    $(document).on('click keypress', '.candidate-card', function(e) {
      if (e.type === 'click' || (e.type === 'keypress' && (e.key === ' ' || e.key === 'Enter'))) {
        $(this).find('input[type=radio]').prop('checked', true).trigger('change');
      }
    });


  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
