<?php
/**
 * Главная страница - список категорий и дуа
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Получаем все категории с их дуа
try {
    $stmt = $pdo->query('
        SELECT * 
        FROM categories 
        ORDER BY sort_order ASC
    ');
    $categories = $stmt->fetchAll();
    
    if (empty($categories)) {
        show_error('Категорий нет');
    }
    
    // Для каждой категории получаем дуа
    foreach ($categories as $key => $category) {
        $stmt = $pdo->prepare('
            SELECT * 
            FROM poems 
            WHERE category_id = ? 
            ORDER BY sort_order ASC
        ');
        $stmt->execute([$category['id']]);
        $categories[$key]['poems'] = $stmt->fetchAll();
    }
    
} catch (PDOException $e) {
    if (DEBUG_MODE) {
        show_error('Ошибка базы данных: ' . $e->getMessage());
    } else {
        show_error('Произошла ошибка. Пожалуйста, попробуйте позже.');
    }
}

// Подключаем шаблон
$page_title = 'АудиоДуа.Онлайн - система заучивания дуа';
include __DIR__ . '/templates/index.php';
