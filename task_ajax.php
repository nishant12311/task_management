<?php

require_once 'db_connect.php';
require_once 'auth_function.php';

//update task status if task end date overdue



$columns = [
    0 => 'task_id',
    1 => 'department_name',
    2 => 'user_first_name',
    3 => 'task_title',
    4 => 'task_assign_date',
    5 => 'task_end_date',
    6 => 'task_status',
    7 => null
];

if(isset($_GET["user_id"])){
    $user_id = $_GET["user_id"];
    // Fetch the total number of records without filtering
    $totalRecordsSql = "SELECT COUNT(*) FROM task_manage WHERE task_user_to = '".$user_id."'";
    $stmt = $pdo->query($totalRecordsSql);
    $totalRecords = $stmt->fetchColumn();

    // Fetch the filtered records
    $searchValue = $_GET['search']['value'];
    $searchQuery = "";
    if (!empty($searchValue)) {
        $searchQuery = " AND (task_department.department_name LIKE :department_name, task_manage.task_title LIKE :task_title OR task_manage.task_status LIKE :task_status)";
    }

    $limit = $_GET['length'];
    $offset = $_GET['start'];
    $orderColumn = $columns[$_GET['order'][0]['column']];
    $orderDir = $_GET['order'][0]['dir'];
    $query = "SELECT task_manage.*, task_department.department_name 
                FROM task_manage 
                JOIN task_department ON task_manage.task_department_id = task_department.department_id 
                WHERE task_manage.task_user_to = :task_user_to AND task_manage.task_assign_date <= :task_assign_date $searchQuery 
                ORDER BY $orderColumn $orderDir 
                LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':task_user_to', (int) $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':task_assign_date', date('Y-m-d'));
    
    if (!empty($searchValue)) {
        $stmt->bindValue(':department_name', "%$searchValue%", PDO::PARAM_STR);
        $stmt->bindValue(':task_title', "%$searchValue%", PDO::PARAM_STR);
        $stmt->bindValue(':task_status', "%$searchValue%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch the total number of records with filtering
    if (!empty($searchValue)) {
        $searchValueSql = "
        SELECT COUNT(*) 
        FROM task_manage 
        JOIN task_department ON task_manage.task_department_id = task_department.department_id 
        WHERE task_manage.task_user_to = '".$user_id."' AND $searchQuery
        ";
        $stmt = $pdo->prepare($searchValueSql);
        $stmt->bindValue(':department_name', "%$searchValue%", PDO::PARAM_STR);
        $stmt->bindValue(':task_title', "%$searchValue%", PDO::PARAM_STR);
        $stmt->bindValue(':task_status', "%$searchValue%", PDO::PARAM_STR);
        $stmt->execute();
        $filteredRecords = $stmt->fetchColumn();
    } else {
        $filteredRecords = $totalRecords;
    }
} else {
    // Fetch the total number of records without filtering
    $totalRecordsSql = "SELECT COUNT(*) FROM task_manage";
    if(isset($_SESSION['user_logged_in'])){

        $totalRecordsSql = "SELECT COUNT(*) FROM task_manage WHERE task_user_to = '".$_SESSION['user_id']."'";
    }
    $stmt = $pdo->query($totalRecordsSql);
    $totalRecords = $stmt->fetchColumn();

    // Fetch the filtered records
    $searchValue = $_GET['search']['value'];
    $searchQuery = "";
    if (!empty($searchValue)) {
        $searchQuery = " AND (task_department.department_name LIKE :department_name, task_user.user_first_name LIKE :user_first_name, task_manage.task_title LIKE :task_title OR task_manage.task_status LIKE :task_status)";
    }

    $limit = $_GET['length'];
    $offset = $_GET['start'];
    $orderColumn = $columns[$_GET['order'][0]['column']];
    $orderDir = $_GET['order'][0]['dir'];
    $query = "";
    if(isset($_SESSION['user_logged_in'])){
        $query = "SELECT task_manage.*, task_department.department_name, task_user.user_first_name, task_user.user_last_name, task_user.user_image  
                FROM task_manage 
                JOIN task_department ON task_manage.task_department_id = task_department.department_id 
                JOIN task_user ON task_manage.task_user_to = task_user.user_id 
                WHERE task_manage.task_user_to = :task_user_to AND task_manage.task_assign_date <= :task_assign_date $searchQuery 
                ORDER BY $orderColumn $orderDir 
                LIMIT :limit OFFSET :offset";
    } else {
        $query = "SELECT task_manage.*, task_department.department_name, task_user.user_first_name, task_user.user_last_name, task_user.user_image  
                FROM task_manage 
                JOIN task_department ON task_manage.task_department_id = task_department.department_id 
                JOIN task_user ON task_manage.task_user_to = task_user.user_id 
                WHERE 1=1 $searchQuery 
                ORDER BY $orderColumn $orderDir 
                LIMIT :limit OFFSET :offset";
    }

    $stmt = $pdo->prepare($query);
    if(isset($_SESSION['user_logged_in'])){
        $stmt->bindValue(':task_user_to', (int) $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':task_assign_date', date('Y-m-d'));
    }
    if (!empty($searchValue)) {
        $stmt->bindValue(':department_name', "%$searchValue%", PDO::PARAM_STR);
        $stmt->bindValue(':user_first_name', "%$searchValue%", PDO::PARAM_STR);
        $stmt->bindValue(':task_title', "%$searchValue%", PDO::PARAM_STR);
        $stmt->bindValue(':task_status', "%$searchValue%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch the total number of records with filtering
    if (!empty($searchValue)) {        
        if(isset($_SESSION['user_logged_in'])){
            $searchValueSql = "
            SELECT COUNT(*) 
            FROM task_manage 
            JOIN task_department ON task_manage.task_department_id = task_department.department_id 
            JOIN task_user ON task_manage.task_user_to = task_user.user_id 
            WHERE task_manage.task_user_to = '".$_SESSION["user_id"]."' AND $searchQuery
            ";
        } else {
            $searchValueSql = "
            SELECT COUNT(*) 
            FROM task_user 
            JOIN task_department ON task_user.task_department_id = task_department.department_id 
            JOIN task_user ON task_user.task_user_to = task_user.user_id 
            WHERE 1=1 $searchQuery
            ";
        }
        $stmt = $pdo->prepare($searchValueSql);
        $stmt->bindValue(':department_name', "%$searchValue%", PDO::PARAM_STR);
        $stmt->bindValue(':user_first_name', "%$searchValue%", PDO::PARAM_STR);
        $stmt->bindValue(':task_title', "%$searchValue%", PDO::PARAM_STR);
        $stmt->bindValue(':task_status', "%$searchValue%", PDO::PARAM_STR);
        $stmt->execute();
        $filteredRecords = $stmt->fetchColumn();
    } else {
        $filteredRecords = $totalRecords;
    }
}

$response = [
    "draw"              => intval($_GET['draw']),
    "recordsTotal"      => intval($totalRecords),
    "recordsFiltered"   => intval($filteredRecords),
    "data"              => $data
];

echo json_encode($response);

?>