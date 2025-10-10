<?php
/**
 * Админ-панель для добавления стихов
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Получаем список категорий для выпадающего списка
try {
    $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY sort_order ASC');
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

$page_title = 'Добавить стих';
$success_message = '';
$error_message = '';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_poem'])) {
    $success_message = '';
    $error_message = '';
    
    // Валидация
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $poem_name = isset($_POST['poem_name']) ? trim($_POST['poem_name']) : '';
    $poem_description = isset($_POST['poem_description']) ? trim($_POST['poem_description']) : '';
    $poem_text = isset($_POST['poem_text']) ? trim($_POST['poem_text']) : '';
    
    if ($category_id <= 0) {
        $error_message = 'Выберите категорию';
    } elseif (empty($poem_name)) {
        $error_message = 'Введите название стиха';
    } elseif (empty($poem_text)) {
        $error_message = 'Введите текст стиха';
    } elseif (!isset($_FILES['audio_file']) || $_FILES['audio_file']['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'Загрузите аудиофайл';
    } else {
        // Парсим текст стиха
        $verses = parse_poem_text($poem_text);
        
        if (empty($verses)) {
            $error_message = 'Не удалось распарсить текст стиха. Проверьте формат.';
        } else {
            try {
                // Начинаем транзакцию
                $pdo->beginTransaction();
                
                // Получаем максимальный sort_order
                $stmt = $pdo->query('SELECT MAX(sort_order) as max_order FROM poems');
                $max_order = $stmt->fetch()['max_order'] ?? 0;
                $new_order = $max_order + 1;
                
                // Добавляем стих
                $stmt = $pdo->prepare('
                    INSERT INTO poems (sort_order, name, description, category_id) 
                    VALUES (?, ?, ?, ?)
                ');
                $stmt->execute([$new_order, $poem_name, $poem_description, $category_id]);
                $poem_id = $pdo->lastInsertId();
                
                // Добавляем части стиха
                $stmt = $pdo->prepare('
                    INSERT INTO verses (audio_timestamp, text, sort_order, is_paragraph_end, poem_id) 
                    VALUES (?, ?, ?, ?, ?)
                ');
                
                foreach ($verses as $index => $verse) {
                    $stmt->execute([
                        $verse['timestamp'],
                        $verse['text'],
                        $index + 1,
                        $verse['is_paragraph_end'],
                        $poem_id
                    ]);
                }
                
                // Загружаем аудиофайл
                $upload_result = upload_audio_file($_FILES['audio_file'], $new_order);
                
                if ($upload_result['success']) {
                    $pdo->commit();
                    $success_message = 'Стих успешно добавлен! <a href="poem.php?id=' . $poem_id . '">Посмотреть</a>';
                    
                    // Очищаем форму
                    $_POST = [];
                } else {
                    $pdo->rollBack();
                    $error_message = 'Ошибка загрузки аудиофайла: ' . $upload_result['error'];
                }
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error_message = DEBUG_MODE ? 'Ошибка БД: ' . $e->getMessage() : 'Ошибка при добавлении стиха';
            }
        }
    }
}

/**
 * Парсинг текста стиха с таймкодами
 * Формат: "Текст строки 2.54"
 * Пустая строка означает конец параграфа для предыдущей строки
 */
function parse_poem_text($text) {
    $lines = explode("\n", $text);
    $verses = [];
    $prev_was_empty = false;
    
    foreach ($lines as $index => $line) {
        $line = trim($line);
        
        // Если строка пустая - помечаем предыдущую как конец параграфа
        if (empty($line)) {
            if (!empty($verses)) {
                $verses[count($verses) - 1]['is_paragraph_end'] = 1;
            }
            continue;
        }
        
        // Ищем таймкод в конце строки
        if (preg_match('/^(.+?)\s+([\d.]+)$/', $line, $matches)) {
            $text = trim($matches[1]);
            $timestamp = (float)$matches[2];
            
            $verses[] = [
                'text' => $text,
                'timestamp' => $timestamp,
                'is_paragraph_end' => 0 // По умолчанию не конец параграфа
            ];
        }
    }
    
    // Последняя строка всегда конец параграфа
    if (!empty($verses)) {
        $verses[count($verses) - 1]['is_paragraph_end'] = 1;
    }
    
    return $verses;
}

/**
 * Загрузка аудиофайла
 */
function upload_audio_file($file, $poem_number) {
    $upload_dir = __DIR__ . '/media/' . $poem_number;
    
    // Создаём директорию если её нет
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return ['success' => false, 'error' => 'Не удалось создать директорию'];
        }
    }
    
    // Проверяем тип файла
    $allowed_types = ['audio/mpeg', 'audio/mp3'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types) && !str_ends_with($file['name'], '.mp3')) {
        return ['success' => false, 'error' => 'Разрешены только MP3 файлы'];
    }
    
    // Сохраняем файл
    $destination = $upload_dir . '/' . $poem_number . '.mp3';
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => 'Не удалось сохранить файл'];
    }
}

include __DIR__ . '/../templates/admin.php';
