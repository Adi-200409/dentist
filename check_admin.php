<?php
require_once "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

// Admin phone number to check
$admin_phone = "9148074307";

try {
    // Check if admin exists
    $stmt = $conn->prepare("SELECT id, name, phone, role FROM users WHERE phone = ?");
    $stmt->bind_param("s", $admin_phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "Admin account found:<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Name: " . $user['name'] . "<br>";
        echo "Phone: " . $user['phone'] . "<br>";
        echo "Role: " . $user['role'] . "<br>";
    } else {
        echo "No admin account found with phone: " . $admin_phone;
    }

    // List all users
    echo "<br><br>All users in database:<br>";
    $all_users = $conn->query("SELECT id, name, phone, role FROM users");
    if ($all_users && $all_users->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Phone</th><th>Role</th></tr>";
        while ($user = $all_users->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['name'] . "</td>";
            echo "<td>" . $user['phone'] . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No users found in the database.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?> 