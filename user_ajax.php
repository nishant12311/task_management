<?php

require_once 'db_connect.php';

$columns = [
    0 => 'user_image',
    1 => 'user_id',
    2 => 'department_name',
    3 => 'user_first_name',
    4 => 'user_last_name',
    5 => 'user_email_address',
    6 => 'user_contact_no',
    7 => 'user_status',
    8 => null
];

$limit = $_GET['length'];
$start = $_GET['start'];
$order = $columns[$_GET['order'][0]['column']];
$dir = $_GET['order'][0]['dir'];

$searchValue = $_GET['search']['value'];

// Get total records
$totalRecordsStmt = $pdo->query("SELECT COUNT(*) FROM task_user");
$totalRecords = $totalRecordsStmt->fetchColumn();

// Get total filtered records
$filterQuery = "SELECT COUNT(*) FROM task_user WHERE 1=1";
if (!empty($searchValue)) {
    $filterQuery .= " AND (department_name LIKE '%$searchValue%' OR user_first_name LIKE '%$searchValue%' OR user_last_name LIKE '%$searchValue%' OR user_email_address LIKE '%$searchValue%' OR user_contact_no LIKE '%$searchValue%' OR user_status LIKE '%$searchValue%')";
}
$totalFilteredRecordsStmt = $pdo->query($filterQuery);
$totalFilteredRecords = $totalFilteredRecordsStmt->fetchColumn();

// Fetch data
$dataQuery = "SELECT * FROM task_user INNER JOIN task_department ON task_user.department_id = task_department.department_id WHERE 1=1";
if (!empty($searchValue)) {
    $dataQuery .= " AND (task_department.department_name LIKE '%$searchValue%' OR task_user.user_first_name LIKE '%$searchValue%' OR task_user.user_last_name LIKE '%$searchValue%' OR task_user.user_email_address LIKE '%$searchValue%' OR task_user.user_contact_no LIKE '%$searchValue%' OR task_user.user_status LIKE '%$searchValue%')";
}
$dataQuery .= " ORDER BY $order $dir LIMIT $start, $limit";
$dataStmt = $pdo->query($dataQuery);
$data = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

$response = [
    "draw"              => intval($_GET['draw']),
    "recordsTotal"      => intval($totalRecords),
    "recordsFiltered"   => intval($totalFilteredRecords),
    "data"              => $data
];

echo json_encode($response);

?>