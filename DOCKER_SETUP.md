# Запуск проекта в Docker (OrbStack)

## Быстрый старт

### 1. Скопируйте конфигурацию для Docker

```bash
cp docker-config.php includes/config.php
```

### 2. Запустите контейнеры

```bash
docker-compose up -d
```

### 3. Подождите ~10 секунд пока MySQL инициализируется

### 4. Откройте в браузере

- **Сайт**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081 (root / root)

## Что будет запущено

- **PHP 8.1 + Apache** на порту 8080
- **MySQL 8.0** на порту 3306
- **phpMyAdmin** на порту 8081

## Полезные команды

### Просмотр логов
```bash
docker-compose logs -f web
docker-compose logs -f db
```

### Остановить контейнеры
```bash
docker-compose down
```

### Остановить и удалить данные БД
```bash
docker-compose down -v
```

### Перезапустить
```bash
docker-compose restart
```

### Зайти в контейнер PHP
```bash
docker exec -it audiodua-web bash
```

### Зайти в MySQL
```bash
docker exec -it audiodua-db mysql -u root -proot audiodua_db
```

## Импорт данных из старой БД

Если у вас уже есть дамп старой БД:

```bash
# 1. Скопируйте дамп в контейнер
docker cp your_old_dump.sql audiodua-db:/tmp/

# 2. Импортируйте
docker exec -i audiodua-db mysql -u root -proot audiodua_db < /tmp/your_old_dump.sql

# 3. Выполните миграцию
docker exec -i audiodua-db mysql -u root -proot audiodua_db < migrate_data.sql
```

## Структура портов

| Сервис | Порт | URL |
|--------|------|-----|
| Web (Apache) | 8080 | http://localhost:8080 |
| MySQL | 3306 | localhost:3306 |
| phpMyAdmin | 8081 | http://localhost:8081 |

## Доступы к БД

**Для приложения:**
- Host: `db` (внутри Docker) или `localhost` (снаружи)
- Database: `audiodua_db`
- User: `audiodua`
- Password: `audiodua`

**Root доступ:**
- User: `root`
- Password: `root`

## Проблемы и решения

### Порт уже занят
Если порт 8080 занят, измените в `docker-compose.yml`:
```yaml
ports:
  - "8888:80"  # вместо 8080
```

### БД не инициализируется
```bash
# Удалите volume и пересоздайте
docker-compose down -v
docker-compose up -d
```

### Нужно пересоздать БД
```bash
docker exec -i audiodua-db mysql -u root -proot -e "DROP DATABASE IF EXISTS audiodua_db; CREATE DATABASE audiodua_db;"
docker exec -i audiodua-db mysql -u root -proot audiodua_db < database.sql
docker exec -i audiodua-db mysql -u root -proot audiodua_db < migrate_data.sql
```

## OrbStack специфика

OrbStack автоматически:
- ✅ Оптимизирует производительность
- ✅ Интегрируется с macOS
- ✅ Предоставляет быстрый доступ к файлам

Никаких дополнительных настроек не требуется!
