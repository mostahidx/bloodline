# 🩸 BloodLine - Hospital Management System

BloodLine is a comprehensive, multi-role hospital management and patient portal system. Built from the ground up using raw PHP, MySQL, and Vanilla JavaScript, it centralizes healthcare records, streamlines appointment scheduling, and manages hospital bed inventory across multiple regional branches.

## 🚀 Tech Stack

* **Backend:** Raw PHP (Session-based authentication, CRUD operations, File handling)
* **Database:** MySQL / MariaDB (Highly relational schema with cascade deletion)
* **Frontend:** HTML5, CSS3 (Custom styling, Grid/Flexbox layouts)
* **Interactivity:** Vanilla JavaScript (AJAX for dynamic dropdowns, DOM manipulation, Modals)
* **Icons:** FontAwesome 6.4.0

## ✨ Key Features

### 🧑‍⚕️ Multi-Role Architecture
* **Patient Portal:** Secure registration and login using email or phone number.
* **Admin Dashboard:** Dedicated access for system administrators to manage system-wide data.
* **Staff Portal:** Hospital-specific access for on-site staff to upload reports directly to patient accounts using their registered phone numbers.

### 🩺 Patient Services (`index.php`)
* **Dynamic Appointment Booking:** AJAX-driven cascading dropdowns filter by `City -> Area -> Hospital -> Department -> Doctor` to fetch real-time consultation fees and availability.
* **Centralized Medical Records:** Patients can upload, view, download, and delete prescription PDFs and medical test reports.
* **Medicine Tracker:** A dashboard widget to keep track of prescribed medicines, dosage times, and remaining days.
* **Real-Time Bed Reservations:** View live bed availability (General, AC Cabin, ICU) across hospitals and instantly reserve them.
* **Billing & Payments:** A consolidated view of unpaid appointment fees and bed reservation costs, complete with a simulated checkout modal supporting Card, bKash, and Nagad payments.

### 🏥 Admin Controls (`admindash.php`)
* Register new branch locations and hospitals globally.
* Add doctors, assign them to specific departments, and set their consultation fees.
* Manage hospital bed inventories (type, total count, cost per day).
* Create and assign staff accounts to specific hospital branches for localized management.

### 📋 Staff Controls (`staff.php`)
* Rapidly search for patients using their registered contact number.
* Upload lab results, X-rays, and test reports directly to a patient's centralized profile.

## 🗄️ Database Schema Highlights

The system relies on a strictly typed, relational SQL schema to maintain data integrity:

* **Users & Roles:** A central Users table branches into Admins, Staff, and Patients, connected via Foreign Keys to handle role-specific attributes.
* **Hospital Operations:** Hospital, Doctors, and Beds tables track physical infrastructure and staff assignments.
* **Patient Records:** Medicines, Prescriptions, and Test_Reports are tied directly to the Patients table. File paths are securely stored in the DB while physical files live in the `uploads/` directory.
* **Financial & Logistical Tracking:** Appointments, Reservations, and Payments map the lifecycle of a patient's visit, with statuses toggling automatically upon successful mock payment.

## ⚙️ Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/BloodLine.git
   ```
2. **Environment Setup:**
   * Ensure you have a local PHP server running (e.g., XAMPP, WAMP, LAMP stack).
   * Move the project folder into your server's root web directory (`htdocs` for XAMPP).
3. **Database Configuration:**
   * Open phpMyAdmin (or your preferred MySQL client) and create a new database named `bloodline`.
   * Import the schema: Run the SQL queries provided in `DATABASE LATEST LATEST.txt`.
   * Populate dummy data: Run the SQL queries provided in `INSERT VALUES.txt` to instantly populate hospitals, doctors, beds, and test accounts.
4. **Create the Uploads Directory:**
   * Create a folder named `uploads` in the root directory of the project.
   * Ensure this folder has read/write permissions so PHP can successfully move uploaded prescriptions and reports into it.
5. **Connect the Application:**
   * Open `database.php` and verify the connection credentials match your local setup:
     ```php
     $servername="localhost";
     $username="root";
     $password=""; // Add your MySQL password if applicable
     $databasename="bloodline";
     ```
6. **Launch:**
   * Navigate to `http://localhost/BloodLine` in your browser.

## 🧪 Test Accounts (From Dummy Data)

* **Admin:** Phone: 01888888888 | Pass: admin123_hash
* **Staff:** Hospital ID: 1 | Pass: staff123_hash
* **Patient:** Email: john@gmail.com | Pass: pat123_hash
