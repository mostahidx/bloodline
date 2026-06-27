<?php
session_start();
include "database.php";


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'Admin') {
    header("Location: index.php");
    exit();
}

$message = "";
$msg_type = ""; 


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    
    if (isset($_POST['add_hospital'])) {
        $name = $_POST['hosp_name'];
        $city = $_POST['hosp_city'];
        $area = $_POST['hosp_area'];
        $contact = $_POST['hosp_contact'];

        $sql = "INSERT INTO Hospital (name, city, area, contact_info) VALUES ('$name', '$city', '$area', '$contact')";
        if(mysqli_query($conn, $sql)) { $message = "Hospital Added Successfully!"; $msg_type = "success"; }
        else { $message = "Error: " . mysqli_error($conn); $msg_type = "error"; }
    }

    
    if (isset($_POST['add_doctor'])) {
        $name = $_POST['doc_name'];
        $dept = $_POST['doc_dept'];
        $fee = $_POST['doc_fee'];
        $hosp_id = $_POST['doc_hosp_id'];

        $sql = "INSERT INTO Doctors (doctor_name, department_name, fee, hospital_id) VALUES ('$name', '$dept', '$fee', '$hosp_id')";
        if(mysqli_query($conn, $sql)) { $message = "Doctor Added Successfully!"; $msg_type = "success"; }
        else { $message = "Error: " . mysqli_error($conn); $msg_type = "error"; }
    }

    
    if (isset($_POST['add_bed'])) {
        $type = $_POST['bed_type'];
        $total = $_POST['bed_total'];
        $avail = $_POST['bed_avail'];
        $cost = $_POST['bed_cost'];
        $hosp_id = $_POST['bed_hosp_id'];

        $sql = "INSERT INTO Beds (bed_type, total_count, available_count, cost_per_day, hospital_id) 
                VALUES ('$type', '$total', '$avail', '$cost', '$hosp_id')";
        if(mysqli_query($conn, $sql)) { $message = "Bed Added Successfully!"; $msg_type = "success"; }
        else { $message = "Error: " . mysqli_error($conn); $msg_type = "error"; }
    }

    
    if (isset($_POST['add_staff'])) {
        $username = $_POST['staff_name'];
        $password = $_POST['staff_pass'];
        $usertype = $_POST['staff_type']; 
        $hosp_id = $_POST['staff_hosp_id'];
        $contact = $_POST['staff_contact'];

        // Step 1: Insert into Users
        $sql_user = "INSERT INTO Users (name, password_hash, user_type) VALUES ('$username', '$password', '$usertype')";
        
        if(mysqli_query($conn, $sql_user)) {
            $new_user_id = mysqli_insert_id($conn);
            
            // Step 2: Insert into Staff
            $sql_staff = "INSERT INTO Staff (user_id, hospital_id, contact_number) VALUES ('$new_user_id', '$hosp_id', '$contact')";
            
            if(mysqli_query($conn, $sql_staff)) {
                $message = "Staff Account Created Successfully!"; $msg_type = "success";
            } else {
                $message = "Error adding to Staff table: " . mysqli_error($conn); $msg_type = "error";
            }
        } else {
            $message = "Error adding User: " . mysqli_error($conn); $msg_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BloodLine</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        body { background-color: #f1f5f9; display: block; height: auto; overflow: auto; }
        .admin-nav { background: #1e293b; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; color: white; }
        .admin-brand { font-size: 1.5rem; font-weight: 700; color: #e11d48; }
        
        .admin-container { max-width: 1000px; margin: 2rem auto; padding: 0 20px; display: grid; gap: 2rem; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); }
        
        .admin-card { background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .admin-card h3 { margin-bottom: 1.5rem; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 0.5rem; }
        
        .alert { padding: 1rem; border-radius: 0.5rem; margin: 1rem auto; max-width: 1000px; text-align: center; }
        .alert.success { background: #dcfce7; color: #166534; }
        .alert.error { background: #fee2e2; color: #991b1b; }

        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-size: 0.9rem; margin-bottom: 0.3rem; color: #64748b; }
        .form-group input { width: 100%; padding: 0.7rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; }
        .btn-submit { width: 100%; background: #1e293b; color: white; padding: 0.8rem; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; }
        .btn-submit:hover { background: #0f172a; }
    </style>
</head>
<body>

    <nav class="admin-nav">
        <div class="admin-brand"><i class="fa-solid fa-shield-halved"></i> BloodLine Admin</div>
        <div class="nav-user">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="logout.php" class="btn-logout" title="Logout" style="color: white;"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </nav>

    <?php if ($message): ?>
        <div class="alert <?php echo $msg_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="admin-container">
        
        <div class="admin-card">
            <h3><i class="fa-solid fa-hospital"></i> Add Hospital</h3>
            <form method="POST">
                <div class="form-group"><label>Hospital Name</label><input type="text" name="hosp_name" required></div>
                <div class="form-group"><label>City</label><input type="text" name="hosp_city" required></div>
                <div class="form-group"><label>Area</label><input type="text" name="hosp_area" required></div>
                <div class="form-group"><label>Contact Info</label><input type="text" name="hosp_contact" required></div>
                <button type="submit" name="add_hospital" class="btn-submit">Add Hospital</button>
            </form>
        </div>

        <div class="admin-card">
            <h3><i class="fa-solid fa-user-doctor"></i> Add Doctor</h3>
            <form method="POST">
                <div class="form-group"><label>Doctor Name</label><input type="text" name="doc_name" required></div>
                <div class="form-group"><label>Department</label><input type="text" name="doc_dept" required></div>
                <div class="form-group"><label>Fee</label><input type="number" name="doc_fee" required></div>
                <div class="form-group"><label>Hospital ID</label><input type="number" name="doc_hosp_id" required></div>
                <button type="submit" name="add_doctor" class="btn-submit">Add Doctor</button>
            </form>
        </div>

        <div class="admin-card">
            <h3><i class="fa-solid fa-bed"></i> Add Bed</h3>
            <form method="POST">
                <div class="form-group"><label>Bed Type</label><input type="text" name="bed_type" placeholder="e.g. General Ward" required></div>
                <div class="form-group"><label>Total Count</label><input type="number" name="bed_total" required></div>
                <div class="form-group"><label>Available Count</label><input type="number" name="bed_avail" required></div>
                <div class="form-group"><label>Cost Per Day</label><input type="number" name="bed_cost" required></div>
                <div class="form-group"><label>Hospital ID</label><input type="number" name="bed_hosp_id" required></div>
                <button type="submit" name="add_bed" class="btn-submit">Add Bed</button>
            </form>
        </div>

        <div class="admin-card">
            <h3><i class="fa-solid fa-id-card"></i> Add Staff Account</h3>
            <form method="POST">
                <div class="form-group"><label>User Name</label><input type="text" name="staff_name" required></div>
                <div class="form-group"><label>Password</label><input type="text" name="staff_pass" required></div>
                <div class="form-group"><label>User Type</label><input type="text" name="staff_type" value="Staff" readonly></div>
                <div class="form-group"><label>Hospital ID</label><input type="number" name="staff_hosp_id" required></div>
                <div class="form-group"><label>Contact Number</label><input type="text" name="staff_contact" required></div>
                <button type="submit" name="add_staff" class="btn-submit">Create Staff Account</button>
            </form>
        </div>

    </div>

</body>
</html>