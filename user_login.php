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
    $email = trim($_POST['user_email_address']);
    $password = trim($_POST['user_email_password']);

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
            $stmt = $pdo->prepare("SELECT * FROM task_user WHERE user_email_address = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if($user['user_status'] === 'Enable'){
                if ($user && password_verify($password, $user['user_email_password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_image'] = $user['user_image'];
                    header('Location: task.php');
                    exit;
                } else {
                    $errors[] = "Invalid email or password.";
                }
            } else {
                $errors[] = "Login is Disable.";
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
    <title>Task Management User Login</title>
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
                        <div class="card-header"><b>User Login</b></div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="user_email_address" class="form-label">User Email:</label>
                                    <input type="email" id="user_email_address" name="user_email_address" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="user_email_password" class="form-label">User Password:</label>
                                    <input type="password" id="user_email_password" name="user_email_password" class="form-control">
                                </div>
                                <input type="submit" value="Login" class="btn btn-primary">&nbsp;&nbsp;&nbsp;
                                <a href="index.php">Admin Login</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>