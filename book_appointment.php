<?php
session_start();
include "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $doctor_id = $_POST['doctor_id'];
    $time = $_POST['time_slot'];
    
    $date = date("Y-m-d", strtotime("+1 day"));
    $patient_id = $_SESSION['user_id']; 

    if ($doctor_id == "0") {
        die("Please select a doctor.");
    }

    $sql = "INSERT INTO Appointments (appt_date, appt_time_slot, patient_id, doctor_id, status) 
            VALUES ('$date', '$time', '$patient_id', '$doctor_id', 'Scheduled')";

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php?tab=appointments");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
}
?>