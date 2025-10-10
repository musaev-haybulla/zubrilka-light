<?php 
$extra_fonts = true;
include __DIR__ . '/header.php'; 
?>

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
            
            if(flag == 0) {
                for(i = 0; i < countCheck; i++){
                    var current = document.getElementById('partition_'+i);
                    var currentLabel = document.getElementById('label_partition_'+i);
                    current.disabled = false;
                    if (currentLabel.className.indexOf('big') == "-1") {
                        currentLabel.className = 'label_partition_check';
                    } else {
                        currentLabel.className = 'label_partition_check_big';
                    }
                }
                return;
            }
            
            for (i = 0; i < countCheck; i++) {
                var current = document.getElementById('partition_'+i);
                var currentLabel = document.getElementById('label_partition_'+i);
                
                if (i != 0) {
                    var prev = document.getElementById('partition_'+(i-1));
                }
                if (i != (countCheck-1)) {
                    var next = document.getElementById('partition_'+(i+1));
                }

                if( (current !== first && current !== last) && 
                    (prev !== last && next !== first) ) {
                    current.disabled = true;
                }
                else {
                    current.disabled = false;
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
        
        // Загрузка аудиофайла
        var loadSoundFile = function(url) {
            console.log('Загрузка аудио:', url);
            sound = new Howl({
                src: [url],
                html5: true, // Попробуем HTML5 Audio вместо Web Audio API
                preload: true,
                format: ['mp3'],
                onload: function() {
                    console.log('Аудио загружено успешно');
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
        
        var play = function(){
            
            if (getSoundStart() === undefined) {
                alert('Вы должны выбрать фрагмент или несколько фрагментов, отметив их галочками');
                return;
            }
            
            playFlag = (+!playFlag);
           
            if (playFlag) {
                console.log('Начинаем воспроизведение');
                
                // Разблокируем AudioContext (для Chrome/Safari)
                if (Howler.ctx && Howler.ctx.state === 'suspended') {
                    Howler.ctx.resume().then(function() {
                        console.log('AudioContext resumed');
                    });
                }
                
                var playBtn = document.getElementById('playBtn');
                playBtn.className = 'btn btn-lg btn-warning';
                playBtn.innerHTML = 'Остановить';
                
                soundStart = getSoundStart();
                soundEnd = getSoundEnd();
                console.log('Start:', soundStart, 'End:', soundEnd);
                
                var loop = document.getElementById('loop').checked;
                var speed = currentSpeed;
                var volume = parseFloat(document.getElementById('points').value);
                
                console.log('Loop:', loop, 'Speed:', speed, 'Volume:', volume);
                
                // Настраиваем Howler (НЕ используем встроенный loop!)
                sound.loop(false); // Всегда false, зацикливание делаем вручную
                sound.rate(speed);
                sound.volume(volume);
                
                console.log('Volume set to:', sound.volume());
                console.log('Muted:', sound.mute());
                console.log('Howler global volume:', Howler.volume());
                console.log('Howler global muted:', Howler._muted);
                
                sound.seek(soundStart);
                
                console.log('Seek position:', sound.seek());
                
                allCheckDisabled();
                
                // Запускаем воспроизведение
                var playId = sound.play();
                console.log('Play ID:', playId);
                
                // Небольшая задержка для проверки состояния
                setTimeout(function() {
                    console.log('Playing state after start:', sound.playing());
                    console.log('Current position:', sound.seek());
                    console.log('Sound state:', sound.state());
                    
                    // Попробуем принудительно установить громкость
                    sound.volume(1.0);
                    Howler.volume(1.0);
                    console.log('Forced volume to 1.0');
                }, 100);
                
                if (loop) {
                    // Для зацикливания следим за позицией
                    monitorLoop();
                } else {
                    // Для одноразового воспроизведения ставим таймер
                    var duration = (soundEnd - soundStart) / speed;
                    setTimeout(function() {
                        if (playFlag && sound.playing()) {
                            sound.stop();
                            playFlag = 0;
                            playBtn.className = 'btn btn-lg btn-success';
                            playBtn.innerHTML = 'Запустить';
                            setCheck();
                        }
                    }, duration * 1000);
                }
                
            } else {
                if (sound) {
                    sound.stop();
                }
                if (loopMonitorId) {
                    cancelAnimationFrame(loopMonitorId);
                    loopMonitorId = null;
                }
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
        .label_partition_check {
            color: #000;
            font-size: 29px;
        }
        .label_partition_uncheck {
            color: #ccc;
            font-size: 29px;
        }
        .label_partition_check_big {
            color: #000;
            font-size: 34px;
        }
        .label_partition_uncheck_big {
            color: #ccc;
            font-size: 34px;
        }
        /* HeroUI стиль чекбоксов для строк стиха */
        .poem-text label {
            display: inline-flex;
            align-items: center;
            cursor: pointer;
            padding: 6px 8px;
            transition: all 0.2s;
            position: relative;
            user-select: none;
        }
        .poem-text label:hover {
            opacity: 0.8;
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
            margin-right: 12px;
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
            left: 19px;
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
        .textSizeNormal {
            font-size: 14px;
            color: #e75e65;
            text-decoration: none;
            border-bottom: 1px dotted #e75e65; 
        }
        .textSizeNormalActive {
            font-size: 14px;
            font-color: #333;
        }
        .textSizeBig {
            font-size: 18px;
            color: #e75e65;
            text-decoration: none;
            border-bottom: 1px dotted #e75e65;  
        }
        .textSizeBigActive {
            font-size: 18px;
            font-color: #333;
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
            gap: 6px;
            font-weight: 500;
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
            justify-content: space-between;
            gap: 6px;
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
            gap: 6px;
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
            border: 3px solid #ddd;
            border-radius: 10px;
            background: #fff;
            transition: all 0.2s;
            font-size: 18px;
            font-weight: 600;
            min-width: 65px;
            height: 49px;
            margin: 4px;
            padding: 7px 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .speed-btn:hover {
            background: #f5f5f5;
            border-color: #999;
            transform: scale(1.1);
        }
        .speed-btn.active {
            background: #5cb85c !important;
            border-color: #4cae4c !important;
            box-shadow: 0 2px 8px rgba(92, 184, 92, 0.4);
            transform: scale(1.15);
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
            <div class="hidden-lg hidden-md hidden-sm col-xs-12">
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
            <div class="col-md-8 col-sm-8 col-xs-12">
                <div class="poem-text">
                    <?php foreach($verses as $key => $verse): ?>
                        <label id='label_partition_<?= $key ?>' for='partition_<?= $key ?>'>
                            <input type='checkbox' id='partition_<?= $key ?>' onChange='setCheck();'>
                            <span><?= h($verse['text']) ?></span>
                        </label>
                        <br />
                        <?php if ($verse['is_paragraph_end']): ?>
                            <br />
                        <?php endif; ?> 
                    <?php endforeach; ?>
                </div>   
            </div>
            <div class="col-md-4 col-sm-4 hidden-xs">
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
