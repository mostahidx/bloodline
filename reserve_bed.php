<?php
session_start();
include "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    
    $bed_id = $_POST['bed_id']; 
    $patient_id = $_SESSION['user_id'];
    
    if(empty($bed_id)) {
        die("Error: No bed selected.");
    }

    // 1. Check if bed is actually available first
    $check_sql = "SELECT available_count FROM Beds WHERE bed_id = '$bed_id'";
    $check_res = mysqli_query($conn, $check_sql);
    $bed_data = mysqli_fetch_assoc($check_res);

    if ($bed_data['available_count'] > 0) {
        // 2. Decrease the Available Count
        $update_sql = "UPDATE Beds SET available_count = available_count - 1 WHERE bed_id = '$bed_id'";
        mysqli_query($conn, $update_sql);

        // 3. Insert Reservation (Status: Pending)
        // Note: We set status to Pending so it shows up in the Payment tab
        $sql = "INSERT INTO Reservations (start_date, end_date, status, patient_id, bed_id) 
                VALUES (NOW(), NOW(), 'Pending', '$patient_id', '$bed_id')";

        if (mysqli_query($conn, $sql)) {
            // Redirect to payments tab so they can pay immediately
            header("Location: index.php?tab=payments");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Sorry, this bed was just booked by someone else.";
    }
} else {
    header("Location: index.php");
}
?>