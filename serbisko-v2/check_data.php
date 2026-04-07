<?php

$pdo = new PDO('sqlite:database/database.sqlite');

$result = $pdo->query('SELECT name FROM sqlite_master WHERE type="table"');
$tables = $result->fetchAll(PDO::FETCH_COLUMN);
echo 'Tables: ' . implode(', ', $tables) . "\n";

$result = $pdo->query('SELECT COUNT(*) as count FROM students');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo 'Students count: ' . $row['count'] . "\n";

$result = $pdo->query('SELECT COUNT(*) as count FROM kiosk_enrollments');
$row = $result->fetch(PDO::FETCH_ASSOC);
echo 'Kiosk enrollments count: ' . $row['count'] . "\n";

if ($row['count'] > 0) {
    echo "Sample kiosk enrollment:\n";
    $result = $pdo->query('SELECT id, student_lrn, academic_status, grade_level FROM kiosk_enrollments LIMIT 1');
    $enrollment = $result->fetch(PDO::FETCH_ASSOC);
    print_r($enrollment);
}