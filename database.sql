-- Новая структура базы данных для АудиоДуа
-- Миграция с обновленными названиями таблиц и колонок

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- Структура таблицы `categories`
-- --------------------------------------------------------

CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Структура таблицы `poems`
-- --------------------------------------------------------

CREATE TABLE `poems` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sort_order` int NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sort_order` (`sort_order`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Структура таблицы `verses`
-- --------------------------------------------------------

CREATE TABLE `verses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `audio_timestamp` float NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL,
  `is_paragraph_end` tinyint(1) NOT NULL DEFAULT '0',
  `poem_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `poem_id` (`poem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
