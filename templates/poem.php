<?php 
$extra_fonts = true;
include __DIR__ . '/header.php'; 
?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Lato:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400&display=swap" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.4/howler.min.js"></script>
    <script src="<?= asset('js/my.js') ?>"></script>
        
    <script>
        countCheck = <?= count($verses) ?>;
        playFlag   = 0;
        textSizeFlag  = (getCookie('textSizeFlag') !== undefined) ? getCookie('textSizeFlag') : 0;
        
        var playerOriginalTop = 0;
        var playerOriginalLeft = 0;
        
        $(document).ready(function() {
            var $player = $('#player');
            playerOriginalTop = $player.offset().top;
            playerOriginalLeft = $player.offset().left;
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
            
            // Определяем Safari
            var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
            console.log('🎵 Howl #' + howlInstanceCount + ' | Pool:', Howler._howls.length, '| Safari:', isSafari);
            
            sound = new Howl({
                src: [url],
                html5: isSafari, // Safari работает лучше с HTML5 Audio
                preload: true,
                format: ['mp3'],
                pool: isSafari ? 5 : 1, // Для Safari увеличиваем пул
                onload: function() {
                    document.getElementById('status').innerHTML = '';
                    document.getElementById('status2').innerHTML = '';
                    var playBtn = document.getElementById('playBtn');
                    playBtn.className = 'btn btn-lg btn-success';
                    playBtn.innerHTML = 'Запустить';
                    playBtn.disabled = false;
                },
                onloaderror: function(id, error) {
                    console.error('Ошибка загрузки:', id, error);
                    alert('Ошибка загрузки аудиофайла: ' + error);
                },
                onplayerror: function(id, error) {
                    console.error('Ошибка воспроизведения:', id, error);
                    alert('Ошибка воспроизведения: ' + error);
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
                    sound.seek(soundStart);
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
                
                var loop = document.getElementById('loop').checked;
                var speed = currentSpeed;
                var volume = parseFloat(document.getElementById('points').value);
                
                // Настраиваем Howler (НЕ используем встроенный loop!)
                sound.loop(false); // Всегда false, зацикливание делаем вручную
                sound.rate(speed);
                sound.volume(volume);
                
                sound.seek(soundStart);
                
                allCheckDisabled();
                
                // Запускаем воспроизведение
                sound.play();
                
                // Запускаем подсветку текущей строки
                startHighlighting();
                
                // Небольшая задержка для проверки состояния и запуска мониторинга
                setTimeout(function() {
                    // Попробуем принудительно установить громкость
                    sound.volume(1.0);
                    Howler.volume(1.0);
                    
                    // Запускаем мониторинг ПОСЛЕ того как звук точно начал играть
                    if (loop) {
                        monitorLoop();
                    } else {
                        monitorOnce();
                    }
                }, 100);
                
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
        
        // Быстрый выбор: выбрать куплет
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
            
            // Находим начало куплета (идём вверх до paragraph-end предыдущего куплета)
            var stanzaStart = 0;
            for (i = selectedIndex - 1; i >= 0; i--) {
                var verseDiv = document.getElementById('partition_'+i).closest('.verse-line');
                if (verseDiv && verseDiv.classList.contains('paragraph-end')) {
                    stanzaStart = i + 1;
                    break;
                }
            }
            
            // Находим конец куплета (идём вниз до paragraph-end)
            var stanzaEnd = countCheck - 1;
            for (i = selectedIndex; i < countCheck; i++) {
                var verseDiv = document.getElementById('partition_'+i).closest('.verse-line');
                if (verseDiv && verseDiv.classList.contains('paragraph-end')) {
                    stanzaEnd = i;
                    break;
                }
            }
            
            // Выделяем весь куплет
            for (i = stanzaStart; i <= stanzaEnd; i++) {
                document.getElementById('partition_'+i).checked = true;
            }
            setCheck();
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
        }
        
        // Очищаем старый выбор шрифта из localStorage
        window.addEventListener('DOMContentLoaded', function() {
            localStorage.removeItem('selectedFont');
        });
        
        // Подсветка текущей строки при воспроизведении
        var highlightInterval = null;
        var currentStanzaNumber = null;
        var isHighlightingActive = false; // Флаг активности
        
        function startHighlighting() {
            if (highlightInterval) return; // Уже запущено
            currentStanzaNumber = null; // Сброс при старте
            isHighlightingActive = true;
            
            highlightInterval = setInterval(function() {
                if (sound && sound.playing() && isHighlightingActive) {
                    var currentTime = sound.seek();
                    highlightCurrentLine(currentTime);
                }
            }, 100); // Проверяем каждые 100мс
        }
        
        function stopHighlighting() {
            isHighlightingActive = false; // Сначала отключаем флаг
            
            if (highlightInterval) {
                clearInterval(highlightInterval);
                highlightInterval = null;
            }
            currentStanzaNumber = null;
            
            // Небольшая задержка перед удалением подсветки
            setTimeout(function() {
                document.querySelectorAll('.verse-line').forEach(function(line) {
                    line.classList.remove('current');
                });
            }, 50);
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
        
        function highlightCurrentLine(currentTime) {
            // Не обрабатываем если подсветка неактивна
            if (!isHighlightingActive) return;
            
            var found = false;
            document.querySelectorAll('.verse-line').forEach(function(line) {
                var start = parseFloat(line.dataset.start);
                var end = parseFloat(line.dataset.end);
                
                // Проверяем диапазон времени
                if (currentTime >= start && currentTime < end && !found) {
                    if (!line.classList.contains('current')) {
                        line.classList.add('current');
                        
                        // Определяем номер куплета
                        var stanzaNum = getStanzaNumber(line);
                        
                        // Скроллим только при смене куплета или первом запуске
                        if (isHighlightingActive && (currentStanzaNumber === null || currentStanzaNumber !== stanzaNum)) {
                            currentStanzaNumber = stanzaNum;
                            line.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                    found = true;
                } else {
                    line.classList.remove('current');
                }
            });
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
        
        // Обработчики для кнопок скорости
        window.addEventListener('DOMContentLoaded', function() {
            var speedButtons = document.querySelectorAll('.speed-btn');
            speedButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var speed = parseFloat(this.getAttribute('data-speed'));
                    setSpeed(speed);
                });
            });
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

        loadSoundFile('<?= media($poem_number . '/' . $poem_number . '.mp3') ?>');
    </script>
            
    <style>
        /* Специфичные стили только для страницы стиха */
        
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
            background: #e6f3ff;
            transform: translateX(2px);
        }
        /* Выбранная строка - голубой фон */
        .poem-text label:has(input[type="checkbox"]:checked) {
            background: #e6f3ff;
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
        .verse-line.current label {
            font-weight: 700;
            transition: font-weight 0.2s ease;
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
            justify-content: space-between;
            align-items: center;
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
            gap: 12px;
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
        .desktop-loop input[type="checkbox"] {
            width: 23px;
            height: 23px;
            cursor: pointer;
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
    </style>

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
                        <label class="mobile-loop">
                            <input type="checkbox" id="loop2" onChange="checkSync(this);" checked>
                            <span>Зациклить</span>
                        </label>
                        <div class="mobile-textsize">
                            <span class="textSizeNormalActive" onClick="TextSizeSetNormal();" id="textSizeNormal2">Средний</span>
                            <span class="divider">|</span>
                            <span class="textSizeBig" onClick="TextSizeSetBig();" id="textSizeBig2">Крупный</span>
                        </div>
                    </div>
                    <span id='status2'>пожалуйста, подождите...<br> загружается аудиофайл</span>
                    <div class="mobile-speed">
                        <label>Скорость</label>
                        <div class="mobile-speed-buttons">
                            <button class="speed-btn" data-speed="0.65" title="Очень медленно">0.65</button>
                            <button class="speed-btn" data-speed="0.8" title="Медленно">0.8</button>
                            <button class="speed-btn active" data-speed="1" title="Обычная скорость">1.0</button>
                            <button class="speed-btn" data-speed="1.25" title="Быстро">1.25</button>
                            <button class="speed-btn" data-speed="1.5" title="Очень быстро">1.5</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                <div class="quick-selection-panel">
                    <button class="quick-btn-toggle" id="toggleSelectBtn" onclick="toggleSelection()">
                        <span class="toggle-text">Выбрать всё</span>
                    </button>
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
                        <label class="desktop-loop">
                            <input type='checkbox' id='loop' onChange="checkSync(this);" checked>
                            <span>Зациклить</span>
                        </label>
                        <div class="desktop-textsize">
                            <span class="textSizeNormalActive" onClick="TextSizeSetNormal();" id="textSizeNormal">Средний</span>
                            <span class="divider">|</span>
                            <span class="textSizeBig" onClick="TextSizeSetBig();" id="textSizeBig">Крупный</span>
                        </div>
                    </div>
                    <span id='status'>пожалуйста, подождите...<br> загружается аудиофайл</span>
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
        <!-- /.row -->

<?php include __DIR__ . '/footer.php'; ?>
