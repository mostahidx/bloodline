<?php
session_start();
include "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $amount = $_POST['amount'];
    $method = $_POST['paymethod'];
    $item_id = $_POST['item_id']; // This is the ID of the Appointment or Reservation
    $item_type = $_POST['item_type']; // 'appointment' or 'reservation'
    $patient_id = $_SESSION['user_id'];

    if ($item_type == 'appointment') {
        // 1. Handle Appointment Payment
        $sql_pay = "INSERT INTO Payments (amount, payment_date, status, payment_method, patient_id, appointment_id) 
                    VALUES ('$amount', NOW(), 'Paid', '$method', '$patient_id', '$item_id')";
        
        // Update Appointment Status
        $sql_update = "UPDATE Appointments SET status = 'Completed' WHERE appointment_id = '$item_id'"; // Or 'Paid' depending on your ENUM
        mysqli_query($conn, $sql_update);

    } elseif ($item_type == 'reservation') {
        // 2. Handle Bed Reservation Payment
        $sql_pay = "INSERT INTO Payments (amount, payment_date, status, payment_method, patient_id, reservation_id) 
                    VALUES ('$amount', NOW(), 'Paid', '$method', '$patient_id', '$item_id')";
        
        // Update Reservation Status
        $sql_update = "UPDATE Reservations SET status = 'Confirmed' WHERE reservation_id = '$item_id'";
        mysqli_query($conn, $sql_update);
    }

    if (mysqli_query($conn, $sql_pay)) {
        header("Location: index.php?tab=payments");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>