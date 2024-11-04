<?php

session_start();

function checkAdminLogin() {
    if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
        header('Location: index.php');
        exit;
    } 
}

function redirectIfLoggedIn() {
    if ((isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) || isset($_SESSION['user_id']) && $_SESSION['user_id']) {
        if($_SESSION['admin_logged_in']){
            header('Location: dashboard.php');
        }
        if($_SESSION['user_id']){
            header('location:task.php');
        }
        exit;
    }
}

function checkAdminOrUserLogin(){
    if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['user_logged_in'])) {
        header('Location: index.php');
        exit;
    }
}

function formatTaskStatus($status){
    if($status === 'Pending'){
        return '<span class="badge bg-primary">Pending</span>';
    }
    if($status === 'Viewed'){
        return '<span class="badge bg-info">Viewed</span>';
    }
    if($status === 'In Progress'){
        return '<span class="badge bg-warning">In Progress</span>';
    }
    if($status === 'Completed'){
        return '<span class="badge bg-success">Completed</span>';
    }
    if($status === 'Delayed'){
        return '<span class="badge bg-danger">Delayed</span>';
    }
}


?>