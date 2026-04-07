<?php

$pdo = new PDO('sqlite:database/database.sqlite');

echo "Checking sample data...\n";

$result = $pdo->query('SELECT COUNT(*) as count FROM students');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo "Students: " . $row['count'] . "\n";

$result = $pdo->query('SELECT COUNT(*) as count FROM users WHERE role = "student"');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo "Student users: " . $row['count'] . "\n";

$result = $pdo->query('SELECT COUNT(*) as count FROM pre_enrollments');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo "Pre-enrollments: " . $row['count'] . "\n";

if ($row['count'] > 0) {
    $result = $pdo->query('SELECT * FROM pre_enrollments LIMIT 1');
    $enrollment = $result->fetch(PDO::FETCH_ASSOC);
    echo "Sample pre-enrollment status: " . $enrollment['status'] . "\n";
}