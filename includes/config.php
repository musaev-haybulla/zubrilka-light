<?php
/**
 * Конфигурация для Docker окружения
 * Скопируйте этот файл в includes/config.php для работы с Docker
 */

// Настройки подключения к БД (Docker)
define('DB_HOST', 'db');  // имя сервиса из docker-compose.yml
define('DB_NAME', 'poems_db');
define('DB_USER', 'poems');
define('DB_PASS', 'poems');
define('DB_CHARSET', 'utf8mb4');

// Базовый URL сайта (без слеша в конце)
define('BASE_URL', 'http://localhost:8080');

// Включить отображение ошибок (для разработки)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
