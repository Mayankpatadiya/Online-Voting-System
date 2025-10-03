<?php
session_start();

require '../db.php';
require 'functions.php'; // Ensure insertAuditLog is available here or define the function in this file

// Protect page: only logged-in admins can view
if (!isset($_SESSION['admin_username'])) {
    header('Location: admin_login.php');
    exit();
}

$itemsPerPage = 20;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $itemsPerPage;

$search = trim($_GET['search'] ?? '');
$whereSQL = '';
$params = [];
$paramTypes = '';

if ($search !== '') {
    $whereSQL = "WHERE action LIKE ? OR username LIKE ?";
    $params = ["%$search%", "%$search%"];
    $paramTypes = 'ss';
}

// 1) Count total rows
$countSql = "SELECT COUNT(*) FROM audit_log $whereSQL";
$countStmt = $conn->prepare($countSql);
if (!$countStmt) {
    die("Prepare failed: " . $conn->error);
}

if ($paramTypes) {
    $countStmt->bind_param($paramTypes, ...$params);
}
$countStmt->execute();
$countStmt->bind_result($totalRows);
$countStmt->fetch();
$countStmt->close();

$totalPages = max(1, ceil($totalRows / $itemsPerPage));

// 2) Fetch audit log data with LIMIT and OFFSET
$sql = "SELECT id, user_id, username, action, details, timestamp 
        FROM audit_log $whereSQL 
        ORDER BY timestamp DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

if ($paramTypes) {
    // bind search params + limit and offset
    $fullParamTypes = $paramTypes . 'ii';
    $paramsWithLimit = array_merge($params, [$itemsPerPage, $offset]);
    // MySQLi requires references for bind_param
    $refs = [];
    foreach ($paramsWithLimit as $key => $value) {
        $refs[$key] = &$paramsWithLimit[$key];
    }
    $stmt->bind_param($fullParamTypes, ...$refs);
} else {
    $stmt->bind_param("ii", $itemsPerPage, $offset);
}

$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Audit Log - Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<!-- Bootstrap CSS & Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
</head>
<body>
<div class="container py-4" style="max-width:1200px;">
<h1 class="mb-4"><i class="bi bi-clipboard-data me-2"></i>Audit Log</h1>

<form method="get" class="mb-3 row g-3 align-items-center">
    <div class="col-auto">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search by user or action...">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="audit_log.php" class="btn btn-outline-secondary ms-2">Reset</a>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['action']) ?></td>
                    <td>
                       <?php
if (!empty($row['details'])) {
    $detailsArray = json_decode($row['details'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($detailsArray)) {
        // nicely format array, e.g. as JSON or print_r within <pre>
        echo '<pre style="white-space: pre-wrap; word-break: break-word; margin:0;">' 
            . htmlspecialchars(print_r($detailsArray, true)) 
            . '</pre>';
    } else {
        // fallback: just output the raw string safely
        echo htmlspecialchars($row['details']);
    }
} else {
    echo '<em>No details</em>';
}
?>

                    </td>
                    <td><?= date('M d, Y h:i:s A', strtotime($row['timestamp'])) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" class="text-center text-muted">No audit logs found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<nav>
    <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="page-link" aria-label="Previous">&laquo; Prev</a>
            </li>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" class="page-link"><?= $p ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="page-link" aria-label="Next">Next &raquo;</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<a href="admin_dashboard.php" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left-circle me-1"></i> Back to Dashboard
</a>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
