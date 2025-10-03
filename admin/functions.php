<?php
/**
 * Insert an entry into the audit log
 * 
 * @param mysqli $conn Database connection
 * @param string $username Username of the user performing the action
 * @param string $action Type of action being performed (e.g., 'login', 'vote', 'create_election')
 * @param mixed $details Additional details about the action (will be JSON encoded)
 * @param int|null $user_id ID of the user (optional)
 * @return bool True on success, False on failure
 */
function insertAuditLog($conn, $username, $action, $details = null, $user_id = null) {
    // Convert details to JSON if it's an array or object
    if (is_array($details) || is_object($details)) {
        $details = json_encode($details);
    }

    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, username, action, details, timestamp) 
                           VALUES (?, ?, ?, ?, NOW())");
    if (!$stmt) {
        error_log("Prepare failed in insertAuditLog: " . $conn->error);
        return false;
    }

    $stmt->bind_param("isss", $user_id, $username, $action, $details);
    
    $res = $stmt->execute();
    if (!$res) {
        error_log("Execute failed in insertAuditLog: " . $stmt->error);
    }
    
    $stmt->close();
    return $res;
}


?>
