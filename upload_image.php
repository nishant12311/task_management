<?php
// upload_image.php
if ($_FILES['file']['name']) {
    $file = $_FILES['file'];
    $fileName = time() . '_' . $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileDestination = 'uploads/' . $fileName; // Ensure uploads folder exists and has write permissions

    if (move_uploaded_file($fileTmp, $fileDestination)) {
        echo json_encode(['location' => $fileDestination]); // Return JSON for Summernote
    } else {
        echo json_encode(['error' => 'Failed to upload image']);
    }
}
?>
