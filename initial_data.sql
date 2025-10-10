-- Начальные данные для Зубрилки
-- Создаём базовую категорию

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Базовая категория
INSERT INTO `categories` (`id`, `name`, `sort_order`) VALUES
(1, 'Школьные стихи', 1);
