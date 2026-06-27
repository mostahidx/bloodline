<?php
session_start();
include "database.php";

$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : 0;
$user_name = $is_logged_in ? $_SESSION['user_name'] : "Guest";

// -- FETCH DATA ONLY IF LOGGED IN --
$res_appt = null;
$res_med = null;
$res_pres = null;
$res_rep = null;
$res_bill_appt = null;
$res_bill_resv = null;

if ($is_logged_in && $_SESSION['user_type'] == 'Patient') {
    // 1. Appointments
    $sql_appt = "SELECT * FROM Appointments 
                 JOIN Doctors ON Appointments.doctor_id = Doctors.doctor_id 
                 WHERE Appointments.patient_id = '$user_id' AND Appointments.status != 'Completed'
                 ORDER BY appt_date ASC";
    $res_appt = mysqli_query($conn, $sql_appt);

    // 2. Medicines
    $sql_med = "SELECT * FROM Medicines WHERE patient_id = '$user_id'";
    $res_med = mysqli_query($conn, $sql_med);

    // 3. Prescriptions
    $sql_pres = "SELECT * FROM Prescriptions WHERE patient_id = '$user_id' ORDER BY prescription_date DESC";
    $res_pres = mysqli_query($conn, $sql_pres);

    // 4. Reports
    $sql_rep = "SELECT * FROM Test_Reports WHERE patient_id = '$user_id' ORDER BY report_date DESC";
    $res_rep = mysqli_query($conn, $sql_rep);

    // 5. BILLING: Fetch Unpaid Appointments
    $sql_bill_appt = "SELECT * FROM Appointments 
                 JOIN Doctors ON Appointments.doctor_id = Doctors.doctor_id
                 WHERE Appointments.patient_id = '$user_id' AND Appointments.status = 'Scheduled'";
    $res_bill_appt = mysqli_query($conn, $sql_bill_appt);

    // 6. BILLING: Fetch Unpaid Bed Reservations
    $sql_bill_resv = "SELECT * FROM Reservations 
                      JOIN Beds ON Reservations.bed_id = Beds.bed_id
                      WHERE Reservations.patient_id = '$user_id' AND Reservations.status = 'Pending'";
    $res_bill_resv = mysqli_query($conn, $sql_bill_resv);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloodLine - Hospital Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    
    <style>
        <?php if ($is_logged_in): ?>
            #auth-container { display: none !important; }
            #app-container { display: block !important; }
        <?php else: ?>
            #auth-container { display: flex !important; }
            #app-container { display: none !important; }
        <?php endif; ?>
        
        button:disabled { background-color: #cbd5e1; cursor: not-allowed; }
        .auth-tab, .switch-text span { cursor: pointer; z-index: 9999; position: relative; }
    </style>
</head>
<body>

    <div id="auth-container" class="auth-container">
        <div class="auth-box">
            <div class="logo-area">
                <i class="fa-solid fa-heart-pulse"></i>
                <h1>BloodLine</h1>
            </div>

            <div class="auth-tabs">
                <button type="button" class="auth-tab active" id="btn-patient">Patient</button>
                <button type="button" class="auth-tab" id="btn-staff">Staff</button>
                <button type="button" class="auth-tab" id="btn-admin">Admin</button>
            </div>

            <form action="login.php" method="POST" id="login-form" class="form-section" style="display: block;">
                <h2 id="login-title">Patient Sign In</h2>
                
                <div id="patient-login-inputs">
                    <div class="input-group"><i class="fa-solid fa-user"></i><input type="text" name="email" placeholder="Email or Phone"></div>
                </div>

                <div id="staff-login-inputs" style="display: none;">
                    <div class="input-group"><i class="fa-solid fa-hospital"></i><input type="text" name="hospital_id" placeholder="Hospital ID"></div>
                </div>

                <div id="admin-login-inputs" style="display: none;">
                    <div class="input-group"><i class="fa-solid fa-user-shield"></i><input type="text" name="admin_contact" placeholder="Admin Phone Number"></div>
                </div>

                <div class="input-group"><i class="fa-solid fa-lock"></i><input type="password" name="password" placeholder="Password"></div>
                <input type="hidden" name="user_type" id="user_type_input" value="patient">
                <button type="submit" class="btn-primary">Sign In</button>
                
                <p class="switch-text">New here? <span id="btn-signup-toggle">Create Account</span></p>
            </form>

            <form action="register.php" method="POST" id="signup-form" class="form-section" style="display: none;">
                <h2>Patient Registration</h2>
                <div class="input-group"><i class="fa-solid fa-user"></i><input type="text" name="name" placeholder="Full Name" required></div>
                <div class="input-group"><i class="fa-solid fa-envelope"></i><input type="text" name="email" placeholder="Email" required></div>
                <div class="input-group"><i class="fa-solid fa-location-dot"></i><input type="text" name="address" placeholder="Address" required></div>
                <div class="input-group"><i class="fa-solid fa-phone"></i><input type="text" name="phone" placeholder="Phone" required></div>
                <div class="input-group"><i class="fa-solid fa-lock"></i><input type="password" name="password" placeholder="Password" required></div>
                <button type="submit" class="btn-primary">Sign Up</button>
                <p class="switch-text">Have an account? <span id="btn-login-toggle">Sign In</span></p>
            </form>
        </div>
    </div>

    <div id="app-container">
        
        <nav class="navbar">
            <div class="nav-brand"><i class="fa-solid fa-heart-pulse"></i> BloodLine</div>
            <ul class="nav-links">
                <li class="active" onclick="showSection('dashboard')"><i class="fa-solid fa-chart-line"></i> Dashboard</li>
                <li onclick="showSection('appointments')"><i class="fa-solid fa-calendar-check"></i> Appointments</li>
                <li onclick="showSection('medicines')"><i class="fa-solid fa-pills"></i> Medicines</li>
                <li onclick="showSection('prescriptions')"><i class="fa-solid fa-file-prescription"></i> Prescriptions</li>
                <li onclick="showSection('reports')"><i class="fa-solid fa-file-medical"></i> Reports</li>
                <li onclick="showSection('beds')"><i class="fa-solid fa-bed-pulse"></i> Beds</li>
                <li onclick="showSection('payments')"><i class="fa-solid fa-credit-card"></i> Payments</li>
            </ul>
            <div class="nav-user">
                <span id="user-name-display"><?php echo htmlspecialchars($user_name); ?></span>
                <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </nav>

        <main class="content-wrapper">

            <section id="dashboard" class="view-section active">
                <h2>Welcome Back, <?php echo htmlspecialchars($user_name); ?>!</h2>
                
                <div class="stats-grid">
                    <div class="stat-card blue">
                        <div class="icon"><i class="fa-solid fa-calendar-day"></i></div>
                        <div class="info">
                            <h3>Appointments</h3>
                            <p><?php echo ($res_appt) ? mysqli_num_rows($res_appt) : 0; ?> Upcoming</p>
                        </div>
                    </div>
                    <div class="stat-card green">
                        <div class="icon"><i class="fa-solid fa-file-invoice"></i></div>
                        <div class="info">
                            <h3>Reports</h3>
                            <p><?php echo ($res_rep) ? mysqli_num_rows($res_rep) : 0; ?> Available</p>
                        </div>
                    </div>
                    <div class="stat-card red">
                        <div class="icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                        <div class="info">
                            <h3>Billing</h3>
                            <p><?php echo ($res_bill_appt) ? mysqli_num_rows($res_bill_appt) : 0; ?> Pending</p>
                        </div>
                    </div>
                </div>

                <div class="dashboard-split">
                    <div class="dash-window">
                        <div class="window-header"><h3><i class="fa-solid fa-clock"></i> Upcoming Appointment</h3></div>
                        <?php 
                        if ($res_appt) {
                            mysqli_data_seek($res_appt, 0);
                            if($row = mysqli_fetch_assoc($res_appt)) {
                                $dateObj = date_create($row['appt_date']);
                                echo '<div class="appt-card-preview">
                                    <div class="date-box"><span>'.date_format($dateObj, "d").'</span><small>'.date_format($dateObj, "M").'</small></div>
                                    <div class="appt-details">
                                        <h4>'.$row['doctor_name'].'</h4>
                                        <p>'.$row['department_name'].' • '.$row['appt_time_slot'].'</p>
                                        <span class="badge pending">Status: '.$row['status'].'</span>
                                    </div>
                                </div>';
                            } else { echo "<p>No upcoming appointments.</p>"; }
                        }
                        ?>
                    </div>

                    <div class="dash-window">
                        <div class="window-header"><h3><i class="fa-solid fa-capsules"></i> Medicine Schedule</h3></div>
                        <?php
                        if($res_med && mysqli_num_rows($res_med) > 0) {
                            mysqli_data_seek($res_med, 0);
                            while($row = mysqli_fetch_assoc($res_med)) {
                                echo '<div class="med-item">
                                    <div class="med-info"><strong>'.$row['medicine_name'].'</strong><small>'.$row['dosage_time'].'</small></div>
                                    <div class="med-actions"><button class="btn-take"><i class="fa-solid fa-check"></i></button></div>
                                </div>';
                            }
                        } else { echo "<p>No medicines added.</p>"; }
                        ?>
                    </div>
                </div>
            </section>

            <section id="appointments" class="view-section">
                <h2>Book an Appointment</h2>
                <form action="book_appointment.php" method="POST" class="appointment-container">
                    <div class="form-grid">
                        
                        <div class="form-group">
                            <label>City</label>
                            <select name="city" id="city_select" required><option value="">Loading...</option></select>
                        </div>
                        <div class="form-group">
                            <label>Area</label>
                            <select name="area" id="area_select" disabled required><option value="">-- Select City First --</option></select>
                        </div>
                        <div class="form-group">
                            <label>Hospital</label>
                            <select name="hospital_id" id="hospital_select" disabled required><option value="">-- Select Area First --</option></select>
                        </div>
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department" id="department_select" disabled required><option value="">-- Select Hospital First --</option></select>
                        </div>
                        <div class="form-group">
                            <label>Doctor</label>
                            <select name="doctor_id" id="doctor_select" disabled required><option value="" data-price="0">-- Select Department First --</option></select>
                        </div>
                        <div class="form-group">
                            <label>Time Slot</label>
                            <select name="time_slot">
                                <option>4:00 PM - 5:00 PM</option>
                                <option>6:00 PM - 7:00 PM</option>
                                <option>8:00 PM - 9:00 PM</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="appt-summary">
                        <h3>Appointment Summary</h3>
                        <div class="price-box">
                            <span>Visit Fee:</span>
                            <h2 id="appt-price">৳ 0</h2>
                        </div>
                        <button type="submit" class="btn-primary full-width">Book Appointment</button>
                    </div>
                </form>

                 <div style="margin-top: 2rem;">
                    <h3>Your Scheduled Appointments</h3>
                    <div class="grid-list">
                    <?php
                    if ($res_appt) {
                        mysqli_data_seek($res_appt, 0);
                        while($row = mysqli_fetch_assoc($res_appt)) {
                            echo '<div class="file-card">
                                <div class="file-icon"><i class="fa-solid fa-user-doctor"></i></div>
                                <div class="file-info">
                                    <h4>'.$row['doctor_name'].'</h4>
                                    <small>'.$row['appt_date'].' @ '.$row['appt_time_slot'].'</small>
                                    <p>Status: '.$row['status'].'</p>
                                </div>
                            </div>';
                        }
                    }
                    ?>
                    </div>
                 </div>
            </section>

            <section id="medicines" class="view-section">
                <h2>Manage Medicines</h2>
                <div class="med-manager">
                    <form action="medicine.php" method="POST" class="add-med-box">
                        <input type="text" name="med_name" placeholder="Medicine Name" required>
                        <input type="text" name="med_time" placeholder="Time (e.g. 8:00 AM)" required>
                        <input type="number" name="med_days" placeholder="Days" style="width: 100px;" required>
                        <button type="submit" class="btn-primary">Add</button>
                    </form>
                    <div class="med-list-container">
                        <h3>Your Medicine List</h3>
                        <ul id="medicine-list">
                            <?php
                            if ($res_med) {
                                mysqli_data_seek($res_med, 0);
                                while($row = mysqli_fetch_assoc($res_med)) {
                                    echo '<li><span><strong>'.$row['medicine_name'].'</strong> - '.$row['dosage_time'].' ('.$row['days_left'].' Days left)</span></li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </section>

            <section id="prescriptions" class="view-section">
                <div class="section-header">
                    <h2>Prescriptions</h2>
                    <form action="upload_prescription.php" method="POST" enctype="multipart/form-data">
                        <input type="text" name="title" placeholder="Prescription Name (e.g. Eye Checkup)" required class="report-input">
                        <input type="file" name="file_upload" required>
                        <button type="submit" class="btn-secondary"><i class="fa-solid fa-upload"></i> Upload</button>
                    </form>
                </div>
                <div class="grid-list">
                    <?php
                    if ($res_pres && mysqli_num_rows($res_pres) > 0) {
                        mysqli_data_seek($res_pres, 0);
                        while ($row = mysqli_fetch_assoc($res_pres)) {
                            $docTitle = !empty($row['title']) ? $row['title'] : "Prescription";
                            echo '<div class="file-card">
                                <div class="file-icon"><i class="fa-solid fa-file-pdf"></i></div>
                                <div class="file-info"><h4>' . htmlspecialchars($docTitle) . '</h4><small>Date: ' . $row['prescription_date'] . '</small></div>
                                <div class="file-actions">
                                    <a href="uploads/' . $row['file_path'] . '" target="_blank"><button type="button"><i class="fa-solid fa-eye"></i></button></a>
                                    <a href="uploads/' . $row['file_path'] . '" download><button type="button"><i class="fa-solid fa-download"></i></button></a>
                                    <a href="delete_prescription.php?id=' . $row['prescription_id'] . '" onclick="return confirm(\'Delete?\');"><button type="button" style="color:#e11d48"><i class="fa-solid fa-trash"></i></button></a>
                                </div>
                            </div>';
                        }
                    } else { echo "<p>No prescriptions found.</p>"; }
                    ?>
                </div>
            </section>

            <section id="reports" class="view-section">
                <div class="section-header">
                    <h2>Medical Reports</h2>
                    <form action="upload_report.php" method="POST" enctype="multipart/form-data">
                        <input type="text" name="report_type" placeholder="Report Name (e.g. CBC Test)" required class="report-input">
                        <input type="file" name="file_upload" required>
                        <button type="submit" class="btn-secondary"><i class="fa-solid fa-upload"></i> Upload</button>
                    </form>
                </div>
                <div class="grid-list">
                    <?php
                    if ($res_rep && mysqli_num_rows($res_rep) > 0) {
                        mysqli_data_seek($res_rep, 0);
                        while ($row = mysqli_fetch_assoc($res_rep)) {
                            echo '<div class="file-card">
                                <div class="file-icon green"><i class="fa-solid fa-file-medical-alt"></i></div>
                                <div class="file-info"><h4>' . htmlspecialchars($row['test_type']) . '</h4><small>Date: ' . $row['report_date'] . '</small></div>
                                <div class="file-actions">
                                    <a href="uploads/' . $row['file_path'] . '" target="_blank"><button type="button"><i class="fa-solid fa-eye"></i></button></a>
                                    <a href="uploads/' . $row['file_path'] . '" download><button type="button"><i class="fa-solid fa-download"></i></button></a>
                                    <a href="delete_report.php?id=' . $row['report_id'] . '" onclick="return confirm(\'Delete?\');"><button type="button" style="color:#e11d48"><i class="fa-solid fa-trash"></i></button></a>
                                </div>
                            </div>';
                        }
                    } else { echo "<p>No reports found.</p>"; }
                    ?>
                </div>
            </section>

            <section id="beds" class="view-section">
                <h2>Bed Reservation</h2>
                <div class="form-grid" style="background: white; padding: 1.5rem; border-radius: 1rem; margin-bottom: 2rem;">
                    <div class="form-group"><label>City</label><select id="bed_city_select"><option value="">Loading...</option></select></div>
                    <div class="form-group"><label>Area</label><select id="bed_area_select" disabled><option value="">-- Select City --</option></select></div>
                    <div class="form-group"><label>Hospital</label><select id="bed_hosp_select" disabled><option value="">-- Select Area --</option></select></div>
                </div>
                <div class="beds-grid" id="bed_display_area">
                    <p style="grid-column: 1/-1; text-align:center;">Please select a hospital to view available beds.</p>
                </div>
            </section>

            <section id="payments" class="view-section">
                <h2>Payments & Billing</h2>
                <div class="payment-tables">
                    <div class="table-box">
                        <h3>Pending Bills</h3>
                        <table>
                            <thead><tr><th>Service</th><th>Amount</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php
                                $has_bills = false;
                                if($res_bill_appt && mysqli_num_rows($res_bill_appt) > 0) {
                                    $has_bills = true;
                                    while($row = mysqli_fetch_assoc($res_bill_appt)) {
                                        echo '<tr>
                                            <td>Appointment - '.$row['doctor_name'].'</td>
                                            <td>৳ '.$row['fee'].'</td>
                                            <td><button class="btn-pay" onclick="openPaymentModal('.$row['fee'].', \'Appt: '.$row['doctor_name'].'\', '.$row['appointment_id'].', \'appointment\')">Pay Now</button></td>
                                        </tr>';
                                    }
                                }
                                if($res_bill_resv && mysqli_num_rows($res_bill_resv) > 0) {
                                    $has_bills = true;
                                    while($row = mysqli_fetch_assoc($res_bill_resv)) {
                                        echo '<tr>
                                            <td>Bed Reservation - '.$row['bed_type'].'</td>
                                            <td>৳ '.$row['cost_per_day'].' (Base Fee)</td>
                                            <td><button class="btn-pay" onclick="openPaymentModal('.$row['cost_per_day'].', \'Bed: '.$row['bed_type'].'\', '.$row['reservation_id'].', \'reservation\')">Pay Now</button></td>
                                        </tr>';
                                    }
                                }
                                if (!$has_bills) echo "<tr><td colspan='3'>No pending bills.</td></tr>";
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <div id="payment-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closePaymentModal()">&times;</span>
            <h3>Complete Payment</h3>
            <p id="pay-desc">Service Name</p>
            <h2 id="pay-amount">৳ 0</h2>
            <form action="process_payment.php" method="POST">
                <input type="hidden" name="amount" id="form-amount">
                <input type="hidden" name="item_id" id="form-item-id">
                <input type="hidden" name="item_type" id="form-item-type">
                
                <div class="payment-methods">
                    <label class="method-card"><input type="radio" name="paymethod" value="Card" checked> <span>Card</span></label>
                    <label class="method-card"><input type="radio" name="paymethod" value="Bkash"> <span>Bkash</span></label>
                    <label class="method-card"><input type="radio" name="paymethod" value="Nagad"> <span>Nagad</span></label>
                </div>
                <div class="payment-inputs">
                    <input type="text" name="account" placeholder="Account/Card Number" required>
                    <input type="password" name="pin" placeholder="PIN / Password" required>
                </div>
                <button type="submit" class="btn-primary full-width">Confirm Payment</button>
            </form>
        </div>
    </div>

    <script src="script.js?v=<?php echo time(); ?>"></script>
</body>
</html>