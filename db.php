<?php
$host = "localhost";
$user = "root";
$pass = "mayanksql@28";
$db   = "votingsystem"; // use your DB name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

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


?>