<?php

if (!file_exists('db_connect.php')) {
    header('Location: install.php');
    exit;
}

require_once 'db_connect.php';

require_once 'auth_function.php';

redirectIfLoggedIn();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['admin_email']);
    $password = trim($_POST['admin_password']);

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM task_admin WHERE admin_email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['admin_password'])) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_logged_in'] = true;
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $errors[] = "DB ERROR: " . $e->getMessage();
        }
    }
}


?>
<!DOCTYPE html>
<html>
<head>
    <title>Task Management Admin Login</title>
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
                        <div class="card-header"><b>Admin Login</b></div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="admin_email" class="form-label">Admin Email:</label>
                                    <input type="email" id="admin_email" name="admin_email" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="admin_password" class="form-label">Admin Password:</label>
                                    <input type="password" id="admin_password" name="admin_password" class="form-control">
                                </div>
                                <input type="submit" value="Login" class="btn btn-primary">&nbsp;&nbsp;&nbsp;
                                <a href="user_login.php">User Login</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>