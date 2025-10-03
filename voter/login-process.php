<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Something went wrong'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $voterId = $_POST['voterId'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($voterId) || empty($password)) {
        $response['message'] = "Please fill in all fields.";
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM voters WHERE voter_id = ?");
    $stmt->bind_param("s", $voterId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['voter_id'] = $user['voter_id'];
            $_SESSION['name'] = $user['name'];
            // $_SESSION['has_voted'] = $user['has_voted'];

            $response['success'] = true;
            $response['message'] = 'Login successful';
        } else {
            $response['message'] = 'Incorrect password.';
        }
    } else {
        $response['message'] = 'Voter ID not found.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
