<?php

file_put_contents('debug.log', "Starting script...\n", FILE_APPEND);

echo "Testing database connection...\n";

try {
    file_put_contents('debug.log', "Connecting to database...\n", FILE_APPEND);
    $pdo = new PDO('sqlite:database/database.sqlite');
    file_put_contents('debug.log', "Connected successfully!\n", FILE_APPEND);
    echo "Database connection successful!\n";

    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    file_put_contents('debug.log', "Tables found: " . count($tables) . "\n", FILE_APPEND);
    echo "Tables found: " . count($tables) . "\n";
    foreach ($tables as $table) {
        file_put_contents('debug.log', "- $table\n", FILE_APPEND);
        echo "- $table\n";
    }

    if (in_array('users', $tables)) {
        file_put_contents('debug.log', "Users table exists. Checking users...\n", FILE_APPEND);
        echo "\nUsers table exists. Checking users...\n";
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        file_put_contents('debug.log', "Total users: " . $result['count'] . "\n", FILE_APPEND);
        echo "Total users: " . $result['count'] . "\n";

        if ($result['count'] > 0) {
            $stmt = $pdo->query("SELECT id, first_name, last_name, birthday FROM users");
            while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $line = "User: {$user['first_name']} {$user['last_name']} (ID: {$user['id']}, DOB: {$user['birthday']})\n";
                file_put_contents('debug.log', $line, FILE_APPEND);
                echo $line;
            }
        } else {
            file_put_contents('debug.log', "No users found. Creating Super Admin...\n", FILE_APPEND);
            echo "No users found. Creating Super Admin...\n";

            // Use bcrypt hash
            $password = password_hash('admin123', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, middle_name, birthday, role, password, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))");
            $stmt->execute(['Super', 'Admin', null, '2000-01-01', 'super_admin', $password]);

            file_put_contents('debug.log', "Super Admin created!\n", FILE_APPEND);
            echo "Super Admin created!\n";
        }
    } else {
        file_put_contents('debug.log', "Users table does not exist!\n", FILE_APPEND);
        echo "Users table does not exist!\n";
    }

} catch (Exception $e) {
    $error = "Error: " . $e->getMessage() . "\n";
    file_put_contents('debug.log', $error, FILE_APPEND);
    echo $error;
}

file_put_contents('debug.log', "Script completed\n", FILE_APPEND);
echo "\n=== LOGIN INSTRUCTIONS ===\n";
echo "Go to: http://localhost:8000\n";
echo "Last Name: Admin\n";
echo "Given Name: Super\n";
echo "Middle Name: (leave blank)\n";
echo "Date of Birth: 2000-01-01\n";
echo "Password: admin123\n";
echo "==========================\n";