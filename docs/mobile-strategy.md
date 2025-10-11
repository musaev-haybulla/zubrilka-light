# 📱 Стратегия мобилизации Зубрилки

## Выбранный подход: PWA (Progressive Web App)

### Почему PWA?

1. **Минимальные изменения**: Используем существующий код
2. **Кроссплатформенность**: iOS + Android одновременно
3. **Без магазинов**: Можно использовать через браузер
4. **Офлайн режим**: Кэширование контента
5. **Установка на экран**: Как обычное приложение

---

## 🎯 Этапы реализации

### Этап 1: Подготовка бэкенда (2-3 дня)

#### 1.1 REST API для аудио и стихов

Создать `/api/` эндпоинты:

```php
// api/poems.php - список всех стихов
GET /api/poems
Response: [
  {
    "id": 1,
    "name": "Письмо Татьяны",
    "category": "Пушкин",
    "audio_url": "/media/1/1.mp3"
  }
]

// api/poem.php?id=1 - детали стиха
GET /api/poem?id=1
Response: {
  "id": 1,
  "name": "Письмо Татьяны к Онегину",
  "verses": [
    {
      "text": "Я Вам пишу, чего же боле",
      "timestamp": 2.54,
      "is_paragraph_end": false
    }
  ],
  "audio_url": "/media/1/1.mp3"
}
```

#### 1.2 Оптимизация аудио файлов

- Конвертировать MP3 в разные битрейты (128kbps для мобилок)
- Добавить поддержку progressive download
- Кэширование заголовков для аудио

---

### Этап 2: PWA инфраструктура (3-4 дня)

#### 2.1 Manifest файл

Создать `manifest.json`:

```json
{
  "name": "Зубрилка - учи стихи легко",
  "short_name": "Зубрилка",
  "description": "Система для заучивания стихов с аудио",
  "start_url": "/",
  "display": "standalone",
  "orientation": "portrait",
  "background_color": "#ffffff",
  "theme_color": "#5cb85c",
  "icons": [
    {
      "src": "/images/icon-72x72.png",
      "sizes": "72x72",
      "type": "image/png"
    },
    {
      "src": "/images/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "/images/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ]
}
```

#### 2.2 Service Worker

Создать `sw.js` для кэширования:

```javascript
const CACHE_NAME = 'zubrilka-v1';
const STATIC_CACHE = [
  '/',
  '/assets/css/bootstrap.min.css',
  '/assets/js/my.js',
  'https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.4/howler.min.js'
];

// Установка SW
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(STATIC_CACHE))
  );
});

// Стратегия: Network First для API, Cache First для статики
self.addEventListener('fetch', (event) => {
  const { request } = event;
  
  // API - всегда с сети
  if (request.url.includes('/api/')) {
    event.respondWith(networkFirst(request));
  }
  // Аудио файлы - кэшируем агрессивно
  else if (request.url.includes('/media/')) {
    event.respondWith(cacheFirst(request));
  }
  // Остальное - сеть с fallback на кэш
  else {
    event.respondWith(networkFirst(request));
  }
});

async function cacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;
  
  const response = await fetch(request);
  const cache = await caches.open(CACHE_NAME);
  cache.put(request, response.clone());
  return response;
}

async function networkFirst(request) {
  try {
    const response = await fetch(request);
    const cache = await caches.open(CACHE_NAME);
    cache.put(request, response.clone());
    return response;
  } catch (error) {
    return await caches.match(request);
  }
}
```

#### 2.3 Регистрация SW в главной странице

Добавить в `templates/header.php`:

```javascript
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then(reg => console.log('SW registered:', reg))
      .catch(err => console.log('SW registration failed:', err));
  });
}
```

---

### Этап 3: Мобильная оптимизация UI (3-4 дня)

#### 3.1 Адаптация под мобилки

- Увеличить зоны нажатия (кнопки > 44px)
- Оптимизировать шрифты для маленьких экранов
- Убрать hover эффекты (touch events)
- Добавить swipe жесты для навигации

#### 3.2 Viewport и мета-теги

Обновить `templates/header.php`:

