<?php

$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'iqa_hw2_db';
$username = getenv('DB_USER') ?: 'root';

$passwordFile = getenv('DB_PASSWORD_FILE') ?: '';
if ($passwordFile !== '' && is_readable($passwordFile)) {
    $password = rtrim(file_get_contents($passwordFile), "\r\n");
} else {
    $password = getenv('DB_PASSWORD') ?: '';
}

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed. Please check the application configuration.');
}
