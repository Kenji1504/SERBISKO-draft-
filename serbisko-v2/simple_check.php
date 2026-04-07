<?php

echo "Starting script...\n";
echo "Current directory: " . __DIR__ . "\n";

// Simple script to check SQLite database
try {
    echo "Connecting to database...\n";
    $db = new PDO('sqlite:database/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully\n";

    echo "Checking users table...\n";
    $result = $db->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    echo "Total users: " . $row['count'] . "\n";

    if ($row['count'] > 0) {
        echo "Existing users:\n";
        $result = $db->query("SELECT id, first_name, last_name, middle_name, birthday, role FROM users");
        while ($user = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$user['first_name']} {$user['last_name']} (ID: {$user['id']}, Role: {$user['role']}, Birthday: {$user['birthday']})\n";
        }
    } else {
        echo "No users found. Creating Super Admin...\n";

        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (first_name, last_name, middle_name, extension_name, birthday, role, password, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))");
        $stmt->execute(['Super', 'Admin', null, null, '2000-01-01', 'super_admin', $password]);

        echo "Super Admin created!\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nLogin credentials:\n";
echo "Last Name: Admin\n";
echo "Given Name: Super\n";
echo "Middle Name: (leave blank)\n";
echo "Date of Birth: 2000-01-01\n";
echo "Password: admin123\n";