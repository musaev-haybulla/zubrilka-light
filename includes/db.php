<?php
/**
 * Подключение к базе данных через PDO
 */

require_once __DIR__ . '/config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    $pdo->exec("SET CHARACTER SET utf8mb4");
} catch (PDOException $e) {
    if (DEBUG_MODE) {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    } else {
        die("Ошибка подключения к базе данных. Пожалуйста, попробуйте позже.");
    }
}
