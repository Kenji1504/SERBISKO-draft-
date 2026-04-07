<?php

// Check exact user data
$pdo = new PDO('sqlite:database/database.sqlite');

echo "Checking user data in detail...\n";

$result = $pdo->query("SELECT id, first_name, last_name, middle_name, birthday, role, password, deleted_at FROM users WHERE first_name = 'Super' AND last_name = 'Admin'");
$users = $result->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    echo "\nUser ID: {$user['id']}\n";
    echo "Name: {$user['first_name']} {$user['last_name']}\n";
    echo "Middle: " . ($user['middle_name'] ?: 'NULL') . "\n";
    echo "Birthday: '{$user['birthday']}'\n";
    echo "Role: {$user['role']}\n";
    echo "Deleted: " . ($user['deleted_at'] ?: 'NULL') . "\n";
    echo "Password hash starts with: " . substr($user['password'], 0, 10) . "...\n";

    // Test password
    $passwordCorrect = password_verify('admin123', $user['password']);
    echo "Password 'admin123' correct: " . ($passwordCorrect ? 'YES' : 'NO') . "\n";
}

echo "\nTesting login query...\n";

// Test the exact query from AuthController
$stmt = $pdo->prepare("
    SELECT id, first_name, last_name, birthday, deleted_at
    FROM users
    WHERE last_name = ?
    AND first_name = ?
    AND birthday = ?
    AND (middle_name IS NULL OR middle_name = '')
");

$stmt->execute(['Admin', 'Super', '2000-01-01']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "Login query FOUND user: {$user['first_name']} {$user['last_name']}\n";
    echo "Birthday in DB: '{$user['birthday']}'\n";
    echo "Deleted: " . ($user['deleted_at'] ?: 'NULL') . "\n";
} else {
    echo "Login query found NO users!\n";

    // Try with datetime format
    echo "Trying with datetime format...\n";
    $stmt->execute(['Admin', 'Super', '2000-01-01 00:00:00']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "Found with datetime format!\n";
    } else {
        echo "Still not found\n";
    }
}