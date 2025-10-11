# Zubrilka - команды для разработки и деплоя

# Показать все доступные команды
default:
    @just --list

# Создать архив для деплоя
deploy-archive:
    #!/usr/bin/env bash
    echo "📦 Создание архива для деплоя..."
    ARCHIVE_NAME="zubrilka-deploy-$(date +%Y%m%d-%H%M%S).zip"
    
    zip -r "$ARCHIVE_NAME" . \
      -x "*.git*" \
      -x "*.DS_Store" \
      -x "*.vscode/*" \
      -x "*.idea/*" \
      -x "*node_modules/*" \
      -x "*vendor/*" \
      -x "*.log" \
      -x "*cache/*" \
      -x "*tmp/*" \
      -x "justfile" \
      -x "README.md" \
      -x "docker-compose.yml" \
      -x "*.zip"
    
    echo "✅ Архив создан: $ARCHIVE_NAME"
    ls -lh "$ARCHIVE_NAME"

# Создать tar.gz архив (альтернатива)
deploy-tar:
    #!/usr/bin/env bash
    echo "📦 Создание tar.gz архива..."
    ARCHIVE_NAME="zubrilka-deploy-$(date +%Y%m%d-%H%M%S).tar.gz"
    
    tar -czf "$ARCHIVE_NAME" \
      --exclude='.git' \
      --exclude='.DS_Store' \
      --exclude='.vscode' \
      --exclude='.idea' \
      --exclude='node_modules' \
      --exclude='vendor' \
      --exclude='*.log' \
      --exclude='cache' \
      --exclude='tmp' \
      --exclude='justfile' \
      --exclude='README.md' \
      --exclude='*.tar.gz' \
      .
    
    echo "✅ Архив создан: $ARCHIVE_NAME"
    ls -lh "$ARCHIVE_NAME"

# Экспорт базы данных
db-export DB_NAME="zubrilka":
    #!/usr/bin/env bash
    echo "💾 Экспорт базы данных..."
    BACKUP_FILE="db-backup-$(date +%Y%m%d-%H%M%S).sql"
    mysqldump -u root {{DB_NAME}} > "$BACKUP_FILE"
    echo "✅ База экспортирована: $BACKUP_FILE"
    ls -lh "$BACKUP_FILE"

# Импорт базы данных
db-import FILE DB_NAME="zubrilka":
    #!/usr/bin/env bash
    echo "📥 Импорт базы данных..."
    mysql -u root {{DB_NAME}} < {{FILE}}
    echo "✅ База импортирована"

# Проверка статуса git
git-status:
    @git status -s

# Коммит и пуш
git-push MESSAGE:
    git add .
    git commit -m "{{MESSAGE}}"
    git push origin main

# Очистка временных файлов
clean:
    #!/usr/bin/env bash
    echo "🧹 Очистка временных файлов..."
    rm -f *.zip *.tar.gz *.log
    find . -name ".DS_Store" -delete
    echo "✅ Очистка завершена"

# Проверка прав доступа
check-permissions:
    #!/usr/bin/env bash
    echo "🔍 Проверка прав доступа..."
    echo "Файлы с правами 777 (небезопасно):"
    find . -type f -perm 777 2>/dev/null || echo "  Не найдено"
    echo ""
    echo "Директории с правами 777:"
    find . -type d -perm 777 2>/dev/null || echo "  Не найдено"

# Установить правильные права (для деплоя)
fix-permissions:
    #!/usr/bin/env bash
    echo "🔧 Установка правильных прав доступа..."
    find . -type f -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;
    chmod 644 includes/config.php
    echo "✅ Права установлены"

# Полный деплой: архив + экспорт БД
deploy-full DB_NAME="zubrilka":
    @echo "🚀 Полный деплой..."
    @just db-export {{DB_NAME}}
    @just deploy-archive
    @echo "✅ Готово! Файлы для загрузки на хостинг:"
    @ls -lht *.zip *.sql | head -2

# Локальный сервер PHP (для тестирования)
serve PORT="8000":
    @echo "🌐 Запуск локального сервера на порту {{PORT}}..."
    @echo "Открой: http://localhost:{{PORT}}"
    php -S localhost:{{PORT}}

# Показать размер проекта
size:
    @echo "📊 Размер проекта:"
    @du -sh .
    @echo ""
    @echo "Топ-5 самых больших директорий:"
    @du -sh */ | sort -hr | head -5

# Деплой через SSH (git pull на сервере)
deploy-ssh HOST USER PATH="/var/www/zubrilka":
    #!/usr/bin/env bash
    echo "🚀 Деплой на сервер через SSH..."
    
    # Проверка незакоммиченных изменений
    if [[ -n $(git status -s) ]]; then
        echo "❌ Есть незакоммиченные изменения!"
        exit 1
    fi
    
    # Push в репозиторий
    echo "📤 Push в репозиторий..."
    git push origin main
    
    # Pull на сервере
    echo "📥 Pull на сервере..."
    ssh {{USER}}@{{HOST}} "cd {{PATH}} && git pull origin main"
    
    echo "✅ Деплой завершён!"
    echo "🌐 Проверь: https://{{HOST}}"

# Деплой через rsync (без git на сервере)
deploy-rsync HOST USER PATH="/var/www/zubrilka":
    #!/usr/bin/env bash
    echo "🚀 Деплой через rsync..."
    
    rsync -avz --delete \
      --exclude='.git' \
      --exclude='.DS_Store' \
      --exclude='.vscode' \
      --exclude='*.zip' \
      --exclude='*.sql' \
      --exclude='justfile' \
      ./ {{USER}}@{{HOST}}:{{PATH}}/
    
    echo "✅ Файлы синхронизированы!"
    echo "🌐 Проверь: https://{{HOST}}"

# Быстрый деплой (из .env файла)
deploy:
    #!/usr/bin/env bash
    if [ ! -f .env ]; then
        echo "❌ Файл .env не найден!"
        echo "Создай его: cp .env.example .env"
        exit 1
    fi
    
    source .env
    
    echo "🚀 Деплой на $DEPLOY_HOST..."
    just deploy-ssh $DEPLOY_HOST $DEPLOY_USER $DEPLOY_PATH

# Запустить OrbStack/Docker
docker-start:
    @echo "🐳 Запуск OrbStack..."
    @open -a OrbStack || echo "⚠️  OrbStack не установлен"

# Остановить OrbStack/Docker
docker-stop:
    @echo "🛑 Остановка OrbStack..."
    @osascript -e 'quit app "OrbStack"' || echo "⚠️  OrbStack не запущен"

# Статус Docker
docker-status:
    @echo "📊 Статус Docker:"
    @docker ps --format "table {{"{{.Names}}"}}\t{{"{{.Status}}"}}\t{{"{{.Ports}}"}}" 2>/dev/null || echo "❌ Docker не запущен"

# Запустить контейнеры проекта (если есть docker-compose.yml)
docker-up:
    @echo "🚀 Запуск контейнеров..."
    @docker-compose up -d

# Остановить контейнеры проекта
docker-down:
    @echo "🛑 Остановка контейнеров..."
    @docker-compose down

# Логи контейнеров
docker-logs SERVICE="":
    #!/usr/bin/env bash
    if [ -z "{{SERVICE}}" ]; then
        docker-compose logs -f
    else
        docker-compose logs -f {{SERVICE}}
    fi
