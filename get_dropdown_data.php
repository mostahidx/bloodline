<?php
include "database.php";

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // 1. GET CITIES
    if ($action == 'get_cities') {
        $sql = "SELECT DISTINCT city FROM Hospital ORDER BY city ASC";
        $result = mysqli_query($conn, $sql);
        echo '<option value="">-- Select City --</option>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<option value="' . $row['city'] . '">' . $row['city'] . '</option>';
        }
    }

    // 2. GET AREAS
    if ($action == 'get_areas' && isset($_POST['city'])) {
        $city = mysqli_real_escape_string($conn, $_POST['city']);
        $sql = "SELECT DISTINCT area FROM Hospital WHERE city = '$city' ORDER BY area ASC";
        $result = mysqli_query($conn, $sql);
        echo '<option value="">-- Select Area --</option>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<option value="' . $row['area'] . '">' . $row['area'] . '</option>';
        }
    }

    // 3. GET HOSPITALS
    if ($action == 'get_hospitals' && isset($_POST['area'])) {
        $area = mysqli_real_escape_string($conn, $_POST['area']);
        $sql = "SELECT hospital_id, name FROM Hospital WHERE area = '$area' ORDER BY name ASC";
        $result = mysqli_query($conn, $sql);
        echo '<option value="">-- Select Hospital --</option>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<option value="' . $row['hospital_id'] . '">' . $row['name'] . '</option>';
        }
    }

    // 4. GET DEPARTMENTS
    if ($action == 'get_departments' && isset($_POST['hospital_id'])) {
        $hospital_id = mysqli_real_escape_string($conn, $_POST['hospital_id']);
        $sql = "SELECT DISTINCT department_name FROM Doctors WHERE hospital_id = '$hospital_id' ORDER BY department_name ASC";
        $result = mysqli_query($conn, $sql);
        echo '<option value="">-- Select Department --</option>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<option value="' . $row['department_name'] . '">' . $row['department_name'] . '</option>';
        }
    }

    // 5. GET DOCTORS
    if ($action == 'get_doctors' && isset($_POST['hospital_id']) && isset($_POST['department'])) {
        $hospital_id = mysqli_real_escape_string($conn, $_POST['hospital_id']);
        $department = mysqli_real_escape_string($conn, $_POST['department']);
        
        $sql = "SELECT doctor_id, doctor_name, fee FROM Doctors 
                WHERE hospital_id = '$hospital_id' AND department_name = '$department' 
                ORDER BY doctor_name ASC";
        $result = mysqli_query($conn, $sql);
        
        echo '<option value="">-- Select Doctor --</option>';
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<option value="' . $row['doctor_id'] . '" data-price="' . $row['fee'] . '">' . $row['doctor_name'] . ' (Fees: ' . $row['fee'] . ')</option>';
        }
    }

    // 6. GET BEDS 
    if ($action == 'get_beds' && isset($_POST['hospital_id'])) {
        $hospital_id = mysqli_real_escape_string($conn, $_POST['hospital_id']);
        
        $sql = "SELECT * FROM Beds WHERE hospital_id = '$hospital_id'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            while($bed = mysqli_fetch_assoc($result)) {
                $is_available = ($bed['available_count'] > 0); 
                $icon = (strpos(strtolower($bed['bed_type']), 'icu') !== false) ? 'fa-procedures' : 'fa-bed';
                
                echo '
                <form action="reserve_bed.php" method="POST" class="bed-card">
                    <i class="fa-solid '.$icon.'"></i>
                    <h3>'.$bed['bed_type'].'</h3>
                    <p class="price">৳ '.$bed['cost_per_day'].' / day</p>
                    <span class="status-text" style="color:'.($is_available ? '#10b981' : '#ef4444').'">Available: '.$bed['available_count'].'</span>
                    
                    <input type="hidden" name="bed_id" value="'.$bed['bed_id'].'">
                    <input type="hidden" name="price" value="'.$bed['cost_per_day'].'">
                    <button type="submit" class="btn-primary" '.($is_available ? '' : 'disabled').'>
                        '.($is_available ? 'Reserve Now' : 'Full').'
                    </button>
                </form>';
            }
        } else {
            echo '<p style="grid-column: 1/-1; text-align:center;">No beds found for this hospital.</p>';
        }
    }
}
?>