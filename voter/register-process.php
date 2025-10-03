<?php
require '../db.php';
header('Content-Type: application/json');
function generateVoterId($conn) {
    $prefix = 'VTR';
    $year = date('Y');
    
    do {
        // Generate 6 random uppercase alphanumeric characters
        $randomPart = strtoupper(substr(bin2hex(random_bytes(3)), 0, 3));
        $voterId = $prefix . $year . $randomPart;

        // Check uniqueness
        $stmt = $conn->prepare("SELECT id FROM voters WHERE voter_id = ?");
        $stmt->bind_param("s", $voterId);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
    } while ($exists);

    return $voterId;
}

// Sanitize inputs
$name = trim($_POST['name']);
$dob = $_POST['dob'];
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

// Validate mandatory fields
if (empty($name) || empty($dob) || empty($email) || empty($password) || empty($confirmPassword)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM voters WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered.']);
    exit;
}
$stmt->close();

// Generate unique voterId
$voterId = generateVoterId($conn);

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert new voter
$stmt = $conn->prepare("INSERT INTO voters (name, voter_id, dob, email, password) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $voterId, $dob, $email, $hashedPassword);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => "Registration successful! Your Voter ID is: $voterId. Redirecting...",
        'redirect' => 'login.php'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed. Try again.']);
}
?>
