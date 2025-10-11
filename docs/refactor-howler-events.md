# Рефакторинг: переход на события Howler

## Что изменено

### 1. Убраны setTimeout таймеры

**ДО:**
```javascript
setTimeout(function() {
    startHighlighting();
    if (loop) {
        monitorLoop();
    } else {
        monitorOnce();
    }
}, 150);
```

**ПОСЛЕ:**
```javascript
// Событие onplay срабатывает КОГДА звук реально начал играть
onplay: function() {
    startHighlighting();
    if (currentLoopMode) {
        monitorLoop();
    } else {
        monitorOnce();
    }
}
```

---

### 2. Флаг isRewinding сбрасывается событием

**ДО:**
```javascript
isRewinding = true;
sound.seek(soundStart);
setTimeout(function() {
    isRewinding = false;
}, 300);
```

**ПОСЛЕ:**
```javascript
isRewinding = true;
sound.seek(soundStart);
// Событие onseek сбросит флаг КОГДА seek завершен

onseek: function() {
    if (isRewinding) {
        clearTimeout(rewindTimerId);
        isRewinding = false;
    }
}
```

---

### 3. Автоматическая остановка подсветки

**ДО:**
```javascript
// Надо вручную вызывать stopHighlighting() при остановке
```

**ПОСЛЕ:**
```javascript
onstop: function() {
    stopHighlighting();
}
```

---

### 4. Защита от гонки таймеров

**ДО:**
```javascript
// Если пользователь остановит звук ДО истечения setTimeout - 
// функции все равно выполнятся
```

**ПОСЛЕ:**
```javascript
function stopHighlighting() {
    // Отменяем все таймеры
    if (startTimerId) clearTimeout(startTimerId);
    if (rewindTimerId) clearTimeout(rewindTimerId);
    // ...
}
```

---

## Добавленные переменные

```javascript
var startTimerId = null;      // ID таймера для отложенного запуска (не используется)
var rewindTimerId = null;     // ID таймера для fallback сброса isRewinding
var currentLoopMode = false;  // Текущий режим зацикливания
```

---

## Преимущества

### ✅ Надежность
- Нет магических чисел (150мс, 300мс)
- Код реагирует на реальные события, а не гадает по таймерам
- Нет гонок: если пользователь быстро останавливает звук - все корректно

### ✅ Простота
- Меньше кода
- Логика более понятная
- События документированы в Howler.js

### ✅ Производительность
- Меньше setTimeout/clearTimeout
- RAF запускается точно когда нужно

---

## Совместимость

### Поддерживаемые браузеры
- Chrome/Chromium ✅
- Safari ✅  
- Firefox ✅
- Yandex Browser ✅
- Edge ✅

События `onplay`, `onseek`, `onstop` поддерживаются во всех браузерах с Howler.js 2.x

---

## Тестирование

### Что тестировать:
1. ✅ Обычное воспроизведение (без loop)
   - Запустить
   - Должна работать подсветка
   - Должен остановиться на последней строке

2. ✅ Зацикливание (с loop)
   - Запустить с галочкой Loop
   - Должна работать подсветка
   - Должен вернуться к первой строке и продолжить

3. ✅ Быстрая остановка
   - Запустить
   - СРАЗУ остановить (в первые 100мс)
   - Не должно быть ошибок в консоли

4. ✅ Изменение скорости во время воспроизведения
   - Должна работать корректно без перезапуска

---

## Откат (если что-то сломалось)

```bash
git checkout main
```

Или восстановить setTimeout:
```javascript
// В функции play() после sound.play():
setTimeout(function() {
    startHighlighting();
    if (currentLoopMode) {
        monitorLoop();
    } else {
        monitorOnce();
    }
}, 150);

// И закомментировать события onplay, onseek, onstop в new Howl({...})
```

---

## История изменений

- **2025-10-11**: Первая версия с событиями Howler
- Убраны setTimeout таймеры
- Добавлена защита от гонок
- Добавлен fallback для isRewinding (на случай если onseek не сработает)
