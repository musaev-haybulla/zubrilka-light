# PWA для разработчика: Глубокое техническое объяснение

## 🎯 Что такое PWA на самом деле?

**PWA = обычный веб-сайт + 3 ключевых компонента:**

1. **Manifest.json** (файл конфигурации)
2. **Service Worker** (JS-воркер в фоне)
3. **HTTPS** (обязательное требование безопасности)

Всё. Больше ничего не меняется в архитектуре.

---

## 📁 Компонент 1: Manifest.json

### Что это?

Обычный JSON-файл, который **описывает ваше веб-приложение как нативное**.

### Зачем?

Браузер читает этот файл и понимает:
- Как называется приложение
- Какую иконку показывать
- В каком режиме открывать (полный экран, standalone, браузер)
- Какой цвет темы использовать

### Где живет?

```
/manifest.json  ← В корне сайта
```

### Минимальный пример:

```json
{
  "name": "Зубрилка - учи стихи легко",
  "short_name": "Зубрилка",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#5cb85c",
  "icons": [
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

### Что означает каждое поле:

| Поле | Что делает | Обязательно? |
|------|------------|--------------|
| `name` | Полное название (показывается при установке) | ✅ Да |
| `short_name` | Короткое название (под иконкой на экране) | ✅ Да |
| `start_url` | С какого URL запускать приложение | ✅ Да |
| `display` | Режим отображения (`standalone`, `fullscreen`, `minimal-ui`) | ✅ Да |
| `background_color` | Цвет экрана загрузки | ⚠️ Нет, но рекомендуется |
| `theme_color` | Цвет строки состояния Android | ⚠️ Нет, но рекомендуется |
| `icons` | Массив иконок разных размеров | ✅ Да (минимум 192x192 и 512x512) |

### Как подключить:

В `<head>` вашей страницы:

```html
<link rel="manifest" href="/manifest.json">
```

### Что происходит после этого:

1. **Браузер скачивает** `manifest.json`
2. **Парсит JSON**
3. **Если всё ОК** → показывает баннер "Установить приложение"
4. **Пользователь нажимает** → создается ярлык с иконкой из `icons[]`

---

## ⚙️ Компонент 2: Service Worker (ключевой момент!)

### Что это?

**Service Worker = JavaScript-файл, который работает в фоне, отдельно от страницы.**

### В чем главная идея?

Обычный JavaScript на странице:
```
Страница → JS код → Выполняется → Страница закрыта → JS умер ❌
```

Service Worker:
```
Страница → Регистрирует SW → SW живет сам по себе ✅
Страница закрыта → SW продолжает работать ✅
```

**Service Worker = прокси между браузером и сетью.**

### Архитектура:

```
┌─────────────────────────────────────────┐
│         Ваша веб-страница               │
│  (index.php, poem.php, JS, CSS)         │
└──────────────┬──────────────────────────┘
               │
               │ Регистрирует SW
               ▼
┌─────────────────────────────────────────┐
│         Service Worker (sw.js)          │ ← Работает в фоне
│  - Перехватывает сетевые запросы        │
│  - Кэширует файлы                       │
│  - Работает даже если страница закрыта  │
└──────────────┬──────────────────────────┘
               │
               │ Запрос файла/API
               ▼
┌─────────────────────────────────────────┐
│  Решает: взять из кэша или из сети?     │
└──────────────┬──────────────────────────┘
               │
        ┌──────┴──────┐
        ▼             ▼
    [Cache]      [Network]
```

### Где живет?

```
/sw.js  ← Обычно в корне сайта
```

### Минимальный пример:

```javascript
// sw.js
const CACHE_NAME = 'zubrilka-v1';

// Событие 1: Установка SW
self.addEventListener('install', (event) => {
  console.log('SW установлен');
  
  // Кэшируем критичные файлы сразу при установке
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll([
        '/',
        '/assets/css/bootstrap.min.css',
        '/assets/js/my.js'
      ]);
    })
  );
});

// Событие 2: Активация SW
self.addEventListener('activate', (event) => {
  console.log('SW активирован');
  
  // Удаляем старые кэши
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Событие 3: Перехват сетевых запросов (ГЛАВНОЕ!)
self.addEventListener('fetch', (event) => {
  console.log('Перехвачен запрос:', event.request.url);
  
  // Стратегия: сначала кэш, потом сеть
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      if (cachedResponse) {
        console.log('Вернули из кэша');
        return cachedResponse;
      }
      
      console.log('Идем в сеть');
      return fetch(event.request);
    })
  );
});
```

### Как зарегистрировать SW:

В вашем `header.php` или главном JS:

```javascript
// Проверяем поддержку
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then((registration) => {
        console.log('SW зарегистрирован:', registration);
      })
      .catch((error) => {
        console.log('Ошибка регистрации SW:', error);
      });
  });
}
```

### Жизненный цикл Service Worker:

```
1. РЕГИСТРАЦИЯ (navigator.serviceWorker.register)
   │
   ▼
