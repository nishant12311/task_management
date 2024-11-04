<?php

session_start();

$next_url = 'index.php';

if(isset($_SESSION['user_logged_in'])){
    $next_url = 'user_login.php';
}

session_unset();
session_destroy();
header('Location: ' . $next_url);
exit;

?>