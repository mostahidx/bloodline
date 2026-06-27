<?php
session_start();
include "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_type = $_POST['user_type'];
    $password = $_POST['password'];

    
    if ($user_type == 'patient') {
        $login_input = $_POST['email']; 
        
        $sql = "SELECT Users.user_id, Users.name, Patients.email 
                FROM Users 
                JOIN Patients ON Users.user_id = Patients.user_id 
                WHERE (Patients.email = '$login_input' OR Patients.contact_number = '$login_input') 
                AND Users.password_hash = '$password'";
        
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_type'] = 'Patient';
            header("Location: index.php?login=success"); 
            exit();
        } else {
            echo "<script>alert('Invalid Email/Phone or Password'); window.location.href='index.php';</script>";
        }
    } 
    
    
    elseif ($user_type == 'staff') {
        $hospital_id = $_POST['hospital_id'];
        
        $sql = "SELECT Users.user_id, Users.name 
                FROM Users 
                JOIN Staff ON Users.user_id = Staff.user_id 
                WHERE Staff.hospital_id = '$hospital_id' AND Users.password_hash = '$password'";
        
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_type'] = 'Staff';
            header("Location: staff.php"); 
            exit();
        } else {
            echo "<script>alert('Invalid Hospital ID or Password'); window.location.href='index.php';</script>";
        }
    }

    
    elseif ($user_type == 'admin') {
        $contact = $_POST['admin_contact'];
        
        $sql = "SELECT Users.user_id, Users.name 
                FROM Users 
                JOIN Admins ON Users.user_id = Admins.user_id 
                WHERE Admins.contact_number = '$contact' AND Users.password_hash = '$password'";
        
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['user_type'] = 'Admin';
            header("Location: admindash.php"); 
            exit();
        } else {
            echo "<script>alert('Invalid Admin Phone or Password'); window.location.href='index.php';</script>";
        }
    }
}
?>