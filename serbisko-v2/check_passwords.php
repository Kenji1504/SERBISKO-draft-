<?php
$pdo = new PDO('sqlite:database/database.sqlite');
$stmt = $pdo->query('SELECT id, first_name, password FROM users');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']}, Name: {$row['first_name']}, Password: {$row['password']}, Length: " . strlen($row['password']) . "\n";
}
