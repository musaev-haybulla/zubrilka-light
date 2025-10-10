<?php
/**
 * Вспомогательные функции
 */

/**
 * Экранирование HTML
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Генерация URL
 */
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Генерация URL для assets
 */
function asset($path) {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

/**
 * Генерация URL для медиа файлов
 */
function media($path) {
    return BASE_URL . '/media/' . ltrim($path, '/');
}

/**
 * Генерация URL для изображений
 */
function image($path) {
    return BASE_URL . '/images/' . ltrim($path, '/');
}

/**
 * Редирект
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Отображение ошибки 404
 */
function show_404($message = 'Страница не найдена') {
    http_response_code(404);
    include __DIR__ . '/../templates/404.php';
    exit;
}

/**
 * Отображение ошибки
 */
function show_error($message) {
    http_response_code(500);
    include __DIR__ . '/../templates/error.php';
    exit;
}
