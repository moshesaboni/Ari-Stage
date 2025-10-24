<?php
require_once 'config.php';

try {
    // 1. Connect without a database to create it
    $pdo_admin = new PDO('mysql:host='.DB_HOST, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo_admin->exec("CREATE DATABASE IF NOT EXISTS `".DB_NAME."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    // echo "Database '".DB_NAME."' created or already exists.\n";

    // 2. Connect to the specific database to create tables
    $pdo = db();
    if ($pdo === null) {
        throw new Exception("Failed to connect to the database. The db() function returned null.");
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
    CREATE TABLE IF NOT EXISTS songs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        bpm INT,
        song_key VARCHAR(50),
        duration_seconds INT,
        notes TEXT,
        tags VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";

    $pdo->exec($sql);
    // echo "Table 'songs' created successfully (if it didn\'t exist).\n";

} catch (Exception $e) {
    http_response_code(500);
    die("DB ERROR: " . $e->getMessage());
}