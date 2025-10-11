<?php 
include __DIR__ . '/header.php'; 
?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Lato:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400&display=swap" rel="stylesheet">

    <!-- Полноэкранный прелоадер -->
    <style>
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.3s ease-out;
        }
        #page-loader.hidden {
            opacity: 0;
            pointer-events: none;
        }
        .loader-spinner {
            width: 60px;
            height: 60px;
            border: 6px solid #f3f3f3;
            border-top: 6px solid #5cb85c;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        .loader-text {
            font-size: 18px;
            color: #666;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
            font-weight: 400;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.4/howler.min.js"></script>
    <script src="<?= asset('js/my.js') ?>"></script>
        
    <script>
        var highlightRafId = null;
        var lastRafTime = null;
        countCheck = <?= count($verses) ?>;
        playFlag   = 0;
        textSizeFlag  = (getCookie('textSizeFlag') !== undefined) ? getCookie('textSizeFlag') : 0;
        
        var playerOriginalTop = 0;
        var playerOriginalLeft = 0;
        
        $(document).ready(function() {
            var $player = $('#player');
            playerOriginalTop = $player.offset().top;
            playerOriginalLeft = $player.offset().left;
            
            // Загружаем аудио после того как DOM готов
            loadSoundFile('<?= media($poem_number . '/' . $poem_number . '.mp3') ?>');
        });
        
        $(window).resize(function() {
            var $player = $('#player');
            if (!$player.hasClass('fixed')) {
                playerOriginalTop = $player.offset().top;
                playerOriginalLeft = $player.offset().left;
            }
        });
        
        $(window).scroll(function(){
            // Sticky только для десктопа (ширина > 768px)
            if ($(window).width() <= 768) {
                return;
            }
            
            var window_top = $(window).scrollTop();
            var $player = $('#player');

            if (window_top > playerOriginalTop - 20) {
                if (!$player.hasClass('fixed')) {
                    $player.addClass('fixed');
                    $player.css('left', playerOriginalLeft + 'px');
                }
            } else {
                if ($player.hasClass('fixed')) {
                    $player.removeClass('fixed');
                    $player.css('left', '');
                }
            }
        });
         
        function getCookie(name) {
            var matches = document.cookie.match(new RegExp(
                "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
            ));
            return matches ? decodeURIComponent(matches[1]) : undefined;
        }

        function setCookie(name, value, options) {
            options = options || {};
            var expires = options.expires;

            if (typeof expires == "number" && expires) {
                var d = new Date();
                d.setTime(d.getTime() + expires * 1000);
                expires = options.expires = d;
            }
            if (expires && expires.toUTCString) {
                options.expires = expires.toUTCString();
            }

            value = encodeURIComponent(value);
            var updatedCookie = name + "=" + value;

            for (var propName in options) {
                updatedCookie += "; " + propName;
                var propValue = options[propName];
                if (propValue !== true) {
                  updatedCookie += "=" + propValue;
                }
            }

            document.cookie = updatedCookie;
        }

        function getSoundArr(arr) {
            var ret = [];
            for(i = 0; i < arr.length; i++) {
                ret[i] = arr[i]['audio_timestamp'];
            }
            return ret;
        }

        function getSoundStart() {
            for(i = 0; i < countCheck; i++) {
                if(document.getElementById('partition_'+i).checked) {
                    if(i == 0){
                        return 0;
                    }
                    return soundArr[i-1];
                }
            }
        }

        function getSoundEnd() {
            for(i = countCheck-1; i >= 0; i--) {
                if(document.getElementById('partition_'+i).checked) {
                    return soundArr[i];
                }
            }
        }

        function setCheck() {
            var flag = 0;
            var first   = null;
            var last    = null;
            
            // Сбрасываем позицию куплета при изменении выбора
            currentStanzaNumber = null;
            
            for(i = 0; i < countCheck; i++){
                var current = document.getElementById('partition_'+i);
                if(current.checked) {
                    flag = 1;
                    last = current;
                    if(first == null) {
                        first = current;
                    }
                }
            }
            
            // Обновляем состояние toggle-кнопки
            var btn = document.getElementById('toggleSelectBtn');
            if (btn) {
                if (flag === 0) {
                    btn.querySelector('.toggle-text').textContent = 'Выбрать всё';
                    btn.classList.remove('active');
                } else {
                    btn.querySelector('.toggle-text').textContent = 'Снять выделение';
                    btn.classList.add('active');
                }
            }
            
            // Обновляем плавающую кнопку
            updateFloatingClearButton();
            
            // Находим индексы первой и последней выбранной строки
            var firstIndex = -1;
            var lastIndex = -1;
            for (i = 0; i < countCheck; i++) {
                var current = document.getElementById('partition_'+i);
                if (current.checked) {
                    if (firstIndex === -1) firstIndex = i;
                    lastIndex = i;
                }
            }
            
            // Обновляем стили и доступность строк
            for (i = 0; i < countCheck; i++) {
                var current = document.getElementById('partition_'+i);
                var currentLabel = document.getElementById('label_partition_'+i);
                
                // Если ничего не выбрано - все разблокированы
                if (flag === 0) {
                    current.disabled = false;
                } 
                // Если выбрана только одна строка - разблокированы она и соседние
                else if (firstIndex === lastIndex) {
                    if (i === firstIndex || i === firstIndex - 1 || i === firstIndex + 1) {
                        current.disabled = false;
                    } else {
                        current.disabled = true;
                    }
                }
                // Если выбран диапазон
                else {
                    // Разрешаем изменение только для:
                    // 1. Крайних выбранных строк (firstIndex, lastIndex) - можно снять
                    // 2. Строк рядом с диапазоном (firstIndex-1, lastIndex+1) - можно добавить
                    if (i === firstIndex || i === lastIndex || i === firstIndex - 1 || i === lastIndex + 1) {
                        current.disabled = false;
                    } else if (i > firstIndex && i < lastIndex) {
                        // Строки внутри диапазона - заблокированы, но выбраны
                        current.disabled = true;
                    } else {
                        // Строки вне диапазона - заблокированы
                        current.disabled = true;
                    }
                }

                if (current.checked) {
                    if (currentLabel.className.indexOf('big') == "-1") {
                        currentLabel.className = 'label_partition_check';
                    } else {
                        currentLabel.className = 'label_partition_check_big';
                    }
                } else {
                    if (currentLabel.className.indexOf('big') == "-1") {
                        currentLabel.className = 'label_partition_uncheck';
                    } else {
                        currentLabel.className = 'label_partition_uncheck_big';
                    }
                }                         
            }
        }

        var arg = <?= json_encode($verses, JSON_UNESCAPED_UNICODE) ?>;
        var soundArr = getSoundArr(arg);

        // Howler.js переменные
        var sound;
        var soundStart = 0;
        var soundEnd = 0;
        var loopMonitorId = null;
        var howlInstanceCount = 0; // Счётчик созданных экземпляров
        
        // Загрузка аудиофайла
        var loadSoundFile = function(url) {
            // Уничтожаем старый экземпляр если есть
            if (sound) {
                sound.unload();
                sound = null;
            }
            
            howlInstanceCount++;
            
            // Определяем браузер
            var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
            var isYandex = /YaBrowser/i.test(navigator.userAgent);
            console.log('🎵 Howl #' + howlInstanceCount + ' | Pool:', Howler._howls.length, '| Safari:', isSafari, '| Yandex:', isYandex);
            
            // Показываем полноэкранный прелоадер
            var loader = document.getElementById('page-loader');
            if (loader) {
                loader.classList.remove('hidden');
                loader.querySelector('.loader-text').textContent = 'Загрузка аудио...';
            }
            
            sound = new Howl({
                src: [url],
                html5: true, // HTML5 Audio сохраняет pitch при изменении скорости
                preload: true,
                format: ['mp3'],
                pool: 1,
                onload: function() {
                    var playBtn = document.getElementById('playBtn');
                    playBtn.className = 'btn btn-lg btn-success';
                    playBtn.innerHTML = 'Запустить';
                    playBtn.disabled = false;
                    if (DEBUG_AUDIO) console.log('✅ Аудио загружено и закешировано');
                    
                    // Скрываем прелоадер
                    var loader = document.getElementById('page-loader');
                    if (loader) {
                        loader.classList.add('hidden');
                    }
                },
                onloaderror: function(id, error) {
                    console.error('Ошибка загрузки:', id, error);
                    alert('Ошибка загрузки аудиофайла: ' + error);
                },
                onplayerror: function(id, error) {
                    console.error('Ошибка воспроизведения:', id, error);
                    alert('Ошибка воспроизведения: ' + error);
                },
                onplay: function() {
                    // Событие срабатывает КОГДА звук реально начал играть
                    if (DEBUG_AUDIO) console.log('🎵 onplay event - sound started playing');
                    
                    // Запускаем подсветку и мониторинг
                    startHighlighting();
                    
                    if (currentLoopMode) {
                        monitorLoop();
                    } else {
                        monitorOnce();
                    }
                },
                onseek: function() {
                    // Событие срабатывает КОГДА seek завершен
                    if (isRewinding) {
                        // Отменяем таймер если он был
                        if (rewindTimerId) {
                            clearTimeout(rewindTimerId);
                            rewindTimerId = null;
                        }
                        isRewinding = false;
                        if (DEBUG_AUDIO) console.log('✅ onseek: rewind complete');
                    }
                },
                onstop: function() {
                    // Событие срабатывает при остановке
                    if (DEBUG_AUDIO) console.log('🛑 onstop event');
                    stopHighlighting();
                }
            });
        }
        
        window.onkeyup = function(e){
            var k = e.keyCode;
            if(k == 13){
                play();
            }
        }
        
        // Мониторинг позиции для зацикливания
        function monitorLoop() {
            if (playFlag && sound && sound.playing()) {
                var currentTime = sound.seek();
                
                if (typeof currentTime === 'number' && currentTime >= soundEnd) {
                    if (DEBUG_AUDIO) console.log('🔄🔄🔄 LOOP DETECTED - REWINDING!', {
                        currentTime: currentTime,
                        soundEnd: soundEnd,
                        soundStart: soundStart,
                        visitedStanzasBefore: Array.from(visitedStanzas),
                        currentStanzaNumber: currentStanzaNumber
                    });
                    
                    // Устанавливаем флаг чтобы RAF не остановился
                    isRewinding = true;
                    
                    sound.seek(soundStart);
                    
                    // Флаг будет сброшен в событии onseek
                    // Но на всякий случай ставим запасной таймер
                    rewindTimerId = setTimeout(function() {
                        isRewinding = false;
                        rewindTimerId = null;
                        if (DEBUG_AUDIO) console.log('✅ Rewind timeout fallback');
                    }, 500);
                    
                    // КРИТИЧНО: сбрасываем посещенные куплеты для корректного скролла
                    visitedStanzas.clear();
                    currentStanzaNumber = null;
                    lastTargetLineId = null;
                    
                    if (DEBUG_SCROLL) console.log('🔄 State after reset:', {
                        visitedStanzas: Array.from(visitedStanzas),
                        currentStanzaNumber: currentStanzaNumber,
                        lastTargetLineId: lastTargetLineId
                    });
                    
                    // Принудительно скроллим к первой строке
                    var firstLine = document.querySelector('.verse-line');
                    if (firstLine) {
                        if (DEBUG_SCROLL) console.log('📍 Forcing scroll to first line:', {
                            lineId: firstLine.id,
                            dataStart: firstLine.dataset.start,
                            dataEnd: firstLine.dataset.end
                        });
                        scrollAnimator.scrollToLine(firstLine, {
                            reason: 'loopRewind',
                            lineId: firstLine.id,
                            isRewind: true,
                            durationOverride: REWIND_DURATION
                        });
                    } else {
                        console.error('❌ First line not found!');
                    }
                }
                
                loopMonitorId = requestAnimationFrame(monitorLoop);
            }
        }
        
        // Мониторинг позиции для одноразового воспроизведения
        function monitorOnce() {
            if (playFlag && sound && sound.playing()) {
                var currentTime = sound.seek();
                
                // Если достигли конца - останавливаем
                if (typeof currentTime === 'number' && currentTime >= soundEnd) {
                    sound.stop();
                    playFlag = 0;
                    stopHighlighting();
                    
                    var playBtn = document.getElementById('playBtn');
                    playBtn.className = 'btn btn-lg btn-success';
                    playBtn.innerHTML = 'Запустить';
                    setCheck();
                    
                    if (loopMonitorId) {
                        cancelAnimationFrame(loopMonitorId);
                        loopMonitorId = null;
                    }
                    return;
                }
                
                loopMonitorId = requestAnimationFrame(monitorOnce);
            }
        }
        
        var play = function(){
            
            if (getSoundStart() === undefined) {
                alert('Вы должны выбрать фрагмент или несколько фрагментов, отметив их галочками');
                return;
            }
            
            playFlag = (+!playFlag);
           
            if (playFlag) {
                // Разблокируем AudioContext если нужно
                if (Howler.ctx && Howler.ctx.state === 'suspended') {
                    Howler.ctx.resume();
                }
                
                var playBtn = document.getElementById('playBtn');
                playBtn.className = 'btn btn-lg btn-warning';
                playBtn.innerHTML = 'Остановить';
                
                soundStart = getSoundStart();
                soundEnd = getSoundEnd();
                
                currentLoopMode = document.getElementById('loop').checked;
                var speed = currentSpeed;
                var volume = parseFloat(document.getElementById('points').value);
                
                // Настраиваем Howler (НЕ используем встроенный loop!)
                sound.loop(false); // Всегда false, зацикливание делаем вручную
                sound.rate(speed);
                sound.volume(volume);
                
                sound.seek(soundStart);
                
                allCheckDisabled();
                
                // Добавляем класс для скрытия фона выделенных строк
                document.body.classList.add('audio-playing');
                
                // Блокируем кнопку "Выбрать всё"
                var toggleBtn = document.getElementById('toggleSelectBtn');
                if (toggleBtn) {
                    toggleBtn.disabled = true;
                    toggleBtn.style.opacity = '0.5';
                    toggleBtn.style.cursor = 'not-allowed';
                }
                
                // Скрываем плавающую кнопку
                updateFloatingClearButton();
                
                // Запускаем воспроизведение
                // Событие onplay запустит startHighlighting() и мониторинг
                if (DEBUG_AUDIO) console.log('🎵 Calling sound.play()...');
                var soundId = sound.play();
                if (DEBUG_AUDIO) console.log('🎵 sound.play() returned:', soundId);
                
            } else {
                if (sound) {
                    sound.stop();
                }
                if (loopMonitorId) {
                    cancelAnimationFrame(loopMonitorId);
                    loopMonitorId = null;
                }
                // Останавливаем подсветку
                stopHighlighting();
                
                var playBtn = document.getElementById('playBtn');
                playBtn.className = 'btn btn-lg btn-success';
                playBtn.innerHTML = 'Запустить';
                setCheck(); // Разблокируем чекбоксы
            }
        }

        function allCheckDisabled() {
            for (i = 0; i < countCheck; i++) {
                var current = document.getElementById('partition_'+i);
                current.disabled = true;              
            }
        }
        
        // Выделить куплет по индексу строки
        function selectStanzaByLineIndex(lineIndex) {
            if (lineIndex < 0 || lineIndex >= countCheck) return;
            
            // Находим начало куплета (идём вверх до paragraph-end предыдущего куплета)
            var stanzaStart = 0;
            for (var i = lineIndex - 1; i >= 0; i--) {
                var verseDiv = document.getElementById('partition_'+i).closest('.verse-line');
                if (verseDiv && verseDiv.classList.contains('paragraph-end')) {
                    stanzaStart = i + 1;
                    break;
                }
            }
            
            // Находим конец куплета (идём вниз до paragraph-end)
            var stanzaEnd = countCheck - 1;
            for (var i = lineIndex; i < countCheck; i++) {
                var verseDiv = document.getElementById('partition_'+i).closest('.verse-line');
                if (verseDiv && verseDiv.classList.contains('paragraph-end')) {
                    stanzaEnd = i;
                    break;
                }
            }
            
            // Проверяем, весь ли куплет уже выделен
            var allChecked = true;
            for (var i = stanzaStart; i <= stanzaEnd; i++) {
                if (!document.getElementById('partition_'+i).checked) {
                    allChecked = false;
                    break;
                }
            }
            
            if (allChecked) {
                // Если весь куплет выделен - снимаем выделение с этого куплета
                for (var i = stanzaStart; i <= stanzaEnd; i++) {
                    document.getElementById('partition_'+i).checked = false;
                }
            } else {
                // Если куплет не полностью выделен - выделяем его (добавляем к текущему выделению)
                for (var i = stanzaStart; i <= stanzaEnd; i++) {
                    document.getElementById('partition_'+i).checked = true;
                }
            }
            
            setCheck();
            updateFloatingClearButton();
        }
        
        // Быстрый выбор: выбрать куплет (для обратной совместимости)
        function selectStanza() {
            // Находим первую выбранную строку
            var selectedIndex = -1;
            for (i = 0; i < countCheck; i++) {
                if (document.getElementById('partition_'+i).checked) {
                    selectedIndex = i;
                    break;
                }
            }
            if (selectedIndex === -1) return; // Ничего не выбрано
            
            selectStanzaByLineIndex(selectedIndex);
        }
        
        // Toggle: выбрать всё / снять выделение
        function toggleSelection() {
            var btn = document.getElementById('toggleSelectBtn');
            
            // Проверяем текущее состояние кнопки
            if (btn.classList.contains('active')) {
                // Снять все
                for (i = 0; i < countCheck; i++) {
                    document.getElementById('partition_'+i).checked = false;
                }
            } else {
                // Выбрать все
                for (i = 0; i < countCheck; i++) {
                    document.getElementById('partition_'+i).checked = true;
                }
            }
            
            setCheck();
            updateFloatingClearButton();
        }
        
        // Снять все выделения (для плавающей кнопки)
        function clearAllSelections() {
            for (var i = 0; i < countCheck; i++) {
                document.getElementById('partition_'+i).checked = false;
            }
            setCheck();
            updateFloatingClearButton();
        }
        
        // Обновление видимости плавающей кнопки
        function updateFloatingClearButton() {
            var hasSelection = false;
            for (var i = 0; i < countCheck; i++) {
                if (document.getElementById('partition_'+i).checked) {
                    hasSelection = true;
                    break;
                }
            }
            
            var floatingBtn = document.getElementById('floatingClearBtn');
            if (floatingBtn) {
                // Показываем кнопку только если есть выделение, НЕТ воспроизведения и НЕ режим самопроверки
                if (hasSelection && !playFlag && !document.body.classList.contains('self-check-mode')) {
                    floatingBtn.style.display = 'flex';
                } else {
                    floatingBtn.style.display = 'none';
                }
            }
        }
        
        // Очищаем старый выбор шрифта из localStorage
        window.addEventListener('DOMContentLoaded', function() {
            localStorage.removeItem('selectedFont');
        });
        
        // Подсветка текущей строки при воспроизведении
        var DEBUG_SCROLL = false; // Включить для отладки скролла
        var DEBUG_AUDIO = false;  // Включить для диагностики аудио (seek, rate, duration)
        var currentStanzaNumber = null;
        var isHighlightingActive = false;
        var visitedStanzas = new Set();
        var scrollTraceId = 0;
        var lastTargetLineId = null;
        var lastPlaybackTime = null;
        var highlightTickId = 0;
        var lastRawSeek = null;
        var lastDeltaSeek = null;
        var highlightRafId = null;
        var lastRafTime = null;
        var isRewinding = false; // Флаг что идет перемотка при зацикливании
        var startTimerId = null; // ID таймера для отложенного запуска
        var rewindTimerId = null; // ID таймера для сброса флага isRewinding
        var currentLoopMode = false; // Текущий режим зацикливания
        
        // Константы для обработки скролла и подсветки
        var MIN_DELTA_SEEK = 0.005;        // Минимальное изменение позиции для обновления
        var REWIND_THRESHOLD = -0.05;      // Порог для определения отката назад
        var REWIND_DURATION = 250;         // Длительность анимации при rewind (мс)
        var NORMAL_SCROLL_DURATION = 600;  // Обычная длительность скролла (мс)
        
        /**
         * Трассировка событий скролла (только для отладки)
         */
        function traceScroll(event, payload) {
            if (!DEBUG_SCROLL) return;
            scrollTraceId += 1;
            console.log('🧭 [' + scrollTraceId + '] ' + event, payload || '');
        }

        /**
         * Трассировка событий подсветки (только для отладки)
         */
        function traceHighlight(event, payload) {
            if (!DEBUG_SCROLL) return;
            highlightTickId += 1;
            console.log('⏺️ [' + highlightTickId + '] ' + event, payload || '');
        }
        
        /**
         * Объект для управления плавным скроллом с отменой
         */
        var scrollAnimator = {
            activeId: 0,
            rafId: null,
            startY: 0,
            targetY: 0,
            duration: NORMAL_SCROLL_DURATION,
            cancel: function(reason) {
                if (this.rafId !== null) {
                    cancelAnimationFrame(this.rafId);
                    this.rafId = null;
                    traceScroll('scrollCancel', { reason: reason || 'manual', targetY: this.targetY });
                }
            },
            scrollToLine: function(line, meta) {
                var rect = line.getBoundingClientRect();
                var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
                var targetY = rect.top + (window.pageYOffset || document.documentElement.scrollTop) - (viewportHeight / 2) + (rect.height / 2);
                targetY = Math.max(0, Math.round(targetY));
                var startY = window.pageYOffset || document.documentElement.scrollTop;
                if (Math.abs(targetY - startY) < 4) {
                    traceScroll('scrollSkip', { reason: 'delta<4', targetY: targetY, startY: startY, meta: meta });
                    window.scrollTo(0, targetY);
                    return;
                }
                this.cancel('new-scroll');
                this.activeId += 1;
                var animationId = this.activeId;
                this.startY = startY;
                this.targetY = targetY;
                var duration = meta && meta.durationOverride ? meta.durationOverride : this.duration;
                var ease = function(t) {
                    return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
                };
                var self = this;
                var startTime = performance.now();
                traceScroll('scrollStart', {
                    id: animationId,
                    startY: startY,
                    targetY: targetY,
                    duration: duration,
                    meta: meta
                });
                var step = function(now) {
                    if (self.activeId !== animationId) {
                        return;
                    }
                    var elapsed = now - startTime;
                    var t = Math.min(1, elapsed / duration);
                    var eased = ease(t);
                    var current = Math.round(self.startY + (self.targetY - self.startY) * eased);
                    window.scrollTo(0, current);
                    if (t < 1) {
                        self.rafId = requestAnimationFrame(step);
                    } else {
                        self.rafId = null;
                        traceScroll('scrollComplete', {
                            id: animationId,
                            finalPosition: current,
                            meta: meta
                        });
                    }
                };
                this.rafId = requestAnimationFrame(step);
            }
        };
        
        function startHighlighting() {
            if (DEBUG_AUDIO) console.log('🚀 startHighlighting() CALLED');
            if (highlightRafId) {
                if (DEBUG_AUDIO) console.log('⚠️ RAF already running, id:', highlightRafId);
                return;
            }
            isHighlightingActive = true;
            visitedStanzas = new Set();
            lastPlaybackTime = null;
            highlightTickId = 0;
            lastRawSeek = null;
            lastDeltaSeek = null;
            lastRafTime = null;
            var rafStartTime = performance.now(); // Запоминаем время старта RAF
            if (DEBUG_AUDIO) console.log('✅ startHighlighting initialized, calling RAF...');
            traceScroll('startHighlighting', {
                currentStanzaNumber: currentStanzaNumber,
                visited: Array.from(visitedStanzas)
            });
            
            var rafLoop = function(now) {
                // Диагностика только при первом кадре
                if (DEBUG_AUDIO && highlightTickId === 0) {
                    console.log('🎬 RAF LOOP STARTED!', {
                        now: now,
                        isHighlightingActive: isHighlightingActive,
                        hasSound: !!sound,
                        soundPlaying: sound ? sound.playing() : 'NO_SOUND'
                    });
                }
                
                // Для HTML5 Audio: в первые 300мс даем время звуку начать играть
                var elapsedSinceStart = now - rafStartTime;
                var isWarmupPhase = elapsedSinceStart < 300;
                
                if (!isHighlightingActive || !sound) {
                    highlightRafId = null;
                    if (DEBUG_AUDIO) console.log('🛑 RAF STOPPED (no sound/inactive):', {
                        isHighlightingActive: isHighlightingActive,
                        hasSound: !!sound
                    });
                    traceHighlight('rafStop', {
                        now: now,
                        playing: sound ? sound.playing() : false,
                        isHighlightingActive: isHighlightingActive
                    });
                    return;
                }
                
                // Проверка playing() с учетом warmup фазы
                if (!sound.playing()) {
                    // Если идет rewind - не останавливаем RAF
                    if (isRewinding) {
                        if (DEBUG_AUDIO) console.log('🔄 Rewind in progress, waiting...', {
                            elapsed: Math.round(elapsedSinceStart),
                            isRewinding: isRewinding
                        });
                        highlightRafId = requestAnimationFrame(rafLoop);
                        return;
                    }
                    
                    if (isWarmupPhase) {
                        // HTML5 Audio еще загружается - продолжаем RAF
                        if (DEBUG_AUDIO) console.log('⏳ Waiting for HTML5 Audio to start...', {
                            elapsed: Math.round(elapsedSinceStart),
                            state: sound.state()
                        });
                        highlightRafId = requestAnimationFrame(rafLoop);
                        return;
                    } else {
                        // Прошло 300мс и звук не играет - останавливаем RAF
                        highlightRafId = null;
                        if (DEBUG_AUDIO) console.log('🛑 RAF STOPPED (not playing after warmup):', {
                            elapsed: Math.round(elapsedSinceStart),
                            soundPlaying: sound.playing()
                        });
                        traceHighlight('rafStop', {
                            now: now,
                            playing: false,
                            isHighlightingActive: isHighlightingActive
                        });
                        return;
                    }
                }
                
                var seekValue = sound.seek();
                
                // HTML5 Audio может вернуть некорректное значение - пропускаем такие фреймы
                if (typeof seekValue !== 'number' || isNaN(seekValue)) {
                    if (DEBUG_AUDIO) {
                        console.warn('⚠️ Invalid seek() in RAF:', seekValue, 'type:', typeof seekValue);
                    }
                    highlightRafId = requestAnimationFrame(rafLoop);
                    return;
                }
                
                // Диагностика для HTML5 Audio в разных браузерах
                if (DEBUG_AUDIO && highlightTickId % 60 === 0) {
                    console.log('🎧 Audio Debug:', {
                        tick: highlightTickId,
                        seek: seekValue,
                        type: typeof seekValue,
                        playing: sound.playing(),
                        rate: sound.rate(),
                        duration: sound.duration()
                    });
                }
                
                var deltaSeek = lastRawSeek === null ? null : seekValue - lastRawSeek;
                var deltaTime = lastRafTime === null ? null : (now - lastRafTime) / 1000;
                var isRewindFrame = deltaSeek !== null && deltaSeek < REWIND_THRESHOLD;
                if (isRewindFrame) {
                    visitedStanzas = new Set();
                }
                if (deltaSeek !== null && Math.abs(deltaSeek) < MIN_DELTA_SEEK) {
                    traceHighlight('rafSkipSmallDelta', {
                        now: now,
                        seek: seekValue,
                        deltaSeek: deltaSeek,
                        deltaTime: deltaTime,
                        playing: sound.playing(),
                        reason: 'abs(deltaSeek)<MIN_DELTA_SEEK'
                    });
                    lastRawSeek = seekValue;
                    lastRafTime = now;
                    lastDeltaSeek = deltaSeek;
                    lastPlaybackTime = seekValue;
                    highlightRafId = requestAnimationFrame(rafLoop);
                    return;
                }
                traceHighlight('rafTick', {
                    now: now,
                    seek: seekValue,
                    deltaSeek: deltaSeek,
                    deltaTime: deltaTime,
                    playing: sound.playing(),
                    isHighlightingActive: isHighlightingActive,
                    currentStanzaNumber: currentStanzaNumber,
                    lastTargetLineId: lastTargetLineId,
                    rafId: highlightTickId + 1
                });
                lastRawSeek = seekValue;
                lastRafTime = now;
                highlightCurrentLine(seekValue, deltaSeek);
                lastDeltaSeek = deltaSeek;
                lastPlaybackTime = seekValue;
                highlightTickId++; // КРИТИЧНО: инкрементируем счетчик кадров!
                highlightRafId = requestAnimationFrame(rafLoop);
            };
            highlightRafId = requestAnimationFrame(rafLoop);
        }
        
        function stopHighlighting() {
            isHighlightingActive = false;
            traceScroll('stopHighlighting', {
                currentStanzaNumber: currentStanzaNumber,
                lastTargetLineId: lastTargetLineId,
                visited: Array.from(visitedStanzas)
            });
            
            // Убираем класс - возвращаем фон выделенным строкам
            document.body.classList.remove('audio-playing');
            
            // Разблокируем кнопку "Выбрать всё"
            var toggleBtn = document.getElementById('toggleSelectBtn');
            if (toggleBtn) {
                toggleBtn.disabled = false;
                toggleBtn.style.opacity = '1';
                toggleBtn.style.cursor = 'pointer';
            }
            
            // Показываем плавающую кнопку если есть выделение
            updateFloatingClearButton();
            
            // Убираем жирность со всех строк
            var lines = document.querySelectorAll('.verse-line');
            lines.forEach(function(line) {
                line.classList.remove('current');
            });
            
            // Отменяем все таймеры
            if (startTimerId) {
                clearTimeout(startTimerId);
                startTimerId = null;
            }
            if (rewindTimerId) {
                clearTimeout(rewindTimerId);
                rewindTimerId = null;
            }
            
            if (highlightRafId !== null) {
                cancelAnimationFrame(highlightRafId);
                highlightRafId = null;
            }
            scrollAnimator.cancel('stopHighlighting');
            lastPlaybackTime = null;
        }
        
        function getStanzaNumber(line) {
            // Ищем номер куплета: идём назад до ближайшего .stanza-divider
            var prev = line.previousElementSibling;
            while (prev) {
                if (prev.classList.contains('stanza-divider')) {
                    var numberSpan = prev.querySelector('.stanza-number');
                    return numberSpan ? parseInt(numberSpan.textContent) : null;
                }
                prev = prev.previousElementSibling;
            }
            return 1; // Первый куплет по умолчанию
        }
        
        function isLineComfortablyVisible(line) {
            var rect = line.getBoundingClientRect();
            var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
            var padding = Math.min(120, viewportHeight * 0.25);
            return rect.top >= padding && rect.bottom <= (viewportHeight - padding);
        }
        
        function highlightCurrentLine(currentTime, deltaSeek) {
            if (!isHighlightingActive) return;
            
            // Валидация seek() для HTML5 Audio - может вернуть объект вместо числа
            if (typeof currentTime !== 'number' || isNaN(currentTime)) {
                if (DEBUG_AUDIO) {
                    console.warn('⚠️ Invalid seek value:', currentTime, 'type:', typeof currentTime);
                }
                return;
            }
            
            var lines = document.querySelectorAll('.verse-line');
            var targetLine = null;
            var isRewind = false;
            if (typeof deltaSeek === 'number' && deltaSeek < REWIND_THRESHOLD) {
                isRewind = true;
            } else if (lastPlaybackTime !== null && currentTime + 0.05 < lastPlaybackTime) {
                isRewind = true;
            }
            
            // Для HTML5 Audio добавляем небольшой буфер для компенсации погрешностей seek()
            var SEEK_BUFFER = 0.05; // 50ms буфер для HTML5 Audio
            
            lines.forEach(function(line) {
                if (targetLine) return;
                var start = parseFloat(line.dataset.start);
                var end = parseFloat(line.dataset.end);
                
                // Проверяем попадание с небольшим буфером для компенсации погрешностей
                if (currentTime >= (start - SEEK_BUFFER) && currentTime < (end + SEEK_BUFFER)) {
                    targetLine = line;
                }
            });
            
            if (!targetLine) {
                if (isRewind) {
                    traceScroll('playbackRewind:noTarget', {
                        currentTime: currentTime,
                        lastPlaybackTime: lastPlaybackTime
                    });
                    visitedStanzas = new Set();
                } else {
                    traceScroll('noTargetLine', {
                        currentTime: currentTime,
                        lastTargetLineId: lastTargetLineId
                    });
                }
                traceHighlight('noTarget', {
                    currentTime: currentTime,
                    isRewind: isRewind,
                    lastPlaybackTime: lastPlaybackTime,
                    lastDeltaSeek: lastDeltaSeek
                });
                
                // Дополнительная диагностика для HTML5 Audio
                if (DEBUG_AUDIO && highlightTickId % 120 === 0) {
                    console.log('🔍 No target line found:', {
                        currentTime: currentTime,
                        firstLineStart: lines[0] ? parseFloat(lines[0].dataset.start) : 'N/A',
                        lastLineEnd: lines[lines.length-1] ? parseFloat(lines[lines.length-1].dataset.end) : 'N/A',
                        soundDuration: sound ? sound.duration() : 'N/A'
                    });
                }
                
                lastPlaybackTime = currentTime;
                return; // сохраняем подсветку последней строки, чтобы избежать рывка при окончании
            }

            if (isRewind) {
                traceScroll('playbackRewind', {
                    currentTime: currentTime,
                    lastPlaybackTime: lastPlaybackTime,
                    targetLine: targetLine.id
                });
                visitedStanzas = new Set();
            }

            var targetLineId = targetLine.id;
            if (targetLineId !== lastTargetLineId) {
                traceScroll('targetLineDetected', {
                    currentTime: currentTime,
                    lineId: targetLineId,
                    start: targetLine.dataset.start,
                    end: targetLine.dataset.end
                });
                traceHighlight('targetLineChange', {
                    lineId: targetLineId,
                    currentTime: currentTime,
                    deltaSeek: lastDeltaSeek,
                    isRewind: isRewind
                });
                lastTargetLineId = targetLineId;
                if (isRewind) {
                    scrollAnimator.cancel('lineChangeDuringRewind');
                    // Rewind logic: cancel any ongoing scroll animation and reset the visited stanzas
                    visitedStanzas = new Set();
                }
            }
            
            lines.forEach(function(line) {
                if (line === targetLine) {
                    if (!line.classList.contains('current')) {
                        line.classList.add('current');
                    }
                    var stanzaNum = getStanzaNumber(line);
                    if (currentStanzaNumber !== stanzaNum || isRewind) {
                        var previousStanza = currentStanzaNumber;
                        currentStanzaNumber = stanzaNum;
                        var firstVisit = !visitedStanzas.has(stanzaNum);
                        if (isRewind) {
                            visitedStanzas = new Set();
                            firstVisit = true;
                        }
                        visitedStanzas.add(stanzaNum);
                        var comfortable = isLineComfortablyVisible(line);
                        traceScroll('stanzaChange', {
                            stanza: stanzaNum,
                            previous: previousStanza,
                            firstVisit: firstVisit,
                            comfortable: comfortable,
                            lineRect: line.getBoundingClientRect(),
                            scrollPosition: window.pageYOffset || document.documentElement.scrollTop,
                            rewind: isRewind
                        });
                        traceHighlight('stanzaDecision', {
                            stanza: stanzaNum,
                            previous: previousStanza,
                            firstVisit: firstVisit,
                            comfortable: comfortable,
                            shouldScroll: isRewind || firstVisit || !comfortable,
                            reason: isRewind ? 'rewind' : (firstVisit ? 'firstVisit' : (comfortable ? 'none' : 'notComfortable'))
                        });
                        var shouldScroll = isRewind || firstVisit || !comfortable;
                        if (shouldScroll) {
                            var before = window.pageYOffset || document.documentElement.scrollTop;
                            var reason = isRewind ? 'rewind' : (firstVisit ? 'firstVisit' : 'notComfortable');
                            traceScroll('scrollRequest', {
                                stanza: stanzaNum,
                                before: before,
                                reason: reason
                            });
                            traceHighlight('scrollDispatch', {
                                stanza: stanzaNum,
                                reason: reason,
                                before: before,
                                animatorActiveId: scrollAnimator.activeId
                            });
                            scrollAnimator.scrollToLine(line, {
                                stanza: stanzaNum,
                                reason: reason,
                                lineId: line.id,
                                isRewind: isRewind,
                                durationOverride: isRewind ? REWIND_DURATION : undefined
                            });
                        }
                    }
                } else {
                    line.classList.remove('current');
                }
            });
            lastPlaybackTime = currentTime;
        }
        
        function gainChange() {
            var volume = parseFloat(document.getElementById('points').value);
            if (sound) {
                sound.volume(volume);
            }
            return volume;
        }

        var currentSpeed = 1;
        
        function setSpeed(speed) {
            currentSpeed = speed;
            if (sound) {
                sound.rate(speed);
            }
            
            // Обновляем активную кнопку
            var buttons = document.querySelectorAll('.speed-btn');
            buttons.forEach(function(btn) {
                if (parseFloat(btn.getAttribute('data-speed')) === speed) {
                    btn.classList.add('active');
                    btn.style.fontWeight = 'bold';
                    btn.style.backgroundColor = '#5cb85c';
                    btn.style.color = 'white';
                } else {
                    btn.classList.remove('active');
                    btn.style.fontWeight = 'normal';
                    btn.style.backgroundColor = '';
                    btn.style.color = '';
                }
            });
        }
        
        // Синхронизация переключателя зацикливания
        function loopSync(checkbox) {
            document.getElementById('loop').checked = checkbox.checked;
            document.getElementById('loop2').checked = checkbox.checked;
        }
        
        // Синхронизация переключателя режима самопроверки
        function selfCheckSync(checkbox) {
            document.getElementById('selfCheck').checked = checkbox.checked;
            document.getElementById('selfCheck2').checked = checkbox.checked;
            toggleSelfCheckMode(checkbox.checked);
        }
        
        // Включение/выключение режима самопроверки
        function toggleSelfCheckMode(enabled) {
            var verseLines = document.querySelectorAll('.verse-line label span');
            
            if (enabled) {
                // Добавляем класс на body для управления стилями
                document.body.classList.add('self-check-mode');
                
                // Останавливаем воспроизведение если оно идет
                if (playFlag && sound) {
                    sound.stop();
                    playFlag = 0;
                    stopHighlighting();
                    var playBtn = document.getElementById('playBtn');
                    if (playBtn) {
                        playBtn.className = 'btn btn-lg btn-success';
                        playBtn.innerHTML = 'Запустить';
                    }
                    setCheck();
                }
                
                // Отключаем все чекбоксы
                for (var i = 0; i < countCheck; i++) {
                    var checkbox = document.getElementById('partition_'+i);
                    if (checkbox) {
                        checkbox.checked = false;
                        checkbox.disabled = true;
                    }
                }
            } else {
                // Убираем класс с body
                document.body.classList.remove('self-check-mode');
                
                // Включаем все чекбоксы обратно
                for (var i = 0; i < countCheck; i++) {
                    var checkbox = document.getElementById('partition_'+i);
                    if (checkbox) {
                        checkbox.disabled = false;
                    }
                }
            }
            
            verseLines.forEach(function(span) {
                // Всегда используем сохраненный оригинальный текст
                var fullText = span.dataset.originalText || span.textContent;
                
                if (enabled) {
                    // Разбиваем на первое слово и остальное
                    var words = fullText.trim().split(/\s+/);
                    if (words.length > 1) {
                        var firstWord = words[0];
                        var restWords = words.slice(1).join(' ');
                        
                        // Создаём HTML с видимым первым словом и заблюренным остальным
                        span.innerHTML = firstWord + ' <span class="blurred-text" onclick="revealText(this)">' + restWords + '</span>';
                    }
                } else {
                    // Восстанавливаем оригинальный текст
                    span.textContent = fullText;
                }
            });
            
            // Обновляем состояние чекбоксов визуально
            if (!enabled) {
                setCheck();
            }
        }
        
        // Временное раскрытие текста при клике
        function revealText(element) {
            element.classList.add('revealed');
            setTimeout(function() {
                element.classList.remove('revealed');
            }, 2000); // Показываем на 2 секунды
        }
        
        // Обработчики для кнопок скорости
        window.addEventListener('DOMContentLoaded', function() {
            var speedButtons = document.querySelectorAll('.speed-btn');
            speedButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var speed = parseFloat(this.getAttribute('data-speed'));
                    setSpeed(speed);
                });
            });
            
            // Сохраняем оригинальные тексты при загрузке
            var verseLines = document.querySelectorAll('.verse-line label span');
            verseLines.forEach(function(span) {
                span.dataset.originalText = span.textContent;
            });
            
            // Добавляем обработчики двойного клика для выделения куплета
            // Используем свою логику вместо dblclick для более точного определения
            var lastClickedLine = null;
            var lastClickTime = 0;
            var DOUBLE_CLICK_THRESHOLD = 400; // 400ms для двойного клика (быстрее стандартных ~500ms)
            
            var allVerseLines = document.querySelectorAll('.verse-line');
            allVerseLines.forEach(function(verseLine, index) {
                verseLine.addEventListener('click', function(e) {
                    // Не работает в режиме самопроверки
                    if (document.body.classList.contains('self-check-mode')) return;
                    
                    // Не работает во время воспроизведения
                    if (playFlag) return;
                    
                    // Игнорируем клики по чекбоксу (они обрабатываются отдельно)
                    if (e.target.type === 'checkbox') return;
                    
                    var now = Date.now();
                    var timeSinceLastClick = now - lastClickTime;
                    
                    // Проверяем: это двойной клик по ТОЙ ЖЕ строке?
                    if (lastClickedLine === index && timeSinceLastClick < DOUBLE_CLICK_THRESHOLD) {
                        // Двойной клик по одной строке - выделяем куплет
                        e.preventDefault();
                        selectStanzaByLineIndex(index);
                        
                        // Сбрасываем, чтобы третий клик не считался за новый двойной
                        lastClickedLine = null;
                        lastClickTime = 0;
                    } else {
                        // Первый клик - запоминаем
                        lastClickedLine = index;
                        lastClickTime = now;
                    }
                });
            });
            
            // Инициализируем плавающую кнопку
            updateFloatingClearButton();
        });

        window.onload = function(){
            textSizeNormal  = document.getElementById('textSizeNormal');
            textSizeNormal2 = document.getElementById('textSizeNormal2');
            textSizeBig     = document.getElementById('textSizeBig');
            textSizeBig2    = document.getElementById('textSizeBig2');

            textSizeNormal.className  = (textSizeFlag == 1) ? 'textSizeNormal' : 'textSizeNormalActive';
            textSizeNormal2.className = (textSizeFlag == 1) ? 'textSizeNormal' : 'textSizeNormalActive';
            textSizeBig.className     = (textSizeFlag == 1) ? 'textSizeBigActive' : 'textSizeBig';
            textSizeBig2.className    = (textSizeFlag == 1) ? 'textSizeBigActive' : 'textSizeBig';

            currentLabelStyle = (textSizeFlag == 1) ? 'label_partition_check_big' : 'label_partition_check';

            for (i = 0; i < countCheck; i++) {
                var label = document.getElementById('label_partition_'+i);
                label.className = currentLabelStyle;
            }
        }
    </script>
            
    <style>
        /* Специфичные стили только для страницы стиха */
        
        /* Красивые toggle switcher'ы (iOS-style) */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 26px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #d1d5db;
            transition: 0.3s;
            border-radius: 26px;
        }
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        input:checked + .toggle-slider {
            background-color: #10b981;
        }
        input:checked + .toggle-slider:before {
            transform: translateX(22px);
        }
        .toggle-label-text {
            font-weight: 500;
            font-size: 16px;
            color: #374151;
            user-select: none;
        }
        .switch-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        /* Blur для режима самопроверки */
        .blurred-text {
            filter: blur(6px);
            user-select: none;
            cursor: help;
            transition: filter 0.3s ease;
        }
        .blurred-text.revealed {
            filter: blur(0px);
        }
        
        /* Скрываем чекбоксы и элементы управления в режиме самопроверки */
        body.self-check-mode .poem-text input[type="checkbox"] + span::before,
        body.self-check-mode .poem-text input[type="checkbox"] + span::after {
            display: none !important;
        }
        body.self-check-mode .poem-text label {
            cursor: default;
            padding-left: 0;
            opacity: 1 !important;
            pointer-events: auto !important;
        }
        body.self-check-mode .poem-text label::before,
        body.self-check-mode .poem-text label::after {
            display: none !important;
        }
        /* Переопределяем стили disabled чекбоксов в режиме самопроверки */
        body.self-check-mode .poem-text label span {
            color: #000 !important;
            opacity: 1 !important;
        }
        body.self-check-mode .poem-text label:has(input[type="checkbox"]:disabled) {
            opacity: 1 !important;
            pointer-events: auto !important;
            cursor: default !important;
        }
        body.self-check-mode #playBtn,
        body.self-check-mode .quick-selection-panel,
        body.self-check-mode .mobile-speed,
        body.self-check-mode .desktop-speed,
        body.self-check-mode .desktop-volume,
        body.self-check-mode #floatingClearBtn {
            display: none !important;
        }
        /* Отключаем hover эффект на строках в режиме самопроверки */
        body.self-check-mode .poem-text label:hover {
            background: transparent !important;
            transform: none !important;
        }
        /* Убираем разделительную линию в режиме самопроверки */
        body.self-check-mode .desktop-top-row {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }
        
        .label_partition_check,
        .label_partition_check span {
            color: #000;
            font-size: 29px;
            font-family: 'Lora', serif !important;
        }
        .label_partition_uncheck,
        .label_partition_uncheck span {
            color: #555;
            font-size: 29px;
            font-family: 'Lora', serif !important;
        }
        .label_partition_check_big,
        .label_partition_check_big span {
            color: #000;
            font-size: 34px;
            font-family: 'Lora', serif !important;
        }
        .label_partition_uncheck_big,
        .label_partition_uncheck_big span {
            color: #555;
            font-size: 34px;
            font-family: 'Lora', serif !important;
        }
        /* HeroUI стиль чекбоксов для строк стиха */
        .poem-text label {
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            padding: 4px 8px;
            margin: 0 -8px;
            transition: all 0.2s;
            position: relative;
            user-select: none;
            border-radius: 6px;
            font-family: 'Lora', serif !important;
        }
        .poem-text label span {
            font-family: 'Lora', serif !important;
        }
        .poem-text label:hover {
            background: #f5f9ff;
            transform: translateX(2px);
        }
        /* Во время воспроизведения - не подсвечивать hover */
        body.audio-playing .poem-text label:hover {
            background: transparent;
            transform: none;
        }
        /* Выбранная строка - едва заметный голубой фон */
        .poem-text label:has(input[type="checkbox"]:checked) {
            background: #f5f9ff;
        }
        /* Во время воспроизведения - без фона */
        body.audio-playing .poem-text label:has(input[type="checkbox"]:checked) {
            background: transparent;
        }
        .poem-text input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            width: 0;
            height: 0;
        }
        /* Чекбокс контейнер */
        .poem-text label::before {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 16px;
            border: 2px solid #d4d4d8;
            border-radius: 6px;
            background: transparent;
            transition: all 0.15s ease;
            flex-shrink: 0;
            position: relative;
        }
        /* Checked состояние */
        .poem-text label:has(input[type="checkbox"]:checked)::before {
            background: #006FEE;
            border-color: #006FEE;
        }
        /* Галочка */
        .poem-text label:has(input[type="checkbox"]:checked)::after {
            content: '';
            position: absolute;
            left: 15px;
            top: 50%;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: translateY(-50%) rotate(45deg);
            margin-top: -1px;
        }
        /* Hover на чекбоксе */
        .poem-text label:hover::before {
            border-color: #006FEE;
        }
        /* Заблокированные чекбоксы (невыбранные строки) */
        .poem-text input[type="checkbox"]:disabled:not(:checked) + span {
            color: #d4d4d8;
        }
        .poem-text label:has(input[type="checkbox"]:disabled:not(:checked)) {
            cursor: not-allowed;
            opacity: 0.3;
            pointer-events: none;
        }
        .poem-text label:has(input[type="checkbox"]:disabled:not(:checked))::before {
            background: #fafafa;
            border-color: #e5e5e5;
            cursor: not-allowed;
        }
        .poem-text label:has(input[type="checkbox"]:disabled:not(:checked)):hover {
            background: transparent;
            transform: none;
        }
        /* Заблокированные но выбранные чекбоксы (при воспроизведении) */
        .poem-text label:has(input[type="checkbox"]:disabled:checked) {
            cursor: default;
            pointer-events: none;
            /* Текст и фон остаются активными */
        }
        /* Серый чекбокс для disabled:checked */
        .poem-text label:has(input[type="checkbox"]:disabled:checked)::before {
            background: #9ca3af !important;
            border-color: #9ca3af !important;
        }
        .poem-text label:has(input[type="checkbox"]:disabled:checked)::after {
            border-color: white !important;
        }
        .textSizeNormal {
            font-size: 14px;
            color: #e75e65;
            text-decoration: none;
            border-bottom: 1px dotted #e75e65;
            font-family: 'Lato', sans-serif;
            font-weight: 600;
        }
        .textSizeNormalActive {
            font-size: 14px;
            font-color: #333;
            font-family: 'Lato', sans-serif;
            font-weight: 600;
        }
        .textSizeBig {
            font-size: 18px;
            color: #e75e65;
            text-decoration: none;
            border-bottom: 1px dotted #e75e65;
            font-family: 'Lato', sans-serif;
            font-weight: 600;
        }
        .textSizeBigActive {
            font-size: 18px;
            font-color: #333;
            font-family: 'Lato', sans-serif;
            font-weight: 600;
        }
        .textSizeNormal:hover {
            cursor: pointer;
            color: #ff827d;
        }
        .textSizeBig:hover {
            cursor: pointer;
            color: #ff827d;
        }
        .poem-text {
            margin-left: 30px;
            font-family: 'Lora', serif !important;
        }
        .verse-line {
            margin-bottom: 6px;
            line-height: 1;
            transition: all 0.3s ease;
        }
        .verse-line.paragraph-end {
            margin-bottom: 0;
        }
        /* Подсветка текущей проигрываемой строки */
        .verse-line.current label,
        .verse-line.current label span {
            /* Усиленное серое свечение - более выразительное */
            text-shadow: 
                0 0 12px rgba(0, 0, 0, 0.45),
                0 0 6px rgba(0, 0, 0, 0.35);
            
            transition: text-shadow 0.2s ease;
        }
        /* Разделители куплетов */
        .stanza-divider {
            display: flex;
            align-items: center;
            margin: 16px 0;
            position: relative;
        }
        .stanza-divider::before {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(to right, transparent, #e5e7eb 20%, #e5e7eb 80%, transparent);
            margin-right: 12px;
        }
        .stanza-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(to left, transparent, #e5e7eb 20%, #e5e7eb 80%, transparent);
            margin-left: 12px;
        }
        .poem-text {
            font-family: 'Lora', serif;
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            background: white;
            padding: 2px 8px;
            border-radius: 10px;
            letter-spacing: 0.5px;
        }
        /* Панель быстрого выбора */
        .quick-selection-panel {
            margin: 0 0 20px 30px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .quick-hint {
            font-size: 13px;
            color: #6b7280;
            font-family: 'Lato', sans-serif;
            font-style: italic;
            user-select: none;
        }
        /* Скрываем подсказку на мобильных */
        @media (max-width: 768px) {
            .quick-hint {
                display: none;
            }
        }
        
        /* Плавающая кнопка снятия выделения */
        #floatingClearBtn {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            z-index: 999;
            transition: all 0.3s ease;
            animation: fadeIn 0.3s ease;
        }
        #floatingClearBtn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }
        #floatingClearBtn:active {
            transform: scale(0.95);
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        /* Скрываем плавающую кнопку в режиме самопроверки */
        body.self-check-mode #floatingClearBtn {
            display: none !important;
        }
        /* На мобильных устройствах сдвигаем выше */
        @media (max-width: 768px) {
            #floatingClearBtn {
                bottom: 80px;
                right: 15px;
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
        }
        .quick-btn-toggle {
            padding: 8px 20px;
            border: 1px solid #d1fae5;
            border-radius: 8px;
            background: #f0fdf4;
            color: #166534;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Lato', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        .quick-btn-toggle:hover {
            background: #dcfce7;
            border-color: #86efac;
            color: #14532d;
        }
        .quick-btn-toggle.active {
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }
        .quick-btn-toggle.active:hover {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #b91c1c;
        }
        .mobile-controls {
            margin: 0 20px 25px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            background: #f9fafb;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .mobile-top-row {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 10px;
        }
        .mobile-loop {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 16px;
        }
        .mobile-textsize {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .mobile-textsize .divider {
            color: #bbb;
        }
        .mobile-speed {
            margin-top: 12px;
        }
        .mobile-speed > label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #888;
            display: block;
            margin-bottom: 6px;
        }
        .mobile-speed-buttons {
            display: flex;
            justify-content: flex-start;
            gap: 4px;
            flex-wrap: wrap;
        }
        .desktop-controls {
            margin-left: 52px;
            padding: 26px;
            border: 1px solid #e0e0e0;
            border-radius: 13px;
            background: #f9fafb;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        .desktop-top-row {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .desktop-loop {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            font-size: 19px;
        }
        .desktop-textsize {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 19px;
        }
        .desktop-textsize .divider {
            color: #bbb;
        }
        .desktop-volume {
            margin: 15px 0;
        }
        .desktop-volume > label {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #888;
            display: block;
            margin-bottom: 10px;
        }
        .desktop-volume input[type="range"] {
            width: 100%;
        }
        .desktop-speed {
            margin-top: 15px;
        }
        .desktop-speed > label {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #888;
            display: block;
            margin-bottom: 10px;
        }
        .desktop-speed-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        .poem-title {
            text-align: center;
            margin: 26px 0 13px;
            font-size: 42px;
            font-weight: 600;
            color: #2c3e50;
        }
        .poem-back-link {
            text-align: center;
            margin-bottom: 25px;
        }
        .poem-back-link a {
            color: #5cb85c;
            font-weight: 500;
        }
        .poem-back-link a:hover {
            text-decoration: none;
            color: #449d44;
        }
        .fixed {
            position: fixed; 
            top: 20px;
            width: 320px;
            z-index: 100;
            margin-left: 0 !important;
        }
        #status, #status2 {
            display: block;
            margin-bottom: 10px;
            color: #666;
            font-size: 14px;
        }
        #status:empty, #status2:empty {
            display: none;
            margin-bottom: 0;
        }
        
        .speed-btn {
            cursor: pointer;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            transition: all 0.15s ease;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Merriweather', serif;
            min-width: 48px;
            height: 32px;
            margin: 0 3px;
            padding: 4px 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #4b5563;
        }
        .speed-btn:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }
        .speed-btn.active {
            background: #10b981;
            border-color: #10b981;
            color: white;
            font-weight: 600;
        }
        
        /* Адаптация для мобильных устройств */
        @media (max-width: 768px) {
            /* Уменьшаем размер текста стиха на мобильных */
            .label_partition_check,
            .label_partition_check span,
            .label_partition_uncheck,
            .label_partition_uncheck span {
                font-size: 24px !important;
            }
            
            .label_partition_check_big,
            .label_partition_check_big span,
            .label_partition_uncheck_big,
            .label_partition_uncheck_big span {
                font-size: 28px !important;
            }
        }
    </style>

    <!-- Полноэкранный прелоадер -->
    <div id="page-loader">
        <div class="loader-spinner"></div>
        <div class="loader-text">Загрузка страницы...</div>
    </div>

    <!-- Page Content -->
    <div class="container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-header">Зубрилка - учи стихи легко!<br>
                <small>Простая, бесплатная, с гибкими настройками и приятным фоновым чтением</small></h1>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h2 class="poem-title"><?= h($poem_name) ?></h2>
                <p class="poem-back-link">
                    <a href="<?= url() ?>">← Вернуться к списку</a>
                </p>
            </div>
        </div>
        <?php if (!empty($description)): ?>
        <div class="row">
            <div class="col-md-12">
                <h3>Описание</h3>
            </div>
            <div class="col-md-10">
                <p style="font-size: 23px; font-family: 'PT Sans'; margin-top: 26px; margin-left: 39px; text-align: justify; border-left: 3px solid #ccc; padding-left: 26px;">
                    <?= h($description) ?>
                </p>
            </div>
        </div>
        <?php endif; ?>
        <!-- /.row -->

        <div class="row">
            <div class="hidden-lg col-xs-12">
                <div class="mobile-controls" id="player2"> 
                    <div class="mobile-top-row">
                        <div class="switch-row">
                            <label class="toggle-switch">
                                <input type="checkbox" id="loop2" onChange="loopSync(this);" checked>
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label-text">Зациклить</span>
                        </div>
                        <div class="switch-row">
                            <label class="toggle-switch">
                                <input type="checkbox" id="selfCheck2" onChange="selfCheckSync(this);">
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label-text">Режим самопроверки</span>
                        </div>
                        <div class="mobile-textsize">
                            <span class="textSizeNormalActive" onClick="TextSizeSetNormal();" id="textSizeNormal2">Средний</span>
                            <span class="divider">|</span>
                            <span class="textSizeBig" onClick="TextSizeSetBig();" id="textSizeBig2">Крупный</span>
                        </div>
                    </div>
                    <span id='status2'></span>
                    <div class="mobile-speed">
                        <label>Скорость</label>
                        <div class="mobile-speed-buttons">
                            <button class="speed-btn" data-speed="0.65" title="Очень медленно">0.65</button>
                            <button class="speed-btn" data-speed="0.8" title="Медленно">0.8</button>
                            <button class="speed-btn active" data-speed="1" title="Обычная скорость">1.0</button>
                            <button class="speed-btn" data-speed="1.25" title="Быстро">1.25</button>
                            <button class="speed-btn" data-speed="1.5" title="Очень быстро">1.5</button>
                            <button class="speed-btn" data-speed="2" title="Пуля">2</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                <div class="quick-selection-panel">
                    <button class="quick-btn-toggle" id="toggleSelectBtn" onclick="toggleSelection()">
                        <span class="toggle-text">Выбрать всё</span>
                    </button>
                    <span class="quick-hint">💡 Двойной клик выделяет/снимает куплет</span>
                </div>
                <div class="poem-text">
                    <?php 
                    $stanza_number = 1;
                    foreach($verses as $key => $verse): 
                    ?>
                        <?php if ($key === 0): ?>
                            <div class="stanza-divider">
                                <span class="stanza-number"><?= $stanza_number ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="verse-line <?= $verse['is_paragraph_end'] ? 'paragraph-end' : '' ?>" 
                             data-start="<?= $key > 0 ? $verses[$key - 1]['audio_timestamp'] : 0 ?>"
                             data-end="<?= $verse['audio_timestamp'] ?>"
                             id="verse_<?= $key ?>">
                            <label id='label_partition_<?= $key ?>' for='partition_<?= $key ?>'>
                                <input type='checkbox' id='partition_<?= $key ?>' onChange='setCheck();'>
                                <span><?= h($verse['text']) ?></span>
                            </label>
                        </div>
                        
                        <?php if ($verse['is_paragraph_end'] && $key < count($verses) - 1): ?>
                            <?php $stanza_number++; ?>
                            <div class="stanza-divider">
                                <span class="stanza-number"><?= $stanza_number ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>   
            </div>
            <div class="col-lg-4 hidden-md hidden-sm hidden-xs">
                <div class="desktop-controls" id="player"> 
                    <div class="desktop-top-row">
                        <div class="switch-row">
                            <label class="toggle-switch">
                                <input type='checkbox' id='loop' onChange="loopSync(this);" checked>
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label-text">Зациклить</span>
                        </div>
                        <div class="switch-row">
                            <label class="toggle-switch">
                                <input type='checkbox' id='selfCheck' onChange="selfCheckSync(this);">
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label-text">Режим самопроверки</span>
                        </div>
                        <div class="desktop-textsize">
                            <span class="textSizeNormalActive" onClick="TextSizeSetNormal();" id="textSizeNormal">Средний</span>
                            <span class="divider">|</span>
                            <span class="textSizeBig" onClick="TextSizeSetBig();" id="textSizeBig">Крупный</span>
                        </div>
                    </div>
                    <span id='status'></span>
                    <div class="desktop-volume">
                        <label>Громкость</label>
                        <input type="range" id="points" step="0.2" min="0.0" max="1" value="1" onchange="gainChange();">
                    </div>
                    <div class="desktop-speed">
                        <label>Скорость</label>
                        <div class="desktop-speed-buttons">
                            <button class="speed-btn" data-speed="0.65" title="Очень медленно">0.65</button>
                            <button class="speed-btn" data-speed="0.8" title="Медленно">0.8</button>
                            <button class="speed-btn active" data-speed="1" title="Обычная скорость">1.0</button>
                            <button class="speed-btn" data-speed="1.25" title="Быстро">1.25</button>
                            <button class="speed-btn" data-speed="1.5" title="Очень быстро">1.5</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <button name="playBtn" id="playBtn" onClick="play();" value="play" style="width:100%; position: fixed; bottom: 0px; right: 0px; font-size: 24px; padding: 18px;" class="btn btn-lg btn-default" disabled="true">Запустить</button>
        </div>
        
        <!-- Плавающая кнопка снятия выделения -->
        <button id="floatingClearBtn" onclick="clearAllSelections();" title="Снять все выделения">
            ✕
        </button>
        
        <!-- /.row -->

<?php include __DIR__ . '/footer.php'; ?>
