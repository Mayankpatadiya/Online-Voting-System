<?php
session_start();

require '../db.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'date_desc';

// Prepare SQL WHERE clause with search
$whereClauses = [];
$params = [];
$paramTypes = '';

if ($search !== '') {
    $whereClauses[] = "(name LIKE ? OR email LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $paramTypes .= 'ss';
}

// Sorting
$allowedSorts = [
    'name_asc' => 'name ASC',
    'name_desc' => 'name DESC',
    'date_asc' => 'registered_at ASC',
    'date_desc' => 'registered_at DESC'
];
$orderBy = $allowedSorts[$sort] ?? 'registered_at DESC';

// Pagination: define how many items per page
$itemsPerPage = 10;

// Get current page from GET, default 1
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $itemsPerPage;

// Count total filtered rows for pagination
$whereSQL = count($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

$countSql = "SELECT COUNT(*) FROM voters $whereSQL";
$countStmt = $conn->prepare($countSql);

if ($paramTypes) {
    $countStmt->bind_param($paramTypes, ...$params);
}

$countStmt->execute();
$countStmt->bind_result($totalRows);
$countStmt->fetch();
$countStmt->close();

$totalPages = ceil($totalRows / $itemsPerPage);

// Fetch actual voter rows
$sql = "SELECT id, name, email, voter_id, dob, registered_at FROM voters $whereSQL ORDER BY $orderBy LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if ($paramTypes) {
    // Add types for limit and offset 'ii'
    $fullParamTypes = $paramTypes . 'ii';

    // Merge all params into one array
    $allParams = array_merge($params, [$itemsPerPage, $offset]);

    // Bind parameters properly
    $stmt->bind_param($fullParamTypes, ...$allParams);
} else {
    $stmt->bind_param('ii', $itemsPerPage, $offset);
}


$stmt->execute();
$result = $stmt->get_result();

?>
<?php if (isset($_GET['msg'])): ?>
    <?php
    $alertClass = 'alert-info';
    $message = '';

    switch ($_GET['msg']) {
        case 'added':
            $alertClass = 'alert-success';
            $message = 'New voter added successfully.';
            break;
        case 'updated':
            $alertClass = 'alert-success';
            $message = 'Voter information updated successfully.';
            break;
        case 'deleted':
            $alertClass = 'alert-danger';
            $message = 'Voter deleted successfully.';
            break;
        case 'error':
            $alertClass = 'alert-danger';
            $message = 'An error occurred. Please try again.';
            break;
        case 'invalidid':
            $alertClass = 'alert-warning';
            $message = 'Invalid voter ID.';
            break;
        case 'notfound':
            $alertClass = 'alert-warning';
            $message = 'Voter not found.';
            break;
        default:
            $alertClass = 'alert-info';
            $message = 'Action completed.';
    }
    ?>
    <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Voters – Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
</head>
<body>
<div class="container py-4">
    <h1 class="mb-4"><i class="bi bi-person-lines-fill me-2"></i>Manage Voters</h1>
    <a href="add_voter.php" class="btn btn-primary mb-3">
        <i class="bi bi-plus-circle me-1"></i> Add New Voter
    </a>
    <div class="table-responsive">
        <form method="get" class="row g-3 mb-3 align-items-center">
    <div class="col-auto">
        <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <div class="col-auto">
        <select name="sort" class="form-select">
            <option value="name_asc" <?= ($_GET['sort'] ?? '') === 'name_asc' ? 'selected' : '' ?>>Name ↑</option>
            <option value="name_desc" <?= ($_GET['sort'] ?? '') === 'name_desc' ? 'selected' : '' ?>>Name ↓</option>
            <option value="date_asc" <?= ($_GET['sort'] ?? '') === 'date_asc' ? 'selected' : '' ?>>Registration Date ↑</option>
            <option value="date_desc" <?= ($_GET['sort'] ?? '') === 'date_desc' ? 'selected' : '' ?>>Registration Date ↓</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Filter</button>
    </div>
</form>

        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Voter Id</th>
                    <th>Date of Birth</th>
                    <th>Date Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
             
                <?php if ($result && $result->num_rows > 0): ?>
    <?php $count = $offset + 1; ?>
    <?php while ($voter = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $count++ ?></td>
            <td><?= htmlspecialchars($voter['name']) ?></td>
            <td><?= htmlspecialchars($voter['email']) ?></td>
            <td><?= htmlspecialchars($voter['voter_id']) ?> </td>
            <td><?= date('M d, Y', strtotime($voter['dob'])) ?></td>
            <td><?= date('M d, Y h:i A', strtotime($voter['registered_at'])) ?></td>
            <td>
               
                                <a href="edit_voter.php?id=<?= $voter['id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="delete_voter.php?id=<?= $voter['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure to delete this voter?')" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="5" class="text-center text-muted">No voters found.</td>
    </tr>
<?php endif; ?>

            </tbody>
        </table>
    </div>

    <a href="admin_dashboard.php" class="btn btn-secondary mt-3">
        <i class="bi bi-arrow-left-circle me-1"></i> Back to Dashboard
    </a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close DB connection
$conn->close();
?>

