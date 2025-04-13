<?php


if (isset($_POST['book'])) {
    include 'conn.php'; // make sure this file has a valid DB connection

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $appointmentdate = $_POST['date'];
    $appointmenttime = $_POST['time'];
    $area = $_POST['area'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $postcode = $_POST['postcode'];

    // Optional: You can add basic validation here before saving to DB

    $stmt = $conn->prepare("INSERT INTO appointment (name, phone, email, appointmentdate, appointmenttime, area, city, state, postcode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $name, $phone, $email, $appointmentdate, $appointmenttime, $area, $city, $state, $postcode);

    if ($stmt->execute()) {
        echo "<script>alert('Appointment booked successfully!'); window.location.href='thankyou.html';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
