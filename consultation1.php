<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "conn.php";

// Create uploads directory if it doesn't exist
$upload_dir = "uploads/consultations/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

try {
    // Create database if not exists
    mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS dentist");
    mysqli_select_db($conn, "dentist");

    // Create consultations table if not exists (removed DROP TABLE command)
    $create_table = "CREATE TABLE IF NOT EXISTS consultations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        age INT NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        preferred_date DATE NOT NULL,
        preferred_time TIME NOT NULL,
        dental_history TEXT,
        current_symptoms TEXT NOT NULL,
        medical_conditions TEXT,
        medications TEXT,
        questions TEXT,
        image_path VARCHAR(255),
        submission_date DATETIME NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'
    )";
    
    if (!mysqli_query($conn, $create_table)) {
        throw new Exception("Error creating table: " . mysqli_error($conn));
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get and sanitize form data
        $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
        $age = mysqli_real_escape_string($conn, $_POST['age'] ?? '');
        $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
        $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
        $preferred_date = mysqli_real_escape_string($conn, $_POST['preferred_date'] ?? '');
        $preferred_time = mysqli_real_escape_string($conn, $_POST['preferred_time'] ?? '');
        $dental_history = mysqli_real_escape_string($conn, $_POST['dental_history'] ?? '');
        $current_symptoms = mysqli_real_escape_string($conn, $_POST['current_symptoms'] ?? '');
        $medical_conditions = mysqli_real_escape_string($conn, $_POST['medical_conditions'] ?? '');
        $medications = mysqli_real_escape_string($conn, $_POST['medications'] ?? '');
        $questions = mysqli_real_escape_string($conn, $_POST['questions'] ?? '');

        // Validate required fields
        if (empty($name) || empty($age) || empty($email) || empty($phone) || empty($preferred_date) || empty($preferred_time) || empty($current_symptoms)) {
            throw new Exception("Please fill in all required fields");
        }

        // Make image upload mandatory
        if (!isset($_FILES['dental_image']) || $_FILES['dental_image']['error'] != 0) {
            throw new Exception("Please upload a dental image");
        }

        // Handle image upload (now mandatory)
        $image_path = null;
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['dental_image']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed)) {
            throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed");
        }

        if ($_FILES['dental_image']['size'] > 5000000) { // 5MB max
            throw new Exception("File size must be less than 5MB");
        }

        // Generate unique filename
        $new_filename = uniqid('dental_', true) . '.' . $file_ext;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['dental_image']['tmp_name'], $upload_path)) {
            $image_path = mysqli_real_escape_string($conn, $upload_path);
        } else {
            throw new Exception("Failed to upload image");
        }

        // Insert data with image path
        $sql = "INSERT INTO consultations (
            name, age, email, phone, preferred_date, preferred_time, 
            dental_history, current_symptoms, medical_conditions, medications, 
            questions, image_path, submission_date
        ) VALUES (
            '$name', '$age', '$email', '$phone', '$preferred_date', '$preferred_time',
            '$dental_history', '$current_symptoms', '$medical_conditions', '$medications',
            '$questions', '$image_path', NOW()
        )";

        if (mysqli_query($conn, $sql)) {
            $success = "Your consultation request has been submitted successfully! We will contact you soon.";
        } else {
            throw new Exception("Error saving data: " . mysqli_error($conn));
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Consultation Request - JUSTSmile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .form-header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #7f8c8d;
            font-size: 1.1em;
        }

        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.2em;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 10px;
            color: #3498db;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        textarea,
        select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input:focus,
        textarea:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        .required {
            color: #e74c3c;
            margin-left: 4px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .alert {
            padding: 15px 30px;
            margin-bottom: 10px;
            border-radius: 5px;
            font-size: 16px;
            opacity: 0;
            transform: translateX(100%);
            animation: slideIn 0.5s forwards, fadeOut 0.5s 2.5s forwards;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .alert i {
            margin-right: 10px;
            font-size: 20px;
        }

        .alert-success {
            background-color: #4CAF50;
            color: white;
            border: none;
        }

        .alert-error {
            background-color: #f44336;
            color: white;
            border: none;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            background-color: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }

        .back-btn i {
            margin-right: 8px;
        }

        .back-btn:hover {
            background-color: #34495e;
            transform: translateX(-5px);
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .form-header h1 {
                font-size: 2em;
            }
        }

        .image-upload-container {
            border: 2px dashed #ddd;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .image-upload-container:hover {
            border-color: #3498db;
        }

        .image-upload-container i {
            font-size: 40px;
            color: #95a5a6;
            margin-bottom: 10px;
        }

        .image-preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .upload-btn {
            background: #ecf0f1;
            color: #2c3e50;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: inline-block;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .upload-btn:hover {
            background: #dfe6e9;
        }

        .file-info {
            margin-top: 10px;
            font-size: 0.9em;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
        
        <div class="form-header">
            <h1>Online Consultation Request</h1>
            <p>Please fill out the form below and we'll get back to you shortly</p>
            </div>
      
        <div id="alert-container" class="alert-container">
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            </div>
      
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-user"></i> Personal Information
                </h3>
                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="name" required placeholder="Enter your full name">
                </div>
      
                <div class="form-group">
                    <label>Age <span class="required">*</span></label>
                    <input type="number" name="age" min="1" max="120" required placeholder="Enter your age">
                </div>
      
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" required placeholder="Enter your email address">
                </div>

                <div class="form-group">
                    <label>Phone Number <span class="required">*</span></label>
                    <input type="tel" name="phone" required placeholder="Enter your phone number">
              </div>
            </div>
      
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-calendar-alt"></i> Preferred Schedule
                </h3>
                <div class="form-group">
                    <label>Preferred Date <span class="required">*</span></label>
                    <input type="date" name="preferred_date" required>
                </div>
      
                <div class="form-group">
                    <label>Preferred Time <span class="required">*</span></label>
                    <input type="time" name="preferred_time" required>
                </div>
                </div>
      
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-notes-medical"></i> Medical Information
                </h3>
                <div class="form-group">
                    <label>Previous Dental History</label>
                    <textarea name="dental_history" placeholder="Please describe any previous dental treatments or procedures..."></textarea>
                </div>
      
                <div class="form-group">
                    <label>Current Symptoms/Concerns <span class="required">*</span></label>
                    <textarea name="current_symptoms" required placeholder="Please describe your current dental issues or concerns..."></textarea>
                </div>
      
                <div class="form-group">
                    <label>Medical Conditions</label>
                    <textarea name="medical_conditions" placeholder="List any medical conditions that might be relevant..."></textarea>
                </div>
      
                <div class="form-group">
                    <label>Current Medications</label>
                    <textarea name="medications" placeholder="List any medications you are currently taking..."></textarea>
                </div>
                </div>
      
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-question-circle"></i> Additional Information
                </h3>
                <div class="form-group">
                    <label>Questions for the Dentist</label>
                    <textarea name="questions" placeholder="Any specific questions you'd like to ask..."></textarea>
              </div>
            </div>
      
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-camera"></i> Dental Images
                </h3>
                <div class="form-group">
                    <label>Upload Dental Images <span class="required">*</span></label>
                    <div class="image-upload-container">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Drag and drop your image here or</p>
                        <div class="file-input-wrapper">
                            <div class="upload-btn">Choose File</div>
                            <input type="file" name="dental_image" id="dental_image" accept="image/*" required onchange="previewImage(this)">
                        </div>
                        <p class="file-info">Maximum file size: 5MB<br>Accepted formats: JPG, JPEG, PNG, GIF</p>
                        <img id="preview" class="image-preview">
                    </div>
                </div>
            </div>
      
            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i> Submit Consultation Request
            </button>
          </form>
        </div>

    <script>
        // Date and Time Validation
        const dateInput = document.querySelector('input[type="date"]');
        const timeInput = document.querySelector('input[type="time"]');
        
        // Set minimum date to today
        function setMinDate() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            const minDate = `${year}-${month}-${day}`;
            dateInput.min = minDate;

            // Validate if selected date is not before today
            dateInput.addEventListener('input', function() {
                const selectedDate = new Date(this.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Reset time part for proper date comparison
                
                if (selectedDate < today) {
                    showAlert('Please select today or a future date', 'error');
                    this.value = '';
                    return;
                }
            });

            // If user selected today's date, disable past times
            if (dateInput.value === minDate) {
                const currentHour = today.getHours();
                const currentMinutes = today.getMinutes();
                const minTime = `${String(currentHour).padStart(2, '0')}:${String(currentMinutes).padStart(2, '0')}`;
                timeInput.min = minTime;
            } else {
                timeInput.min = "09:00";
            }
        }

        // Set initial min date
        setMinDate();

        // Update time restrictions when date changes
        dateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            
            // Validate selected date
            if (selectedDate < today && selectedDate.toDateString() !== today.toDateString()) {
                showAlert('Please select today or a future date', 'error');
                this.value = '';
                return;
            }
            
            // Reset time input
            timeInput.value = '';
            
            // If selected date is today, disable past times
            if (selectedDate.toDateString() === today.toDateString()) {
                const currentHour = today.getHours();
                const currentMinutes = today.getMinutes();
                timeInput.min = `${String(currentHour).padStart(2, '0')}:${String(currentMinutes).padStart(2, '0')}`;
            } else {
                timeInput.min = "09:00";
            }
        });

        // Time validation
        timeInput.addEventListener('change', function() {
            const selectedTime = this.value;
            const [hours, minutes] = selectedTime.split(':').map(Number);
            
            // Check if time is within working hours (9 AM to 7 PM)
            if (hours < 9 || hours >= 19) {
                showAlert('Please select a time between 9:00 AM and 7:00 PM', 'error');
                this.value = '';
                return;
            }
            
            // Check if selected date is today
            const selectedDate = new Date(dateInput.value);
            const today = new Date();
            
            if (selectedDate.toDateString() === today.toDateString()) {
                const currentHour = today.getHours();
                const currentMinutes = today.getMinutes();
                
                // If selected time is in the past
                if (hours < currentHour || (hours === currentHour && minutes <= currentMinutes)) {
                    showAlert('Please select a future time', 'error');
                    this.value = '';
                    return;
                }
            }
        });

        // Add alert functionality
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alert-container');
            
            // Create alert element
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            
            // Create icon element
            const icon = document.createElement('i');
            icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            
            // Add icon and message to alert
            alert.appendChild(icon);
            alert.appendChild(document.createTextNode(' ' + message));
            
            // Add alert to container
            alertContainer.appendChild(alert);

            // Remove alert after animation
            setTimeout(() => {
                alert.remove();
            }, 3000);

            // Scroll to top to show alert if needed
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Form validation with alert messages
        document.querySelector('form').addEventListener('submit', function(e) {
            // Image validation
            const imageInput = document.querySelector('input[name="dental_image"]');
            if (!imageInput.files || !imageInput.files[0]) {
                e.preventDefault();
                showAlert('Please upload a dental image', 'error');
                return;
            }

            // Phone validation
            const phone = document.querySelector('input[name="phone"]').value;
            const phoneRegex = /^[6-9]\d{9}$/;
            
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                showAlert('Please enter a valid 10-digit phone number starting with 6-9', 'error');
                return;
            }

            // Date and time validation
            const selectedDate = dateInput.value;
            const selectedTime = timeInput.value;

            if (!selectedDate || !selectedTime) {
                e.preventDefault();
                showAlert('Please select both date and time', 'error');
                return;
            }

            // Validate working hours
            const [hours] = selectedTime.split(':').map(Number);
            if (hours < 9 || hours >= 19) {
                e.preventDefault();
                showAlert('Please select a time between 9:00 AM and 7:00 PM', 'error');
                return;
            }
        });

        // Update image preview function with alerts
        function previewImage(input) {
            const preview = document.getElementById('preview');
            const container = document.querySelector('.image-upload-container');
            const file = input.files[0];
            
            if (file) {
                // Check file size
                if (file.size > 5000000) { // 5MB
                    showAlert('File size must be less than 5MB', 'error');
                    input.value = '';
                    preview.style.display = 'none';
                    container.style.borderColor = '#e74c3c';
                    return;
                }

                // Check file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    showAlert('Only JPG, JPEG, PNG & GIF files are allowed', 'error');
                    input.value = '';
                    preview.style.display = 'none';
                    container.style.borderColor = '#e74c3c';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    container.style.borderColor = '#2ecc71';
                    showAlert('Image uploaded successfully', 'success');
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
                container.style.borderColor = '#ddd';
            }
        }

        // Drag and drop functionality
        const dropZone = document.querySelector('.image-upload-container');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.style.borderColor = '#3498db';
        }

        function unhighlight(e) {
            dropZone.style.borderColor = '#ddd';
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            const fileInput = document.getElementById('dental_image');
            
            fileInput.files = files;
            previewImage(fileInput);
        }
    </script>
</body>
</html>
