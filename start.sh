#!/bin/bash

echo "🚀 Запуск АудиоДуа в Docker..."

# Копируем конфигурацию для Docker
if [ ! -f includes/config.php ]; then
    echo "📝 Копирую конфигурацию для Docker..."
    cp docker-config.php includes/config.php
else
    echo "⚠️  includes/config.php уже существует, пропускаю копирование"
fi

# Запускаем контейнеры
echo "🐳 Запускаю Docker контейнеры..."
docker-compose up -d

# Ждем пока MySQL инициализируется
echo "⏳ Ожидаю инициализацию MySQL (15 секунд)..."
sleep 15

# Проверяем статус
echo ""
echo "✅ Готово!"
echo ""
echo "📍 Доступные сервисы:"
echo "   🌐 Сайт:        http://localhost:8080"
echo "   🗄️  phpMyAdmin:  http://localhost:8081 (root / root)"
echo ""
echo "📊 Статус контейнеров:"
docker-compose ps
echo ""
echo "📝 Логи: docker-compose logs -f"
echo "🛑 Остановить: docker-compose down"
