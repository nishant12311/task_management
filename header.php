<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Static Navigation - SB Admin</title>
        <link href="asset/css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" type="text/css" href="asset/vendor/datatables/dataTables.bootstrap5.min.css"/>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>        
    </head>
    <body>
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="index.html">Start Bootstrap</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Search-->
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            </form>
            
            <!-- Notification Icon -->
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="notificationDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fa-fw"></i>
                        <!-- Notification Badge -->
                        <?php
                        include 'db_connect.php'; // Include your DB connection file

                        // Check if user_id is set in session
                        if (isset($_SESSION['user_id'])) {
                            $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session
                            $stmt = $pdo->prepare("SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = ? AND status = 'unread'");
                            $stmt->execute([$user_id]);
                            $notificationCount = $stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
                        } else {
                            $notificationCount = 0; // Default to 0 if user_id is not set
                        }
                        ?>
                        <span class="badge bg-danger"><?= $notificationCount; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                        <?php
                        if (isset($user_id)) {
                            // Fetch unread notifications
                            $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND status = 'unread' ORDER BY created_at DESC LIMIT 5");
                            $stmt->execute([$user_id]);
                            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if (count($notifications) > 0) {
                                foreach ($notifications as $notification) {
                                    echo '<li><a class="dropdown-item" href="view_notification.php?id=' . $notification['notification_id'] . '">' . htmlspecialchars($notification['notification_text']) . '</a></li>';
                                }
                            } else {
                                echo '<li><a class="dropdown-item" href="#">No new notifications</a></li>';
                            }
                        } else {
                            echo '<li><a class="dropdown-item" href="#">Please log in to see notifications</a></li>';
                        }
                        ?>
                    </ul>
                </li>

                <!-- User Profile Dropdown -->
                <li class="nav-item dropdown">
                    <?php
                    if (isset($_SESSION['user_logged_in'])) {
                    ?>
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?php echo $_SESSION['user_image']; ?>" width="40" class="rounded-circle" />
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="user_profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    <?php 
                    } else {
                    ?>
                        <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="admin_change_password.php">Change Password</a></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    <?php
                    }
                    ?>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <?php
                            if (isset($_SESSION['admin_logged_in'])) {
                            ?>
                                <a class="nav-link" href="dashboard.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                    Dashboard
                                </a>
                                <a class="nav-link" href="department.php">
                                    <div class="sb-nav-link-icon"><i class="far fa-building"></i></div>
                                    Department
                                </a>
                                <a class="nav-link" href="user.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-user-md"></i></div>
                                    User
                                </a>
                                <a class="nav-link" href="task.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-notes-medical"></i></div>
                                    Task
                                </a>
                                <a class="nav-link" href="admin_change_password.php">
                                    <div class="sb-nav-link-icon"><i class="far fa-id-card"></i></div>
                                    Change Password
                                </a>
                                <a class="nav-link" href="logout.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                                    Logout
                                </a>
                            <?php
                            } else {
                            ?>
                                <a class="nav-link" href="task.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-notes-medical"></i></div>
                                    Task
                                </a>
                                <a class="nav-link" href="user_profile.php">
                                    <div class="sb-nav-link-icon"><i class="far fa-id-card"></i></div>
                                    Profile
                                </a>
                                <a class="nav-link" href="user_change_password.php">
                                    <div class="sb-nav-link-icon"><i class="far fa-id-card"></i></div>
                                    Change Password
                                </a>
                                <a class="nav-link" href="logout.php">
                                    <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                                    Logout
                                </a>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        Start Bootstrap
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
