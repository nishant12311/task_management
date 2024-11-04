<?php 

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

$message = '';
$success = false;
$user_id = $_SESSION['user_id'] ?? '';
$user_first_name = '';
$user_last_name = '';
$department_id = '';
$user_email_address = '';
$user_contact_no = '';
$user_date_of_birth = '';
$user_gender = 'Male';
$user_address = '';
$user_image = '';

// Fetch the current user data
if (!empty($user_id)) {
    $stmt = $pdo->prepare("SELECT * FROM task_user WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_first_name = $user['user_first_name'];
        $user_last_name = $user['user_last_name'];
        $user_email_address = $user['user_email_address'];
        $user_contact_no = $user['user_contact_no'];
        $user_date_of_birth = $user['user_date_of_birth'];
        $user_gender = $user['user_gender'];
        $user_address = $user['user_address'];
        $user_image = $user['user_image'];
    } else {
        $message = 'User not found.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_first_name = trim($_POST['user_first_name']);
    $user_last_name = trim($_POST['user_last_name']);
    $user_email_address = trim($_POST['user_email_address']);
    $user_contact_no = trim($_POST['user_contact_no']);
    $user_date_of_birth = trim($_POST['user_date_of_birth']);
    $user_gender = trim($_POST['user_gender']);
    $user_address = trim($_POST['user_address']);
    $new_image = $_FILES['user_image'];
    
    // Validate inputs
    if (empty($user_first_name) || empty($user_last_name) || empty($user_email_address) || empty($user_contact_no) || empty($user_date_of_birth) || empty($user_gender) || empty($user_address)) {
        $message = 'All fields are required.';
    } elseif (!filter_var($user_email_address, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
    } elseif ($new_image['error'] !== UPLOAD_ERR_NO_FILE && $new_image['error'] !== UPLOAD_ERR_OK) {
        $message = 'Error uploading image.';
    } else {
        // Check if email already exists for another user
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM task_user WHERE user_email_address = :user_email_address AND user_id != :user_id");
        $stmt->execute(['user_email_address' => $user_email_address, 'user_id' => $user_id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $message = 'Email already exists.';
        } else {
            // Handle image upload
            if ($new_image['error'] === UPLOAD_ERR_OK) {
                $image_path = 'uploads/' . basename($new_image['name']);
                if (move_uploaded_file($new_image['tmp_name'], $image_path)) {
                    $user_image = $image_path;
                } else {
                    $message = 'Failed to upload image.';
                }
            }

            // Update the database
            if (empty($message)) {
                try {
                    $stmt = $pdo->prepare("UPDATE task_user SET user_first_name = :user_first_name, user_last_name = :user_last_name, user_email_address = :user_email_address, user_contact_no = :user_contact_no, user_date_of_birth = :user_date_of_birth, user_gender = :user_gender, user_address = :user_address, user_image = :user_image, user_updated_on = NOW() WHERE user_id = :user_id");
                    $stmt->execute([
                        'user_first_name'       => $user_first_name,
                        'user_last_name'        => $user_last_name,
                        'user_email_address'    => $user_email_address,
                        'user_contact_no'       => $user_contact_no,
                        'user_date_of_birth'    => $user_date_of_birth,
                        'user_gender'           => $user_gender,
                        'user_address'          => $user_address,
                        'user_image'            => $user_image,
                        'user_id'               => $user_id
                    ]);
                    $success = true;
                } catch (PDOException $e) {
                    $message = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

include('header.php');
?>

<h1 class="mt-4">Profile</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="task.php">Task</a></li>
    <li class="breadcrumb-item active">Profile</a></li>
</ol>
    <?php
    if(isset($message) && $message !== ''){
        echo '
        <div class="alert alert-danger">
        '.$message.'
        </div>
        ';
    }
    if($success){
        echo '<div class="alert alert-success">Data updated successfully</div>';
    }
    ?>
    <div class="card">
        <div class="card-header"><b>Change Profile Details</b></div>
        <div class="card-body">
            <form method="post" action="user_profile.php" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="user_first_name">First Name:</label>
                        <input type="text" id="user_first_name" name="user_first_name" class="form-control" value="<?php echo htmlspecialchars($user_first_name ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="user_last_name">Last Name:</label>
                        <input type="text" id="user_last_name" name="user_last_name" class="form-control" value="<?php echo htmlspecialchars($user_last_name ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="user_email_address">Email:</label>
                        <input type="email" id="user_email_address" name="user_email_address" class="form-control" value="<?php echo htmlspecialchars($user_email_address ?? ''); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="user_contact_no">Contact No:</label>
                        <input type="text" id="user_contact_no" name="user_contact_no" class="form-control" value="<?php echo htmlspecialchars($user_contact_no ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="user_gender">Gender:</label>
                        <select id="user_gender" name="user_gender" class="form-select">
                            <option value="Male" <?php if (isset($user_gender) && $user_gender == 'Male') echo 'selected'; ?>>Male</option>
                            <option value="Female" <?php if (isset($user_gender) && $user_gender == 'Female') echo 'selected'; ?>>Female</option>
                            <option value="Other" <?php if (isset($user_gender) && $user_gender == 'Other') echo 'selected'; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="user_date_of_birth">Date of Birth:</label>
                        <input type="date" id="user_date_of_birth" name="user_date_of_birth" class="form-control" value="<?php echo htmlspecialchars($user_date_of_birth ?? ''); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="user_address">Address:</label>
                        <input type="text" id="user_address" name="user_address" class="form-control" value="<?php echo htmlspecialchars($user_address ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="user_image">User Image:</label><br />
                        <input type="file" id="user_image" name="user_image" accept="image/*">
                        <?php if ($user_image) { ?>
                            <div class="mt-2">
                                <img src="<?php echo htmlspecialchars($user_image); ?>" class="img-thumbnail" alt="User Image" width="100">
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <div class="mt-2 text-center">
                    <input type="submit" value="Save" class="btn btn-primary" />
                </div>
            </form>
        </div>
    </div>

<?php
include('footer.php');
?>