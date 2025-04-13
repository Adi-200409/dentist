<?php

$server = "localhost";
$username = "root";
$password = ""; // Keep it empty if you're using XAMPP
$dbname = "dentist";

$conn = new mysqli($server, $username, $password, $dbname);

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// } else {
//     echo "Connection successful!";
// }

?>
