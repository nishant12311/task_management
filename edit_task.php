<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

$message = '';

// Fetch task details if id is set
if (isset($_GET['id'])) {
    $task_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM task_manage WHERE task_id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$task) {
        $message = "Task not found!";
        exit;
    }

    // Fetch departments for the dropdown
    $departments = $pdo->query("SELECT department_id, department_name FROM task_department WHERE department_status = 'Enable'")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch users for the dropdown
    $stmt = $pdo->prepare("SELECT user_id, CONCAT(user_first_name, ' ', user_last_name) AS user_name FROM task_user WHERE department_id = ? AND user_status = 'enable'");
    $stmt->execute([$task['task_department_id']]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $message = "Invalid task ID!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    $task_id = $_POST['task_id'];
    $task_department_id = $_POST['task_department_id'];
    $task_user_to = $_POST['task_user_to'];
    $task_title = trim($_POST['task_title']);
    $task_creator_description = trim($_POST['task_creator_description']);
    $task_assign_date = $_POST['task_assign_date'];
    $task_end_date = $_POST['task_end_date'];

    // Validate fields
    if (empty($task_department_id)) {
        $errors[] = 'Department is required.';
    }
    if (empty($task_user_to)) {
        $errors[] = 'User is required.';
    }
    if (empty($task_title)) {
        $errors[] = 'Task title is required.';
    }
    if (empty($task_creator_description)) {
        $errors[] = 'Task description is required.';
    }
    if (empty($task_assign_date)) {
        $errors[] = 'Task assign date is required.';
    }
    if (empty($task_end_date)) {
        $errors[] = 'Task end date is required.';
    }

    // Check for duplicate task title
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM task_manage WHERE task_title = ? AND task_id != ?");
    $stmt->execute([$task_title, $task_id]);
    $duplicate = $stmt->fetchColumn();
    if ($duplicate) {
        $errors[] = 'Task title already exists.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE task_manage SET task_department_id = ?, task_user_to = ?, task_title = ?, task_creator_description = ?, task_assign_date = ?, task_end_date = ?, task_updated_on = NOW() WHERE task_id = ?");
        $stmt->execute([$task_department_id, $task_user_to, $task_title, $task_creator_description, $task_assign_date, $task_end_date, $task_id]);
        header("Location: task.php");
        exit;
    } else {
        $message = '<ul class="list-unstyled">';
        foreach ($errors as $error) {
            $message .= '<li>' . $error . '</li>';
        }
        $message .= '</ul>';
    }
}

include('header.php');
?>

<h1 class="mt-4">Edit Task</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="task.php">Task Management</a></li>
    <li class="breadcrumb-item active">Edit Task</li>
</ol>
<?php
if($message !== ''){
    echo '<div class="alert alert-danger">'.$message.'</div>';
}
?>
<div class="card">
    <div class="card-header">Edit Task</div>
        <div class="card-body">
            <form id="editTaskForm" method="POST" action="edit_task.php?id=<?php echo htmlspecialchars($task_id); ?>">
                <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="task_department_id">Department Name</label>
                        <select name="task_department_id" id="task_department_id" class="form-select">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo $department['department_id']; ?>" <?php echo $department['department_id'] == $task['task_department_id'] ? 'selected' : ''; ?>><?php echo $department['department_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="task_user_to">User Name</label>
                        <select name="task_user_to" id="task_user_to" class="form-select">
                            <option value="">Select User</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['user_id']; ?>" <?php echo $user['user_id'] == $task['task_user_to'] ? 'selected' : ''; ?>><?php echo $user['user_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="task_title">Task Title</label>
                    <input type="text" name="task_title" id="task_title" class="form-control" value="<?php echo $task['task_title']; ?>">
                </div>
                <div class="mb-3">
                    <label for="task_creator_description">Task Description</label>
                    <textarea name="task_creator_description" id="task_creator_description" class="summernote"><?php echo $task['task_creator_description']; ?></textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="task_assign_date">Task Assign Date</label>
                        <input type="date" name="task_assign_date" id="task_assign_date" class="form-control" value="<?php echo $task['task_assign_date']; ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="task_end_date">Task End Date</label>
                        <input type="date" name="task_end_date" id="task_end_date" class="form-control" value="<?php echo $task['task_end_date']; ?>">
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary">Update Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include('footer.php');
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.js"></script>

<script>
$(document).ready(function() {
    $('.summernote').summernote({
        height: 200
    });

    $('#task_department_id').change(function() {
        var departmentId = $(this).val();
        if (departmentId) {
            $.ajax({
                url: 'fetch_users.php',
                type: 'POST',
                data: { department_id: departmentId },
                success: function(data) {
                    $('#task_user_to').html(data);
                }
            });
        } else {
            $('#task_user_to').html('<option value="">Select User</option>');
        }
    });
});
</script>