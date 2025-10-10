# 🚀 Быстрый старт с Docker

## Один скрипт для запуска всего

```bash
./start.sh
```

Или вручную:

## Шаг 1: Скопируйте конфигурацию

```bash
cp docker-config.php includes/config.php
```

## Шаг 2: Запустите Docker

```bash
docker-compose up -d
```

## Шаг 3: Подождите 15 секунд

MySQL инициализируется и автоматически:
- ✅ Создаст таблицы (`database.sql`)
- ✅ Попытается мигрировать данные из старых таблиц (`migrate_data.sql`)
- ✅ Загрузит тестовые данные если старых нет (`test_data.sql`)

## Шаг 4: Откройте в браузере

- **Сайт**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
  - Логин: `root`
  - Пароль: `root`

## Что внутри?

- **PHP 8.1** с Apache
- **MySQL 8.0** с автоматической инициализацией
- **phpMyAdmin** для управления БД
- **Тестовые данные** для проверки

## Полезные команды

### Просмотр логов
```bash
docker-compose logs -f
```

### Остановить
```bash
docker-compose down
```

### Перезапустить
```bash
docker-compose restart
```

### Полная переустановка (удалит БД!)
```bash
docker-compose down -v
docker-compose up -d
```

## Импорт реальных данных

Если у вас есть дамп старой БД:

```bash
# Импортируйте старый дамп
docker exec -i audiodua-db mysql -u root -proot audiodua_db < /path/to/old_dump.sql

# Выполните миграцию
docker exec -i audiodua-db mysql -u root -proot audiodua_db < migrate_data.sql
```

## Требования

- OrbStack или Docker Desktop
- Свободные порты: 8080, 8081, 3306

Всё! 🎉
