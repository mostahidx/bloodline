<?php
session_start();
include "database.php";

if (isset($_FILES['file_upload']) && isset($_SESSION['user_id'])) {
    $title = $_POST['report_type']; 
    $file_name = time() . "_" . basename($_FILES['file_upload']['name']);
    $target_path = "uploads/" . $file_name;
    $patient_id = $_SESSION['user_id'];

    if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $target_path)) {
        $sql = "INSERT INTO Test_Reports (test_type, file_path, report_date, patient_id) 
                VALUES ('$title', '$file_name', NOW(), '$patient_id')";
        
        if(mysqli_query($conn, $sql)) {
            header("Location: index.php?tab=reports");
            exit();
        } else {
            echo "Database Error: " . mysqli_error($conn);
        }
    } else {
        echo "File upload failed. Check folder permissions.";
    }
}
?>