
<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

include('header.php');
?>

<h1 class="mt-4">Department Management</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item active">Department Management</li>
</ol>

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col col-md-6"><b>Department List</b></div>
            <div class="col col-md-6">
                <a href="add_department.php" class="btn btn-success btn-sm float-end">Add</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table id="departmentTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Added On</th>
                    <th>Updated On</th>
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
    $('#departmentTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "department_ajax.php",
            "type": "GET"
        },
        "columns": [
            { "data": "department_id" },
            { "data": "department_name" },
            { 
                "data" : null,
                "render" : function(data, type, row){
                    if(row.department_status === 'Enable'){
                        return `<span class="badge bg-success">Enable</span>`;
                    } else {
                        return `<span class="badge bg-danger">Disable</span>`;
                    }
                } 
            },
            { "data": "department_added_on" },
            { "data": "department_updated_on" },
            {
                "data" : null,
                "render" : function(data, type, row){
                    return `
                    <div class="text-center">
                        <a href="edit_department.php?id=${row.department_id}" class="btn btn-warning btn-sm">Edit</a>&nbsp;
                    `;
                }
            }
        ]
    });
});
</script>