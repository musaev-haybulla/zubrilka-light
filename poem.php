<?php
/**
 * Страница дуа с аудиоплеером
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Получаем ID дуа из URL
$poem_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($poem_id <= 0) {
    show_404('Дуа не найдено');
}

try {
    // Получаем информацию о стихе
    $stmt = $pdo->prepare('
        SELECT name, description, sort_order 
        FROM poems 
        WHERE id = ?
    ');
    $stmt->execute([$poem_id]);
    $poem = $stmt->fetch();
    
    if (!$poem) {
        show_404('Дуа с таким ID не найдено');
    }
    
    $poem_name = $poem['name'];
    $description = $poem['description'];
    $poem_number = $poem['sort_order'];
    
    // Получаем все части (verses) стиха
    $stmt = $pdo->prepare('
        SELECT audio_timestamp, text, is_paragraph_end 
        FROM verses 
        WHERE poem_id = ? 
        ORDER BY sort_order ASC
    ');
    $stmt->execute([$poem_id]);
    $verses = $stmt->fetchAll();
    
    if (empty($verses)) {
        show_error('Дуа с таким ID не найдено');
    }
    
} catch (PDOException $e) {
    if (DEBUG_MODE) {
        show_error('Ошибка базы данных: ' . $e->getMessage());
    } else {
        show_error('Произошла ошибка. Пожалуйста, попробуйте позже.');
    }
}

// Подключаем шаблон с Howler.js
$page_title = 'Зубрилка - учи стихи легко!';
include __DIR__ . '/templates/poem.php';
