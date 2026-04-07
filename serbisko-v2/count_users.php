<?php

$pdo = new PDO('sqlite:database/database.sqlite');
$result = $pdo->query('SELECT COUNT(*) as count FROM users');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo 'Total users: ' . $row['count'] . "\n";

$result = $pdo->query('SELECT id, first_name, last_name, birthday FROM users');
while($user = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "User: {$user['first_name']} {$user['last_name']} (ID: {$user['id']}, DOB: {$user['birthday']})\n";
}