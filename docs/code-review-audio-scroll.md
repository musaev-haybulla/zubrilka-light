# Код-ревью: аудио и подсветка строк

## 1. Лишние решения / технический долг

### ❌ Дублирующийся setTimeout (строки 460-480)
```javascript
setTimeout(function() {
    console.log('🔍 100ms after play() - checking state:');
    sound.volume(1.0);  // Принудительная установка громкости
    Howler.volume(1.0);
    
    if (loop) {
        monitorLoop();
    } else {
        monitorOnce();
    }
}, 100);
```

**Проблема:** 
- Дублирует проверку состояния
- Принудительная установка громкости - старый костыль (громкость уже установлена на строке 439)
- monitorLoop можно запускать сразу, не ждать 100мс

**Рекомендация:** Убрать setTimeout, запускать monitorLoop сразу после play()

---

## 2. Костыли (работают, но требуют переосмысления)

### 🔧 Костыль #1: Задержка 150мс перед startHighlighting (строка 452)
```javascript
setTimeout(function() {
    startHighlighting();
}, 150);
```

**Причина:** HTML5 Audio не сразу переходит в `playing: true`

**Системное решение:**
- Использовать событие Howler `onplay` вместо таймера
- Или проверять `sound.playing()` в RAF warmup фазе (уже есть)

### 🔧 Костыль #2: Флаг isRewinding с setTimeout (строка 344)
```javascript
isRewinding = true;
setTimeout(function() {
    isRewinding = false;
}, 300);
```

**Причина:** `seek()` временно останавливает `playing: true`

**Системное решение:**
- Использовать событие Howler `onseek` 
- Или проверять что seek прошел успешно

### 🔧 Костыль #3: Warmup фаза 300мс в RAF (строка 701)
```javascript
var elapsedSinceStart = now - rafStartTime;
var isWarmupPhase = elapsedSinceStart < 300;
```

**Причина:** Та же - HTML5 Audio медленно стартует

**Системное решение:** Все три костыля решаются одним способом - использовать события Howler вместо таймеров

---

## 3. Потенциальные проблемы совместимости

### ⚠️ Проблема #1: Гонка таймеров
```javascript
setTimeout(startHighlighting, 150);  // Запуск RAF
setTimeout(monitorLoop, 100);         // Запуск мониторинга
setTimeout(isRewinding = false, 300); // Сброс флага
```

**Риск:** Если звук остановят до истечения таймеров - функции вызовутся с невалидным состоянием

**Решение:** Отменять все setTimeout при `stopHighlighting()`

### ⚠️ Проблема #2: visitedStanzas не очищается при stop
```javascript
function stopHighlighting() {
    // НЕТ: visitedStanzas.clear()
}
```

**Риск:** При следующем запуске может не скроллить к уже посещенным куплетам

**Решение:** Очищать в stopHighlighting ИЛИ в startHighlighting (сейчас очищается только в startHighlighting - это ОК)

### ⚠️ Проблема #3: scrollAnimator может конфликтовать с ручным скроллом
```javascript
scrollAnimator.scrollToLine(firstLine, {...});
```

**Риск:** Если пользователь скроллит руками во время воспроизведения - анимация может дергаться

**Решение:** Уже есть проверка `comfortable` - это нормально

---

## 4. Что было убрано

### ✅ Убрана принудительная установка громкости (строки 470-472)
```javascript
// УБРАНО:
sound.volume(1.0);  // Уже установлено на строке 439!
Howler.volume(1.0);
```

### ✅ monitorLoop/monitorOnce перенесены в setTimeout
**НЕ УБРАНО** - оказалось что setTimeout нужен! Если запускать monitorOnce() сразу после play(), звук еще не playing и мониторинг не начинается.

**Решение:** запускать monitorLoop/monitorOnce внутри того же setTimeout что и startHighlighting

### ❌ Выключить DEBUG логи по умолчанию
```javascript
var DEBUG_SCROLL = false;
var DEBUG_AUDIO = false;
```

---

## 5. Системное решение через события Howler

Вместо таймеров использовать события:

```javascript
sound = new Howl({
    src: [url],
    html5: true,
    onplay: function() {
        // Вызвать startHighlighting КОГДА звук реально заиграл
        startHighlighting();
    },
    onseek: function() {
        // Сбросить флаг isRewinding КОГДА seek завершен
        isRewinding = false;
    },
    onstop: function() {
        stopHighlighting();
    }
});
```

**Преимущества:**
- Нет магических чисел (150мс, 300мс)
- Нет гонок
- Код реагирует на реальные события, а не гадает по таймерам

---

## 6. Приоритет исправлений

### Сейчас (критично для стабильности):
1. ✅ Убрать принудительную установку громкости
2. ✅ Выключить DEBUG по умолчанию
3. ✅ Перенести monitorLoop/monitorOnce в setTimeout (было регрессия)

### Потом (техдолг, но не критично):
4. ⏳ Перейти на события Howler вместо setTimeout
5. ⏳ Добавить очистку таймеров в stopHighlighting
6. ⏳ Добавить обработку ошибок для sound.seek()

---

## Итог

**Текущее состояние:** Работает корректно, но есть технический долг

**Что делать:**
1. Убрать очевидный мусор (3 правки)
2. Запланировать рефакторинг на события (когда будет время)
