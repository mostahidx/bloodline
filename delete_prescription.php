<?php
session_start();
include "database.php";

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $id = $_GET['id'];
    $patient_id = $_SESSION['user_id'];

    $sql_check = "SELECT file_path FROM Prescriptions WHERE prescription_id = '$id' AND patient_id = '$patient_id'";
    $result = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $file_path = "uploads/" . $row['file_path'];
        
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        $sql_del = "DELETE FROM Prescriptions WHERE prescription_id = '$id'";
        mysqli_query($conn, $sql_del);
    }
}

header("Location: index.php?tab=prescriptions");
exit();
?>












