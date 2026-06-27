<?php
session_start();
include "database.php";

if (isset($_FILES['file_upload']) && isset($_SESSION['user_id'])) {
    $title = $_POST['title'];
    $file_name = time() . "_" . basename($_FILES['file_upload']['name']);
    $target_path = "uploads/" . $file_name;
    $patient_id = $_SESSION['user_id'];

    if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $target_path)) {
        $sql = "INSERT INTO Prescriptions (prescription_date, file_path, patient_id, title) 
                VALUES (NOW(), '$file_name', '$patient_id', '$title')";
        
        if(mysqli_query($conn, $sql)) {
            header("Location: index.php?tab=prescriptions");
            exit();
        } else {
            echo "Database Error: " . mysqli_error($conn);
        }
    } else {
        echo "File upload failed. Check folder permissions.";
    }
}
