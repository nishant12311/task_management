<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$department_id = $_GET['id'] ?? '';
$department_name = '';
$department_status = 'Enable';
$message = '';

// Fetch the current department data
if (!empty($department_id)) {
    $stmt = $pdo->prepare("SELECT * FROM task_department WHERE department_id = :department_id");
    $stmt->execute(['department_id' => $department_id]);
    $department = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($department) {
        $department_name = $department['department_name'];
        $department_status = $department['department_status'];
    } else {
        $message = 'Department not found.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $department_name = trim($_POST['department_name']);
    $department_status = trim($_POST['department_status']);
    $department_id = $_POST['department_id'];
    // Validate inputs
    if (empty($department_name)) {
        $message = 'Department name is required.';
    } elseif (!in_array($department_status, ['Enable', 'Disable'])) {
        $message = 'Invalid department status.';
    } else {
        // Check if department name already exists for another department
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM task_department WHERE department_name = :department_name AND department_id != :department_id");
        $stmt->execute([
            'department_name' => $department_name,
            'department_id' => $department_id
        ]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $message = 'Department with this name already exists.';
        } else {
            // Update the database
            try {
                $stmt = $pdo->prepare("UPDATE task_department SET department_name = :department_name, department_status = :department_status, department_updated_on = NOW() WHERE department_id = :department_id");
                $stmt->execute([
                    'department_name' => $department_name,
                    'department_status' => $department_status,
                    'department_id' => $department_id
                ]);
                header('location:department.php');
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

include('header.php');
?>

<h1 class="mt-4">Edit Department</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="department.php">Department Management</a></li>
    <li class="breadcrumb-item active">Edit Department</li>
</ol>

<div class="row">
    <div class="col-md-4">
        <?php
        if(isset($message) && $message !== ''){
            echo '
            <div class="alert alert-danger">
            '.$message.'
            </div>
            ';
        }
        ?>
        <div class="card">
            <div class="card-header">Edit Department</div>
            <div class="card-body">
            <form method="post" action="edit_department.php?id=<?php echo htmlspecialchars($department_id); ?>">
                <div class="mb-3">
                    <label for="department_name">Department Name</label>
                    <input type="text" id="department_name" name="department_name" class="form-control" value="<?php echo htmlspecialchars($department_name); ?>">
                </div>
                <div class="mb-3">
                    <label for="department_status">Department Status</label>
                    <select id="department_status" name="department_status" class="form-select">
                        <option value="Enable" <?php if ($department_status == 'Enable') echo 'selected'; ?>>Enable</option>
                        <option value="Disable" <?php if ($department_status == 'Disable') echo 'selected'; ?>>Disable</option>
                    </select>
                </div>
                <input type="hidden" name="department_id" value="<?php echo htmlspecialchars($department_id); ?>">
                <input type="submit" value="Update Department" class="btn btn-primary">
            </form>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>