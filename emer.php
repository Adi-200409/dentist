<?php

include "conn.php";

if(isset($_POST['submit'])){
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $issue = $_POST['issue'];
    $urgency = $_POST['urgency'];
    // Insert the data into the database
    $sql = "INSERT INTO emergency (name,  phone, issue, urgency) VALUES ('$name', '$phone', '$issue','$urgency')";
    if(mysqli_query($conn, $sql)){
        echo "<script>alert('Emergency call submitted successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}
  
?>