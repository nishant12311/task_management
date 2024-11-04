
<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

checkAdminLogin();

include('header.php');
?>

<h1 class="mt-4">User Management</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item active">User Management</li>
</ol>

<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col col-md-6"><b>User List</b></div>
            <div class="col col-md-6">
                <a href="add_user.php" class="btn btn-success btn-sm float-end">Add</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table id="userTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>ID</th>
                    <th>Department</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Contact No.</th>
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
    $('#userTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "user_ajax.php",
            "type": "GET"
        },
        "columns": [
            {
                "data" : null,
                "render" : function(data, type, row){
                    return `<img src="${row.user_image}" width="50" />`;
                }
            },
            { "data": "user_id" },
            { "data": "department_name" },
            { "data": "user_first_name" },
            { "data": "user_last_name" },
            { "data": "user_email_address" },
            { "data": "user_contact_no" },
            { 
                "data" : null,
                "render" : function(data, type, row){
                    if(row.user_status === 'Enable'){
                        return `<span class="badge bg-success">Enable</span>`;
                    } else {
                        return `<span class="badge bg-danger">Disable</span>`;
                    }
                } 
            },
            {
                "data" : null,
                "render" : function(data, type, row){
                    return `
                    <div class="text-center">
                        <a href="view_user.php?id=${row.user_id}" class="btn btn-primary btn-sm">View</a>&nbsp;
                        <a href="edit_user.php?id=${row.user_id}" class="btn btn-warning btn-sm">Edit</a>
                    `;
                }
            }
        ]
    });
});
</script>