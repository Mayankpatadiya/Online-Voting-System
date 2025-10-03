<?php
session_start();
ob_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../db.php'; // Update path if needed
require 'functions.php';


$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username !== '' && $password !== '') {
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $db_username, $db_password);
            $stmt->fetch();

            // Direct comparison (no hashing)
            if ($password === $db_password) {
                $_SESSION['admin_username'] = $db_username;
                $_SESSION['admin_id'] = $id;
                
                // Log successful login
                insertAuditLog($conn, $db_username, 'ADMIN_LOGIN', [
                    'status' => 'success',
                    'method' => 'password'
                ], $id);
                
                header("Location: admin_dashboard.php");
                exit;
            } else {
                // Log failed login attempt
                insertAuditLog($conn, $username, 'ADMIN_LOGIN_FAILED', [
                    'status' => 'failed',
                    'reason' => 'invalid_password'
                ]);
                $error = "Invalid username or password.";
            }
        } else {
            // Log failed login attempt for non-existent user
            insertAuditLog($conn, $username, 'ADMIN_LOGIN_FAILED', [
                'status' => 'failed',
                'reason' => 'invalid_username'
            ]);
            $error = "Invalid username or password.";
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }
}




?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { background: #e8eafc; min-height: 100vh; }
        .login-card { background: #fff; padding: 2.5rem 2rem; border-radius: 15px; max-width: 400px; margin: 8vh auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card mx-auto">
            <h3 class="mb-4 text-primary text-center">Admin Login</h3>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form  method="post" autocomplete="off">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="off">
                </div>
                <button class="btn btn-primary w-100" type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
