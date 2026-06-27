<?php
include "database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // --- 1. SECURITY CHECK: Does this Email or Phone already exist? ---
    $check_sql = "SELECT * FROM Patients WHERE email = '$email' OR contact_number = '$phone'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>
                alert('Error: This Email or Phone Number is already registered. Please use a different one.'); 
                window.location.href='index.php';
              </script>";
        exit(); 
    }

    $sql_user = "INSERT INTO Users (name, password_hash, user_type) VALUES ('$name', '$password', 'Patient')";
    
    if(mysqli_query($conn, $sql_user)) {
        $user_id = mysqli_insert_id($conn); 

        $sql_patient = "INSERT INTO Patients (user_id, address, email, contact_number) 
                        VALUES ('$user_id', '$address', '$email', '$phone')";
        
        if(mysqli_query($conn, $sql_patient)) {
            echo "<script>
                    alert('Account Created Successfully! Please Login.'); 
                    window.location.href='index.php';
                  </script>";
            exit();
        } else {
             echo "Error creating patient profile: " . mysqli_error($conn);
        }
    } else {
        echo "Error creating user: " . mysqli_error($conn);
    }
}
?>