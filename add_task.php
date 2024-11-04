<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

// Fetch departments for the dropdown
$departments = $pdo->query("SELECT department_id, department_name FROM task_department WHERE department_status = 'enable'")->fetchAll(PDO::FETCH_ASSOC);

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $errors = [];

    $task_department_id = $_POST['task_department_id'];
    $task_user_to = $_POST['task_user_to'];
    $task_title = trim($_POST['task_title']);
    $task_creator_description = trim($_POST['task_creator_description']);
    $task_assign_date = $_POST['task_assign_date'];
    $task_end_date = $_POST['task_end_date'];
    $task_priority = $_POST['task_priority']; // New priority field

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
    } else {
        // Check if Task End Date is less than Task Assign Date
        if (strtotime($task_end_date) < strtotime($task_assign_date)) {
            $errors[] = 'Task end date cannot be earlier than task assign date.';
        }
    }
    if (empty($task_priority)) {
        $errors[] = 'Task priority is required.';
    }

    if (empty($errors)) {
        // Insert the task into task_manage table
        $stmt = $pdo->prepare("INSERT INTO task_manage (task_title, task_creator_description, task_department_id, task_user_to, task_assign_date, task_end_date, task_priority, task_status, task_added_on, task_updated_on) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW(), NOW())");
        $stmt->execute([$task_title, $task_creator_description, $task_department_id, $task_user_to, $task_assign_date, $task_end_date, $task_priority]);

        // Get the last inserted task ID
        $task_id = $pdo->lastInsertId();

        // Insert notification for the assigned user
        $notification_text = "A new task '$task_title' has been assigned to you.";
        $notification_stmt = $pdo->prepare("INSERT INTO notifications (user_id, task_id, notification_text, status, created_at) 
                                            VALUES (?, ?, ?, 'unread', NOW())");
        $notification_stmt->execute([$task_user_to, $task_id, $notification_text]);

        // Redirect to task list after successful insertion
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

<h1 class="mt-4">Add Task</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="task.php">Task Management</a></li>
    <li class="breadcrumb-item active">Add Task</li>
</ol>
<?php
if($message !== ''){
    echo '<div class="alert alert-danger">'.$message.'</div>';
}
?>
<div class="card">
    <div class="card-header">Add Task</div>
        <div class="card-body">
            <form id="addTaskForm" method="POST" action="add_task.php">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="task_department_id">Department Name</label>
                        <select name="task_department_id" id="task_department_id" class="form-select">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo $department['department_id']; ?>"><?php echo $department['department_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="task_user_to">User Name</label>
                        <select name="task_user_to" id="task_user_to" class="form-select">
                            <option value="">Select User</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="task_title">Task Title</label>
                    <input type="text" name="task_title" id="task_title" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="task_creator_description">Task Description</label>
                    <textarea name="task_creator_description" id="task_creator_description" class="summernote"></textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="task_assign_date">Task Assign Date</label>
                        <input type="date" name="task_assign_date" id="task_assign_date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="task_end_date">Task End Date</label>
                        <input type="date" name="task_end_date" id="task_end_date" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="task_priority">Task Priority</label>
                    <select name="task_priority" id="task_priority" class="form-select">
                        <option value="">Select Priority</option>
                        <option value="High">High</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="Low">Low</option>
                    </select>
                </div>
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-primary">Add Task</button>
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
        height: 200,
        callbacks: {
            onImageUpload: function(files) {
                for (var i = 0; i < files.length; i++) {
                    sendFile(files[i]);
                }
            }
        }
    });

    function sendFile(file) {
        var data = new FormData();
        data.append("file", file);

        $.ajax({
            url: 'upload.php',
            type: "POST",
            data: data,
            contentType: false,
            processData: false,
            success: function(url) {
                $('.summernote').summernote('insertImage', url);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
                alert('Error uploading image. Please try again.');
            }
        });
    }

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

    $('#addTaskForm').on('submit', function(e) {
        var assignDate = $('#task_assign_date').val();
        var endDate = $('#task_end_date').val();
        
        if (new Date(endDate) < new Date(assignDate)) {
            e.preventDefault(); // Prevent form submission
            alert('Task end date cannot be earlier than task assign date.');
        }
    });
});    
</script>
