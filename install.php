<?php

session_start();

// Check if installation is already done
if (file_exists('config.php')) {
    header('Location: index.php');
    exit;
}

$errors = [];
$install_step = 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['db_host'])) {
    $host = trim($_POST['db_host']);
    $db = trim($_POST['db_name']);
    $user = trim($_POST['db_user']);
    $pass = trim($_POST['db_pass']);

    if (empty($host)) {
        $errors[] = "Database host is required.";
    }
    if (empty($db)) {
        $errors[] = "Database name is required.";
    }
    if (empty($user)) {
        $errors[] = "Database user is required.";
    }
    if (empty($pass)) {
        $errors[] = "Database password is required.";
    }

    if (empty($errors)) {
        try {
            $pdo = new PDO("mysql:host=$host", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS $db");
            $pdo->exec("USE $db");

            // Create tables
            $tables = [
                "CREATE TABLE IF NOT EXISTS task_admin (
                    admin_id INT AUTO_INCREMENT PRIMARY KEY,
                    admin_email VARCHAR(255) NOT NULL,
                    admin_password VARCHAR(255) NOT NULL
                )",
                "CREATE TABLE IF NOT EXISTS task_department (
                    department_id INT AUTO_INCREMENT PRIMARY KEY,
                    department_name VARCHAR(255) NOT NULL,
                    department_status ENUM('Enable', 'Disable'),
                    department_added_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    department_updated_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS task_user (
                    user_id INT AUTO_INCREMENT PRIMARY KEY,
                    user_first_name VARCHAR(255) NOT NULL,
                    user_last_name VARCHAR(255) NOT NULL,
                    department_id INT NOT NULL,
                    user_email_address VARCHAR(255) NOT NULL,
                    user_email_password VARCHAR(255) NOT NULL,
                    user_contact_no VARCHAR(20),
                    user_date_of_birth DATE,
                    user_gender ENUM('Male', 'Female', 'Other'),
                    user_address TEXT,
                    user_status  ENUM('Enable', 'Disable'),
                    user_image VARCHAR(255),
                    user_added_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    user_updated_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (department_id) REFERENCES task_department(department_id)
                )",
                "CREATE TABLE IF NOT EXISTS task_manage (
                    task_id INT AUTO_INCREMENT PRIMARY KEY,
                    task_title VARCHAR(255) NOT NULL,
                    task_creator_description TEXT,
                    task_completion_description TEXT,
                    task_department_id INT NOT NULL,
                    task_user_to INT NOT NULL,
                    task_assign_date DATE,
                    task_end_date DATE,
                    task_status  ENUM('Pending', 'Viewed', 'In Progress', 'Completed', 'Delayed'),
                    task_added_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    task_updated_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (task_department_id) REFERENCES task_department(department_id),
                    FOREIGN KEY (task_user_to) REFERENCES task_user(user_id)
                )"
            ];

            foreach ($tables as $table) {
                $pdo->exec($table);
            }

            $install_step = 2;
        } catch (PDOException $e) {
            $errors[] = "DB ERROR: ". $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_email'])) {
    $host = trim($_POST['db_host']);
    $db = trim($_POST['db_name']);
    $user = trim($_POST['db_user']);
    $pass = trim($_POST['db_pass']);
    $admin_email = trim($_POST['admin_email']);
    $admin_password = trim($_POST['admin_password']);

    if (empty($admin_email)) {
        $errors[] = "Admin email is required.";
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($admin_password)) {
        $errors[] = "Admin password is required.";
    } elseif (strlen($admin_password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("INSERT INTO task_admin (admin_email, admin_password) VALUES (?, ?)");
            $stmt->execute([$admin_email, $hashed_password]);

            //echo "Installation and admin user creation completed successfully!";
            // Create a config.php file to signal the installation completion
            $config_content = "<?php\n";
            $config_content .= "define('DB_HOST', '$host');\n";
            $config_content .= "define('DB_NAME', '$db');\n";
            $config_content .= "define('DB_USER', '$user');\n";
            $config_content .= "define('DB_PASS', '$pass');\n";
            file_put_contents('config.php', $config_content);

            //Create Database connection file
            $db_connect_content = "<?php
            require_once 'config.php';
            try {
                \$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
                \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException \$e) {
                die('DB ERROR: ' . \$e->getMessage());
            }
            ?>
            ";
            file_put_contents('db_connect.php', $db_connect_content);

            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = "DB ERROR: ". $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Task Management Installation Page</title>
    <link href="asset/vendor/bootstrap/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body>
    <main>
        <div class="container">
            <h1 class="mt-5 mb-5 text-center">Task Management System</h1>
            <div class="row">
            <div class="col-md-4">&nbsp;</div>
                <div class="col-md-4">                    
                    <?php if (!empty($errors)) { ?>
                        <div class="alert alert-danger">
                            <ul class="list-unstyled">
                                <?php foreach ($errors as $error) { ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                    <div class="card">
                        <?php if ($install_step == 1) { ?>
                        <div class="card-header"><b>Task Management System Installation</b></div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="db_host" class="form-label">Database Host:</label>
                                    <input type="text" id="db_host" name="db_host" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="db_name" class="form-label">Database Name:</label>
                                    <input type="text" id="db_name" name="db_name" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="db_user" class="form-label">Database User:</label>
                                    <input type="text" id="db_user" name="db_user" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="db_pass" class="form-label">Database Password:</label>
                                    <input type="password" id="db_pass" name="db_pass" class="form-control">
                                </div>
                                <input type="submit" value="Install" class="btn btn-primary">
                            </form>
                        </div>
                        <?php } elseif ($install_step == 2) { ?>
                        <div class="card-header"><b>Set up Admin</b></div>
                        <div class="card-body">
                            <form method="post" action="">
                                <input type="hidden" name="db_host" value="<?php echo $host; ?>">
                                <input type="hidden" name="db_name" value="<?php echo $db; ?>">
                                <input type="hidden" name="db_user" value="<?php echo $user; ?>">
                                <input type="hidden" name="db_pass" value="<?php echo $pass; ?>">
                                <div class="mb-3">
                                    <label for="admin_email" class="form-label">Admin Email:</label>
                                    <input type="email" id="admin_email" name="admin_email" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="admin_password" class="form-label">Admin Password:</label>
                                    <input type="password" id="admin_password" name="admin_password" class="form-control">
                                </div>
                                <input type="submit" value="Create Admin" class="btn btn-primary">
                            </form>
                        <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>