<?php 
session_start(); 
include "database.php";

if(isset($_POST["med_name"]) && isset($_SESSION['user_id'])) {
    
    $name = $_POST["med_name"];
    $time = $_POST["med_time"];
    $days = $_POST["med_days"];
    $patient_id = $_SESSION['user_id']; 

    $sql = "INSERT INTO Medicines (medicine_name, dosage_time, days_left, patient_id)
            VALUES ('$name', '$time', '$days', '$patient_id')";
    
    if(mysqli_query($conn, $sql)) {
        header("Location: index.php?tab=medicines"); 
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "Please log in first.";
}
?>