```html
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Зубрилка">
<link rel="apple-touch-icon" href="/images/icon-192x192.png">
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#5cb85c">
```

#### 3.3 Оптимизация управления аудио

- Заменить кнопки на touch-friendly элементы
- Добавить визуальный feedback при нажатии
- Улучшить индикатор прогресса
- Добавить жесты для перемотки

---

### Этап 4: Офлайн режим (2-3 дня)

#### 4.1 Кэширование данных

- LocalStorage для списка стихов
- IndexedDB для аудио файлов
- Синхронизация при появлении сети

#### 4.2 UI для офлайн режима

- Индикатор онлайн/офлайн статуса
- Список доступных офлайн стихов
- Кнопка "Скачать для офлайн"

---

### Этап 5: Тестирование (3-4 дня)

#### 5.1 Тесты на устройствах

- iPhone Safari (iOS 15+)
- Android Chrome (Android 10+)
- iPad Safari
- Разные размеры экранов

#### 5.2 Производительность

- Lighthouse PWA Score > 90
- Время загрузки < 3 сек
- Плавность прокрутки 60 FPS
- Аудио латентность < 100ms

#### 5.3 Офлайн сценарии

- Загрузка без интернета
- Воспроизведение кэшированных стихов
- Синхронизация при восстановлении сети

---

## 📊 Итоговый таймлайн

| Этап | Дни | Описание |
|------|-----|----------|
| Бэкенд API | 2-3 | REST endpoints, оптимизация аудио |
| PWA инфраструктура | 3-4 | Manifest, Service Worker, регистрация |
| Мобильный UI | 3-4 | Адаптация интерфейса, touch оптимизация |
| Офлайн режим | 2-3 | Кэширование, синхронизация |
| Тестирование | 3-4 | Устройства, производительность, баги |
| **ИТОГО** | **13-18 дней** | ~100-140 часов работы |

---

## 🔧 Технические требования

### Новые зависимости

```json
{
  "workbox-webpack-plugin": "^7.0.0",  // Упрощение SW
  "idb": "^8.0.0"                      // IndexedDB wrapper
}
```

### Серверные требования

- HTTPS обязателен (для PWA)
- Поддержка HTTP/2 (желательно)
- Правильные MIME types для аудио
- CORS для API (если нужно)

---

## 🚀 Альтернативный путь: Гибридное приложение

Если PWA не удовлетворит (например, нужны push-уведомления на iOS), можно **позже** обернуть PWA в Capacitor:

```bash
npm install @capacitor/core @capacitor/cli
npx cap init
npx cap add ios
npx cap add android
```

Это добавит:
- Нативную упаковку PWA
- Доступ к App Store / Google Play
- Дополнительные нативные API

**Время**: +1 неделя

---

## 💰 Оценка сложности

### Для вас (веб-разработчик):

- **PWA**: 🟢 Легко (80% знакомых технологий)
- **Service Worker**: 🟡 Средне (новая концепция, но JS)
- **Mobile UI**: 🟢 Легко (CSS + Bootstrap)
- **API**: 🟢 Легко (PHP как обычно)

### Общая сложность: **4/10**

Это **самый простой** путь для перехода на мобилку, сохраняя веб-версию.

---

## 📚 Ресурсы для изучения

### PWA Basics
- [web.dev PWA guide](https://web.dev/progressive-web-apps/)
- [MDN Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)

### Audio на мобилках
- [Howler.js mobile best practices](https://github.com/goldfire/howler.js#mobile-playback)
- [Web Audio API on mobile](https://web.dev/audio-on-mobile/)

### Тестирование
- [Lighthouse CI](https://github.com/GoogleChrome/lighthouse-ci)
- [BrowserStack](https://www.browserstack.com/) для тестов на реальных устройствах

---

## ✅ Следующий шаг

**Вопрос к вам**: Готовы начать с PWA? Я могу:

1. **Создать API endpoints** для стихов и аудио
2. **Настроить Service Worker** с кэшированием
3. **Адаптировать UI** для мобильных устройств
4. **Добавить manifest.json** и иконки

С чего начнем? 🚀