2. УСТАНОВКА (install event)
   │ ← Кэшируем критичные файлы
   ▼
3. ОЖИДАНИЕ (waiting)
   │ ← Если уже есть старый SW
   ▼
4. АКТИВАЦИЯ (activate event)
   │ ← Удаляем старые кэши
   ▼
5. РАБОТА (fetch events)
   │ ← Перехватываем запросы
   │
   └─→ [Обновление SW при изменении sw.js]
```

### Важные моменты:

#### 1. **Scope (область действия)**

SW может перехватывать только те запросы, которые идут с его "области":

```javascript
// SW в корне (/) контролирует весь сайт
/sw.js → контролирует /, /poem.php, /api/*, etc.

// SW в подпапке контролирует только её
/admin/sw.js → контролирует только /admin/*
```

#### 2. **SW обновляется автоматически**

Когда вы меняете `sw.js`:

```
Пользователь заходит на сайт
  ↓
Браузер проверяет: изменился ли sw.js?
  ↓
Если да → скачивает новый → устанавливает → ждет закрытия старых вкладок
  ↓
Пользователь закрывает все вкладки
  ↓
Новый SW активируется при следующем визите
```

Чтобы **обновить немедленно**, используют:

```javascript
self.addEventListener('install', (event) => {
  self.skipWaiting(); // ← Активировать сразу, не ждать
});

self.addEventListener('activate', (event) => {
  event.waitUntil(clients.claim()); // ← Захватить текущие страницы
});
```

#### 3. **Кэширование: стратегии**

Есть несколько паттернов работы с кэшем:

##### **Cache First** (сначала кэш):
```javascript
// Быстро, но может быть устаревшим
event.respondWith(
  caches.match(event.request).then((cached) => {
    return cached || fetch(event.request);
  })
);
```

Используют для: статичных файлов (CSS, JS, шрифты).

##### **Network First** (сначала сеть):
```javascript
// Всегда свежее, но медленнее
event.respondWith(
  fetch(event.request)
    .then((response) => {
      // Кэшируем ответ для офлайна
      caches.open(CACHE_NAME).then((cache) => {
        cache.put(event.request, response.clone());
      });
      return response;
    })
    .catch(() => {
      // Если сети нет → из кэша
      return caches.match(event.request);
    })
);
```

Используют для: API запросов, динамического контента.

##### **Stale While Revalidate** (кэш + обновление):
```javascript
// Показываем кэш сразу, обновляем в фоне
event.respondWith(
  caches.open(CACHE_NAME).then((cache) => {
    return cache.match(event.request).then((cached) => {
      const fetchPromise = fetch(event.request).then((networkResponse) => {
        cache.put(event.request, networkResponse.clone());
        return networkResponse;
      });
      
      return cached || fetchPromise; // Кэш или сеть
    });
  })
);
```

Используют для: аватары, изображения, не критичный контент.

---

## 🔒 Компонент 3: HTTPS

### Зачем обязателен HTTPS?

Service Worker = мощный инструмент:
- Перехватывает ВСЕ сетевые запросы
- Может подменять ответы
- Работает в фоне

**Без HTTPS** злоумышленник может:
1. Зарегистрировать свой SW через MITM атаку
2. Перехватывать пароли, данные
3. Показывать фейковый контент

**HTTPS** защищает канал регистрации SW.

### Как получить HTTPS:

- **Локально для разработки**: `localhost` автоматически считается безопасным
- **На продакшене**: Let's Encrypt (бесплатный SSL сертификат)

---

## 🧩 Как всё работает вместе: Полный цикл

### Шаг 1: Первый визит пользователя

```
Пользователь → https://zubrilka.com
       ↓
Браузер скачивает index.php
       ↓
<link rel="manifest"> → Скачивает manifest.json
       ↓
<script> регистрирует SW → Скачивает sw.js
       ↓
SW устанавливается → Кэширует файлы (CSS, JS, страницы)
       ↓
Браузер видит manifest + SW → Показывает "Установить приложение?"
```

**Результат**: Сайт работает, SW установлен, но пока не перехватывает запросы.

### Шаг 2: Второй визит (SW активирован)

```
Пользователь → https://zubrilka.com
       ↓
SW перехватывает запрос
       ↓
Проверяет кэш: есть /? → Да!
       ↓
Возвращает из кэша (мгновенно, без сети)
       ↓
Страница загружается < 100ms ⚡
```

### Шаг 3: Офлайн режим

```
Пользователь → https://zubrilka.com (нет интернета)
       ↓
Браузер пытается fetch() → Ошибка сети
       ↓
SW перехватывает ошибку
       ↓
Возвращает закэшированную версию
       ↓
Сайт работает офлайн! ✅
```

### Шаг 4: Установка как приложение (Android)

```
Пользователь нажимает "Установить"
       ↓
Chrome читает manifest.json
       ↓
Создает WebAPK (легкий Android пакет):
  - Регистрирует иконку в системе
  - Создает ярлык на главном экране
  - Связывает с вашим доменом
       ↓
Пользователь открывает иконку
       ↓
Chrome запускается в standalone режиме (без адресной строки)
       ↓
SW продолжает работать как обычно
```

---

## 💻 Что меняется в вашем коде?

### Для Зубрилки (PHP + jQuery + Howler.js):

#### ❌ Что НЕ меняется:

- **Backend (PHP)**: всё остается как есть
- **БД (MySQL)**: без изменений
- **Логика (JS)**: код работает точно так же
- **Стили (CSS)**: без изменений
- **Howler.js**: работает как и раньше

#### ✅ Что добавляется:

1. **manifest.json** (новый файл)
2. **sw.js** (новый файл)
3. **Регистрация SW** (5-10 строк JS в header.php)
4. **Мета-теги** для PWA (в `<head>`)

### Конкретный пример для Зубрилки:

#### Файл 1: `/manifest.json`

```json
{
  "name": "Зубрилка - учи стихи легко",
  "short_name": "Зубрилка",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#5cb85c",
  "icons": [
    {
      "src": "/images/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "/images/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any maskable"
    }
  ]
}
```

#### Файл 2: `/sw.js`

```javascript
const CACHE_NAME = 'zubrilka-v1';
const STATIC_ASSETS = [
  '/',
  '/index.php',
  '/assets/css/bootstrap.min.css',
  '/assets/css/modern-business.css',
  'https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.4/howler.min.js'
];

// Установка: кэшируем статику
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(STATIC_ASSETS))
      .then(() => self.skipWaiting())
  );
});

// Активация: удаляем старые кэши
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((key) => key !== CACHE_NAME)
          .map((key) => caches.delete(key))
      );
    }).then(() => self.clients.claim())
  );
});

// Перехват запросов
self.addEventListener('fetch', (event) => {
  const { request } = event;
  
  // Аудио файлы: Cache First (быстрая загрузка)
  if (request.url.includes('/media/')) {
    event.respondWith(
      caches.match(request).then((cached) => {
        if (cached) return cached;
        
        return fetch(request).then((response) => {
          return caches.open(CACHE_NAME).then((cache) => {
            cache.put(request, response.clone());
            return response;
          });
        });
      })
    );
  }
  // API: Network First (свежие данные)
  else if (request.url.includes('/api/')) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          return caches.open(CACHE_NAME).then((cache) => {
            cache.put(request, response.clone());
            return response;
          });
        })
        .catch(() => caches.match(request))
    );
  }
  // Остальное: Network First с fallback на кэш
  else {
    event.respondWith(
      fetch(request)
        .catch(() => caches.match(request))
    );
  }
});
```

#### Файл 3: Изменения в `templates/header.php`

```php
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- PWA мета-теги -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#5cb85c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Зубрилка">
    <link rel="apple-touch-icon" href="/images/icon-192x192.png">
    
    <!-- Остальной код без изменений -->
    ...
</head>
<body>
    
    <!-- Регистрация Service Worker -->
    <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
          .then(reg => console.log('SW registered', reg))
          .catch(err => console.log('SW error', err));
      });
    }
    </script>
```

---

## 🔍 Отладка PWA

### Chrome DevTools:

1. **Application tab** → Service Workers
   - Видно: статус, lifecycle, можно удалить/обновить
   
2. **Application tab** → Manifest
   - Видно: как браузер парсит ваш manifest.json
   
3. **Application tab** → Cache Storage
   - Видно: все закэшированные файлы

4. **Network tab** → Filter by Service Worker
   - Видно: какие запросы обслуживает SW

### Lighthouse:

```bash
# В Chrome DevTools → Lighthouse → Run PWA audit
```

Проверяет:
- Manifest корректен?
- SW зарегистрирован?
- HTTPS включен?
- Иконки правильных размеров?
- Офлайн fallback работает?

---

## 🎯 Итого: PWA в двух словах

### Что это технически:

**PWA = Web App + manifest.json + Service Worker + HTTPS**

### Что меняется в разработке:

1. Добавляете **2 файла** (manifest + sw.js)
2. Добавляете **10 строк кода** (регистрация SW + мета-теги)
3. Думаете про **кэширование** (какие файлы кэшировать, какая стратегия)

### Что получаете:

- ✅ Установка на главный экран
- ✅ Офлайн режим
- ✅ Быстрая загрузка (из кэша)
- ✅ Push-уведомления (опционально)
- ✅ Выглядит как нативное приложение

### Сложность для веб-разработчика:

**3/10** — это просто еще один JS-файл, который управляет кэшированием.

---

Готов внедрять в Зубрилку? С чего начнем? 🚀
