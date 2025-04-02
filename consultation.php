<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "dentist");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Collect form data
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $symptoms = $_POST["symptoms"];

    // Handle file upload
    $target_dir = "uploads/";
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate file type
    $allowed_types = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowed_types)) {
        die("Only JPG, JPEG, PNG & GIF files are allowed.");
    }

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Insert data into database
        $sql = "INSERT INTO consultations (name, email, phone, symptoms, image) VALUES ('$name', '$email', '$phone', '$symptoms', '$image_name')";

        if ($conn->query($sql) === TRUE) {
            echo "Consultation submitted successfully!";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "File upload failed!";
    }

    $conn->close();
}
?>
