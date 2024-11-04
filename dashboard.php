<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminOrUserLogin();

$pendingSql = "SELECT COUNT(*) FROM task_manage WHERE task_status = 'Pending' OR task_status = 'Viewed'";
$processSql = "SELECT COUNT(*) FROM task_manage WHERE task_status = 'In Progress'";
$completeSql = "SELECT COUNT(*) FROM task_manage WHERE task_status = 'Completed'";
$delaySql = "SELECT COUNT(*) FROM task_manage WHERE task_status = 'Delayed'";
if(isset($_SESSION['user_logged_in'])){
    $pendingSql = "SELECT COUNT(*) FROM task_manage WHERE (task_status = 'Pending' OR task_status = 'Viewed') AND task_user_to = '".$_SESSION['user_id']."'";
    $processSql = "SELECT COUNT(*) FROM task_manage WHERE task_status = 'In Progress' AND task_user_to = '".$_SESSION['user_id']."'";
    $completeSql = "SELECT COUNT(*) FROM task_manage WHERE task_status = 'Completed' AND task_user_to = '".$_SESSION['user_id']."'";
    $delaySql = "SELECT COUNT(*) FROM task_manage WHERE task_status = 'Delayed' AND task_user_to = '".$_SESSION['user_id']."'";
}
$stmt = $pdo->prepare($pendingSql);
$stmt->execute();
$total_pending_tasks = $stmt->fetchColumn();

// Count total in-process tasks
$stmt = $pdo->prepare($processSql);
$stmt->execute();
$total_in_process_tasks = $stmt->fetchColumn();

// Count total completed tasks
$stmt = $pdo->prepare($completeSql);
$stmt->execute();
$total_completed_tasks = $stmt->fetchColumn();

// Count total delayed tasks
$stmt = $pdo->prepare($delaySql);
$stmt->execute();
$total_delayed_tasks = $stmt->fetchColumn();

include('header.php');
?>
<style>
    .circle {
        display: inline-block;
        width: 100px;
        height: 100px;
        line-height: 100px;
        border-radius: 50%;
        background-color: white;
        color: black;
        text-align: center;
        font-size: 36px;
        font-weight: bold;
        margin-bottom:16px;
    }
</style>
<h1 class="mt-4">Dashboard</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item active">Dashboard</li>
</ol>
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body text-center">
                <div class="circle"><?php echo $total_pending_tasks; ?></div><br />
                <b>Pending Task</b>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white mb-4">
            <div class="card-body text-center">
                <div class="circle"><?php echo $total_in_process_tasks; ?></div><br />
                <b>In Process Task</b>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white mb-4">
            <div class="card-body text-center">
                <div class="circle"><?php echo $total_completed_tasks; ?></div><br />
                <b>Completed Task</b>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-danger text-white mb-4">
            <div class="card-body text-center">
                <div class="circle"><?php echo $total_delayed_tasks; ?></div><br />
                <b>Delayed Task</b>
            </div>
        </div>
    </div>
</div>

<!-- Pie Chart for Task Status -->
<!-- Pie Chart for Task Status -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-chart-pie me-1"></i>
        Task Status Overview
    </div>
    <div class="card-body d-flex justify-content-center">
        <canvas id="taskStatusPieChart" style="max-width: 300px; max-height: 300px;"></canvas>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col col-md-6"><b>Task List</b></div>
            <div class="col col-md-6">
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
    // Pie chart for task status
    const ctx = document.getElementById('taskStatusPieChart').getContext('2d');
    const taskStatusPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Pending', 'In Progress', 'Completed', 'Delayed'],
            datasets: [{
                data: [<?php echo $total_pending_tasks; ?>, <?php echo $total_in_process_tasks; ?>, <?php echo $total_completed_tasks; ?>, <?php echo $total_delayed_tasks; ?>],
                backgroundColor: ['#007bff', '#ffc107', '#28a745', '#dc3545'],
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + tooltipItem.raw;
                        }
                    }
                }
            }
        },
    });

    // DataTable initialization
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
                    <?php if(isset($_SESSION["admin_logged_in"])){ ?>
                    if(row.task_status === 'Pending' || row.task_status === 'Viewed'){
                        btn += `<a href="edit_task.php?id=${row.task_id}" class="btn btn-warning btn-sm">Edit</a>&nbsp;`;
                        btn += `<button type="button" class="btn btn-danger btn-sm btn-delete" data-id="${row.task_id}">Delete</button>`;
                    }
                    <?php } ?>
                    return `<div class="text-center">${btn}</div>`;
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
