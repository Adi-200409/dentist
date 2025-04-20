<?php
require_once 'conn.php';

header('Content-Type: text/plain');
echo "FIXING USERS TABLE STRUCTURE\n\n";

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Backup existing users
    echo "Backing up existing users...\n";
    $users = [];
    $result = $conn->query("SELECT * FROM users");
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo "Backed up " . count($users) . " users.\n\n";
    
    // Rename existing table
    echo "Renaming existing users table...\n";
    $conn->query("RENAME TABLE users TO users_old");
    
    // Create new table with correct structure
    echo "Creating new users table with correct structure...\n";
    $create = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        password VARCHAR(255) NOT NULL,
        favorite_number INT,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (phone)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->query($create);
    
    // Migrate users from old table to new table
    echo "Migrating users to new table...\n";
    foreach ($users as $user) {
        $stmt = $conn->prepare("INSERT INTO users (id, name, phone, password, favorite_number, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        // Set default values for missing fields
        $id = $user['id'];
        $name = $user['name'];
        $phone = $user['phone'];
        $password = $user['password'];
        $favorite_number = isset($user['favorite_number']) ? (int)$user['favorite_number'] : NULL;
        $role = !empty($user['role']) ? ($user['role'] == 'admin' ? 'admin' : 'user') : 'user';
        $created_at = $user['created_at'];
        
        $stmt->bind_param("issssss", $id, $name, $phone, $password, $favorite_number, $role, $created_at);
        $stmt->execute();
        
        echo "- Migrated user: {$name} (ID: {$id}, Phone: {$phone})\n";
    }
    
    // Drop old table
    echo "\nDropping old users table...\n";
    $conn->query("DROP TABLE users_old");
    
    // Commit transaction
    $conn->commit();
    echo "Fixed users table structure successfully!\n";
    
    // Verify new structure
    echo "\nVerifying new structure:\n";
    $structure = $conn->query("DESCRIBE users");
    while ($col = $structure->fetch_assoc()) {
        echo "- {$col['Field']} ({$col['Type']})" . 
             ($col['Null'] === 'NO' ? ' NOT NULL' : '') . 
             ($col['Key'] === 'PRI' ? ' PRIMARY KEY' : '') . 
             ($col['Key'] === 'UNI' ? ' UNIQUE' : '') . 
             ($col['Default'] ? " DEFAULT '{$col['Default']}'" : '') . 
             "\n";
    }
    
    // Show user count
    $count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
    echo "\nTotal users in fixed table: {$count}\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "ERROR: " . $e->getMessage() . "\n";
    
    // Try to restore users table if it was dropped
    $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
    if ($tableCheck->num_rows === 0 && $conn->query("SHOW TABLES LIKE 'users_old'")->num_rows > 0) {
        echo "Attempting to restore original users table...\n";
        $conn->query("RENAME TABLE users_old TO users");
    }
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

echo "\nDONE!\n";
?> 