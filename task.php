
<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

$task_id = $_GET['id'] ?? '';
$action = $_GET['action'] ?? '';

if(!empty($task_id) && $action === 'delete'){
    $stmt = $pdo->prepare("DELETE FROM task_manage WHERE task_id = :task_id");
    $stmt->execute([
        'task_id'       => $task_id
    ]);
    header('location:task.php');
}

include('header.php');
?>

<h1 class="mt-4">Task Management</h1>
<ol class="breadcrumb mb-4">
<?php
    if(isset($_SESSION['admin_logged_in'])){
    ?>
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <?php
    }
    ?>
    <li class="breadcrumb-item active">Task Management</li>
</ol>

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col col-md-6"><b>Task List</b></div>
            <div class="col col-md-6">
                <?php
                if(isset($_SESSION["admin_logged_in"])){
                ?>
                <a href="add_task.php" class="btn btn-success btn-sm float-end">Add</a>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table id="taskTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department</th>
                    <th>User Detail</th>
                    <th>Task Title</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<?php
include('footer.php');
?>

<script>
$(document).ready(function() {
    $('#taskTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "task_ajax.php",
            "type": "GET"
        },
        "columns": [
            { "data": "task_id" },
            { "data": "department_name" },
            {
                "data" : null,
                "render" : function(data, type, row){
                    return `<img src="${row.user_image}" class="rounded-circle" width="40" /> ${row.user_first_name} ${row.user_last_name}`;
                }
            },            
            { "data": "task_title" },
            { "data": "task_assign_date" },
            { "data": "task_end_date" },
            { 
                "data" : null,
                "render" : function(data, type, row){
                    if(row.task_status === 'Pending'){
                        return `<span class="badge bg-primary">Pending</span>`;
                    }
                    if(row.task_status === 'Viewed'){
                        return `<span class="badge bg-info">Viewed</span>`;
                    }
                    if(row.task_status === 'In Progress'){
                        return `<span class="badge bg-warning">In Progress</span>`;
                    }
                    if(row.task_status === 'Completed'){
                        return `<span class="badge bg-success">Completed</span>`;
                    }
                    if(row.task_status === 'Delayed'){
                        return `<span class="badge bg-danger">Delayed</span>`;
                    }
                } 
            },
            {
                "data" : null,
                "render" : function(data, type, row){
                    let btn = `<a href="view_task.php?id=${row.task_id}" class="btn btn-primary btn-sm">View</a>&nbsp;`;
                    <?php
                    if(isset($_SESSION["admin_logged_in"])){
                    ?>
                    if(row.task_status === 'Pending' || row.task_status === 'Viewed'){
                        btn += `<a href="edit_task.php?id=${row.task_id}" class="btn btn-warning btn-sm">Edit</a>&nbsp;`;
                        btn += `<button type="button" class="btn btn-danger btn-sm btn-delete" data-id="${row.task_id}">Delete</button>`;
                    }
                    <?php
                    }
                    ?>
                    return `
                    <div class="text-center">
                        ${btn}
                    </div>
                    `;
                }
            }
        ]
    });

    $(document).on('click', '.btn-delete', function() {
        if(confirm("Are you sure you want to remove this task?")){
            let id = $(this).data('id');
            window.location.href = 'task.php?id=' + id + '&action=delete';
        }
    });
});
</script>