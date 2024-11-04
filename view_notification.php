<?php
session_start();
require 'db_connect.php'; // Include the database connection

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) && !isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Fetch user ID
$user_id = $_SESSION['user_id'] ?? 0; // Use a default value if user_id is not set

// Check if a notification ID is provided to update its status
if (isset($_GET['id'])) {
    $notification_id = $_GET['id'];

    try {
        // Update the notification status to 'read'
        $updateQuery = "UPDATE notifications SET status = 'read' WHERE notification_id = :notification_id AND user_id = :user_id";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->bindParam(':notification_id', $notification_id, PDO::PARAM_INT);
        $updateStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $updateStmt->execute();
    } catch (PDOException $e) {
        die('DB ERROR: ' . $e->getMessage());
    }
}

try {
    // Prepare the SQL statement to fetch notifications
    $query = "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all notifications
    $allNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch user details
    if ($user_id > 0) {
        $userQuery = "SELECT user_first_name, user_last_name FROM task_user WHERE user_id = :user_id";
        $userStmt = $pdo->prepare($userQuery);
        $userStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $userStmt->execute();
        $userDetails = $userStmt->fetch(PDO::FETCH_ASSOC);
        $firstName = $userDetails['user_first_name'] ?? 'Guest';
        $lastName = $userDetails['user_last_name'] ?? '';
    } else {
        $firstName = 'Guest';
        $lastName = '';
    }
} catch (PDOException $e) {
    die('DB ERROR: ' . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="asset/css/styles.css" rel="stylesheet" />
    <title>View Notifications</title>
</head>
<body>
    <?php include 'header.php'; ?> <!-- Include the header file -->

    <div class="container mt-4">
        <h1>Notifications</h1>
        <p class="small">Logged in as: <?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></p>

        <?php if (empty($allNotifications)): ?>
            <div class="alert alert-info">No notifications found.</div>
        <?php else: ?>
            <h3>All Notifications</h3>
            <ul class="list-group">
                <?php foreach ($allNotifications as $notification): ?>
                    <li class="list-group-item <?php echo ($notification['status'] === 'unread') ? 'font-weight-bold' : ''; ?>">
                        <?php echo htmlspecialchars($notification['notification_text']); ?>
                        <small class="text-muted"> - <?php echo htmlspecialchars($notification['created_at']); ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
