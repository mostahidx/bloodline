<?php
session_start();
include "database.php";

// Security: Ensure only Staff can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'Staff') {
    header("Location: index.php");
    exit();
}

$message = "";
$msg_type = ""; // success or error

// --- HANDLE UPLOAD LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file_upload'])) {
    
    $phone = mysqli_real_escape_string($conn, $_POST['patient_phone']);
    $report_title = mysqli_real_escape_string($conn, $_POST['report_type']);
    
    // 1. Find Patient ID using Phone Number
    // FIXED: Changed "SELECT user_id" to "SELECT Patients.user_id" to remove ambiguity
    $sql_find = "SELECT Patients.user_id, Users.name FROM Patients 
                 JOIN Users ON Patients.user_id = Users.user_id 
                 WHERE contact_number = '$phone'";
    
    $result_find = mysqli_query($conn, $sql_find);

    if (mysqli_num_rows($result_find) > 0) {
        $patient = mysqli_fetch_assoc($result_find);
        $patient_id = $patient['user_id'];
        $patient_name = $patient['name'];

        // 2. Process File Upload
        $file_name = time() . "_" . basename($_FILES['file_upload']['name']);
        $target_path = "uploads/" . $file_name;

        if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $target_path)) {
            // 3. Insert into Database
            $sql_insert = "INSERT INTO Test_Reports (test_type, file_path, report_date, patient_id) 
                           VALUES ('$report_title', '$file_name', NOW(), '$patient_id')";
            
            if (mysqli_query($conn, $sql_insert)) {
                $message = "Success! Report uploaded for patient: <strong>$patient_name</strong>";
                $msg_type = "success";
            } else {
                $message = "Database Error: " . mysqli_error($conn);
                $msg_type = "error";
            }
        } else {
            $message = "File upload failed. Check folder permissions.";
            $msg_type = "error";
        }
    } else {
        $message = "Error: No patient found with phone number <strong>$phone</strong>.";
        $msg_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - BloodLine</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        /* Specific Styles for Staff Page to keep it clean */
        body { background-color: #f1f5f9; display: block; height: auto; overflow: auto; }
        .staff-nav {
            background: #ffffff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 2rem;
        }
        .staff-brand { font-size: 1.5rem; font-weight: 700; color: #e11d48; }
        .staff-container { max-width: 600px; margin: 0 auto; padding: 20px; }
        
        .upload-card {
            background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .upload-card h2 { margin-bottom: 1.5rem; color: #1e293b; text-align: center; }
        
        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; }
        .alert.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #64748b; }
        .form-group input { width: 100%; padding: 0.8rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; }
    </style>
</head>
<body>

    <nav class="staff-nav">
        <div class="staff-brand"><i class="fa-solid fa-heart-pulse"></i> BloodLine Staff</div>
        <div class="nav-user">
            <span>Staff: <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="logout.php" class="btn-logout" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
        </div>
    </nav>

    <div class="staff-container">
        
        <?php if ($message): ?>
            <div class="alert <?php echo $msg_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="upload-card">
            <h2><i class="fa-solid fa-file-medical"></i> Upload Patient Report</h2>
            
            <form action="staff.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>Patient Phone Number</label>
                    <input type="text" name="patient_phone" placeholder="Enter exact phone number" required>
                </div>

                <div class="form-group">
                    <label>Report Name / Type</label>
                    <input type="text" name="report_type" placeholder="e.g. CBC, X-Ray, Dengue Test" required>
                </div>

                <div class="form-group">
                    <label>Select Report File</label>
                    <input type="file" name="file_upload" required>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-upload"></i> Upload Report
                </button>

            </form>
        </div>
    </div>

</body>
</html>