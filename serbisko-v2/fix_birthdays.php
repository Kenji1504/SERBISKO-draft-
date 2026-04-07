<?php

echo "Checking birthdays...\n";
$pdo = new PDO('sqlite:database/database.sqlite');
$result = $pdo->query('SELECT id, birthday FROM users WHERE first_name="Super"');
while($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo 'ID ' . $row['id'] . ': ' . $row['birthday'] . "\n";
}

echo "\nUpdating birthdays to date-only format...\n";
$stmt = $pdo->prepare('UPDATE users SET birthday = ? WHERE first_name = ? AND last_name = ?');
$stmt->execute(['2000-01-01', 'Super', 'Admin']);
echo "Updated!\n";

echo "Checking again...\n";
$result = $pdo->query('SELECT id, birthday FROM users WHERE first_name="Super"');
while($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo 'ID ' . $row['id'] . ': ' . $row['birthday'] . "\n";
}