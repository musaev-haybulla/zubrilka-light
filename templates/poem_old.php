<?php 
$extra_fonts = true;
include __DIR__ . '/header.php'; 
?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script src="<?= asset('js/my.js') ?>"></script>
        
    <script>
        countCheck = <?= count($verses) ?>;
        playFlag   = 0;
        textSizeFlag  = (getCookie('textSizeFlag') !== undefined) ? getCookie('textSizeFlag') : 0; //normal
        langFlag = (getCookie('langFlag') !== undefined) ? getCookie('langFlag') : 'russian'; //normal;
        
        $(window).scroll(function(){
            var window_top = $(window).scrollTop();
            var div_top = $('#player').offset().top;

            if (window_top > div_top) {
                $('#player').addClass('fixed');
            } else {
                $('#player').removeClass('fixed');
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

        function deleteCookie(name) {
            setCookie(name, "", {
                expires: -1
            })
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
                if(document.getElementById('partition_'+langFlag+'_'+i).checked) {
                    if(i == 0){
                        return 0;
                    }
                    return soundArr[i-1];
                }
            }
        }

        function getSoundEnd() {
            for(i = countCheck-1; i >= 0; i--) {
                if(document.getElementById('partition_'+langFlag+'_'+i).checked) {
                    return soundArr[i];
                }
            }
        }

        function setCheck() {
            var flag = 0;
            var countChecked = 0;
            var prev    = new Object();
            var next    = new Object();
            var first   = null;
            var last    = null;
            for(i = 0; i < countCheck; i++){
                var current = document.getElementById('partition_'+langFlag+'_'+i);
                if(current.checked) {
                    flag = 1;
                    countChecked++;
                    last = current;
                    if(first == null) {
                        first = current;
                    }
                }
            }
            if(flag == 0) {
                for(i = 0; i < countCheck; i++){
                    var current = document.getElementById('partition_'+langFlag+'_'+i);
                    var currentLabel = document.getElementById('label_partition_'+langFlag+'_'+i);
                    current.disabled = false;
                    if (currentLabel.className.indexOf('big') == "-1") {
                        currentLabel.className = 'label_partition_'+langFlag+'_check';
                    } else {
                        currentLabel.className = 'label_partition_'+langFlag+'_check_big';
                    }
                    
                }
                return;
            }
            for (i = 0; i < countCheck; i++) {
                var current = document.getElementById('partition_'+langFlag+'_'+i);
                var currentLabel = document.getElementById('label_partition_'+langFlag+'_'+i);
                
                if (i != 0) {
                    var prev = document.getElementById('partition_'+langFlag+'_'+(i-1));
                }
                if (i != (countCheck-1)) {
                    var next = document.getElementById('partition_'+langFlag+'_'+(i+1));
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
                        currentLabel.className = 'label_partition_'+langFlag+'_check';
                    } else {
                        currentLabel.className = 'label_partition_'+langFlag+'_check_big';
                    }
                } else {
                    if (currentLabel.className.indexOf('big') == "-1") {
                        currentLabel.className = 'label_partition_'+langFlag+'_uncheck';
                    } else {
                        currentLabel.className = 'label_partition_'+langFlag+'_uncheck_big';
                    }
                }                         
            }
        }

        var arg = <?= json_encode($verses, JSON_UNESCAPED_UNICODE) ?>;
        var soundArr = getSoundArr(arg);

        // создаем аудио контекст
        var context;
       
        var contextClass = (window.AudioContext || 
        window.webkitAudioContext || 
        window.mozAudioContext || 
        window.oAudioContext || 
        window.msAudioContext);
        
        if (contextClass) {
          // Web Audio API is available.
          var context = new contextClass();
        }
       
        // переменные для буфера, источника и получателя
        var buffer, source, destination; 
        
        // функция для подгрузки файла в буфер
        var loadSoundFile = function(url) {
            // делаем XMLHttpRequest (AJAX) на сервер
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.responseType = 'arraybuffer'; // важно
            xhr.onprogress = function(event) {
                var percentComplete = (event.loaded / event.total) * 100;
                if(percentComplete < 100) {
                    document.getElementById('percent').innerHTML = percentComplete.toPrecision(3)+'%';
                    document.getElementById('percent2').innerHTML = percentComplete.toPrecision(3)+'%';
                } else {
                    document.getElementById('status').innerHTML = '';
                    document.getElementById('status2').innerHTML = '';
                    playBtnButton = document.getElementById('playBtn');
                    playBtnButton.className = 'btn btn-lg btn-success';
                    playBtnButton.innerHTML = 'Запустить';
                    playBtnButton.disabled = false;
                }
            }
            xhr.onload = function(e) {
                // декодируем бинарный ответ
                context.decodeAudioData(this.response,
                function(decodedArrayBuffer) {
                    // получаем декодированный буфер
                    buffer = decodedArrayBuffer;
                }, function(e) {
                    console.log('Error decoding file', e);
                });
            };
            xhr.send();
        }
        
        window.onkeyup = function(e){
            var k = e.keyCode;
            if(k == 13){
                // нажат enter
                play();
            }
        }
        // функция начала воспроизведения
        var play = function(){
            
            if (getSoundStart() === undefined) {
                alert('Вы должны выбрать фрагмент или несколько фрагментов, отметив их галочками');
                return;
            }
            playFlag = (+!playFlag); // меняем 0 на 1 и 1 на 0 (operator + convert boolean to int)
           
            if (playFlag) {
                playBtnButton = document.getElementById('playBtn');
                playBtnButton.className = 'btn btn-lg btn-warning';
                playBtnButton.innerHTML = 'Остановить';

                // создаем источник
                source           = context.createBufferSource();
                function onEnded() {
                    playFlag = 0;
                    playBtnButton = document.getElementById('playBtn');
                    playBtnButton.className = 'btn btn-lg btn-success';
                    playBtnButton.innerHTML = 'Запустить';
                    setCheck();
                }
                source.onended = onEnded;
                
                // подключаем буфер к источнику
                source.buffer    = buffer;
                // считываем и устанавливаем цикличность
                var loop    = document.getElementById('loop');
                source.loop = loop.checked;
                // начало и конец проигрывания
                source.loopStart = getSoundStart();
                if (source.loop) {
                    source.loopEnd = getSoundEnd();
                } else {
                    source.loopEnd = getSoundEnd()-getSoundStart();
                }
                // дефолтный получатель звука
                source.playbackRate.value = playbackRateChange(); 

                destination      = context.destination;
                //создание объекта GainNode и его привязка
                gainNode = context.createGain ? context.createGain() : context.createGainNode();
                source.connect(gainNode);
                gainNode.connect(destination);
                gainNode.gain.value = gainChange();
                
                allCheckDisabled(); // вырубаем все check
                // воспроизводим
                if(!source.loop) {
                    source.start(0, source.loopStart, source.loopEnd);
                } else {
                    source.start(0, source.loopStart);
                }
            } else {
                playBtnButton = document.getElementById('playBtn');
                playBtnButton.className = 'btn btn-lg btn-success';
                playBtnButton.innerHTML = 'Запустить';
                source.stop(0);
            }
        }

        function allCheckDisabled() {
            for (i = 0; i < countCheck; i++) {
                var current = document.getElementById('partition_'+langFlag+'_'+i);
                current.disabled = true;              
            }
        }
        
        function gainChange() {
            gainNode.gain.value = document.getElementById('points').value;
            return gainNode.gain.value;
        }

        function playbackRateChange() {
            source.playbackRate.value = document.getElementById('playbackRate').value;
            return source.playbackRate.value;
        }

        function isAllChecked() {
            for (i = 0; i < countCheck; i++) {
                var current = document.getElementById('partition_'+langFlag+'_'+i);
                if(current.checked) {
                    return false;
                }
            }
            return true;
        }

        window.onload = function(){
            langRussian   = document.getElementById('langRussian');
            langRussian2  = document.getElementById('langRussian2');
            langArabian   = document.getElementById('langArabian');
            langArabian2  = document.getElementById('langArabian2');

            langRussian.className  = (langFlag == 'russian') ? 'langRussianActive' : 'langRussian';
            langRussian2.className = (langFlag == 'russian') ? 'langRussianActive' : 'langRussian';
            langArabian.className  = (langFlag == 'arabian') ? 'langArabianActive' : 'langArabian';
            langArabian2.className = (langFlag == 'arabian') ? 'langArabianActive' : 'langArabian';;


            textSizeNormal  = document.getElementById('textSizeNormal');
            textSizeNormal2 = document.getElementById('textSizeNormal2');
            textSizeBig     = document.getElementById('textSizeBig');
            textSizeBig2    = document.getElementById('textSizeBig2');

            textSizeNormal.className  = (textSizeFlag == 1) ? 'textSizeNormal' : 'textSizeNormalActive';
            textSizeNormal2.className = (textSizeFlag == 1) ? 'textSizeNormal' : 'textSizeNormalActive';
            textSizeBig.className     = (textSizeFlag == 1) ? 'textSizeBigActive' : 'textSizeBig';
            textSizeBig2.className    = (textSizeFlag == 1) ? 'textSizeBigActive' : 'textSizeBig';

            russianStyle = document.getElementById('russian');
            arabianStyle = document.getElementById('arabian');
            russianStyle.style.display = (langFlag == 'russian') ? 'block' : 'none';
            arabianStyle.style.display = (langFlag == 'arabian') ? 'block' : 'none';

            currentLabelRussianStyle = (textSizeFlag == 1) ? 'label_partition_russian_check_big' : 'label_partition_russian_check';
            currentLabelArabianStyle = (textSizeFlag == 1) ? 'label_partition_arabian_check_big' : 'label_partition_arabian_check';

            for (i = 0; i < countCheck; i++) {
                var russianLabel = document.getElementById('label_partition_russian_'+i);
                var arabianLabel = document.getElementById('label_partition_arabian_'+i);
                russianLabel.className = currentLabelRussianStyle;
                arabianLabel.className = currentLabelArabianStyle;
            }
        }

        loadSoundFile('<?= media($poem_number . '/' . $poem_number . '.mp3') ?>');
    </script>
            
    <style>
        .label_partition_russian_check {
            color: #000;
            font-size: 22px;
        }
        .label_partition_russian_uncheck {
            color: #ccc;
            font-size: 22px;
        }
        .label_partition_russian_check_big {
            color: #000;
            font-size: 26px;
        }
        .label_partition_russian_uncheck_big {
            color: #ccc;
            font-size: 26px;
        }
        .label_partition_arabian_check {
            color: #000;
            font-size: 48px;
        }
        .label_partition_arabian_uncheck {
            color: #ccc;
            font-size: 48px;
        }
        .label_partition_arabian_check_big {
            color: #000;
            font-size: 62px;
        }
        .label_partition_arabian_uncheck_big {
            color: #ccc;
            font-size: 62px;
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
        .langRussian {
            color: #e75e65;
            text-decoration: none;
            border-bottom: 1px dotted #e75e65; 
        }
        .langRussianActive {
            font-color: #333;
        }
        .langArabian {
            color: #e75e65;
            text-decoration: none;
            border-bottom: 1px dotted #e75e65;  
        }
        .langArabianActive {
            font-color: #333;
        }
        .langRussian:hover {
            cursor: pointer;
            color: #ff827d;
        }
        .langArabian:hover {
            cursor: pointer;
            color: #ff827d;
        }
        .russian {
            margin-left: 30px;
        }
        .arabian {
            margin-right: 30px;
            direction: rtl;
        }
        .fixed {
            position:fixed; 
            top:0px;
        }
    </style>

    <!-- Page Content -->
    <div class="container">

        <!-- Page Heading/Breadcrumbs -->
        <div class="row">
            <div class="col-md-12">
                <h1 class="page-header">АудиоДуа.Онлайн - система заучивания дуа<br>
                <small>Простая, бесплатная, с гибкими настройками и приятным фоновым чтением</small></h1>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h3>Описание</h3>
            </div>
            <div class="col-md-10">
                <p style="font-size: 18px; font-family: 'PT Sans'; margin-top: 20px; margin-left: 30px; text-align: justify; border-left: 2px solid #ccc; padding-left: 20px;">
                    <?= h($description) ?>
                </p>
            </div>
        </div>
        <!-- /.row -->

        <!-- Portfolio Item Row -->
        <div class="row">
            <div class="col-md-12">
            <div style="margin-left: 30px; margin-right: 30px; border-bottom: dashed #ccc 1px; margin-bottom: 30px; padding-bottom: 10px;">
                <h2 style="text-align: center;">Плеер</h2>
            </div>
            </div>
        </div>
        <div class="row">
            <div class="hidden-lg hidden-md hidden-sm col-xs-12">
                <div style="margin-left: 30px; margin-right: 30px; border-bottom: dashed #ccc 1px; margin-bottom: 10px; padding-bottom: 10px; margin-top: -15px;" id="player2"> 
                    <input type="checkbox" id="loop2" onChange="checkSync(this);" checked>&nbsp;
                    <label for='loop2'>Зациклить</label>&nbsp;&nbsp;
                    <span class="langRussianActive" onClick="LangSetRussian();" id="langRussian2">Русский</span> | 
                    <span class="langArabian" onClick="LangSetArabian();" id="langArabian2">Арабский</span>&nbsp;&nbsp;
                    <span class="textSizeNormalActive" onClick="TextSizeSetNormal();" id="textSizeNormal2">Средний</span> | 
                    <span class="textSizeBig" onClick="TextSizeSetBig();" id="textSizeBig2">Крупный</span>&nbsp;
                    <span id='status2'>пожалуйста, подождите...<br> загружается аудиофайл (<span id='percent2'></span>)</span>
                </div>
            </div>
            <div class="col-md-8 col-sm-8 col-xs-12">
                <div id="russian" class="russian">
                    <?php foreach($verses as $key => $verse): ?>
                        <label id='label_partition_russian_<?= $key ?>' for='partition_russian_<?= $key ?>'>
                            <input type='checkbox' id='partition_russian_<?= $key ?>' onChange='setCheck();'>
                            &nbsp;<?= h($verse['text_ru']) ?>
                        </label>
                        <br />
                        <?php if ($verse['is_paragraph_end']): ?>
                            <br />
                        <?php endif; ?> 
                    <?php endforeach; ?>
                </div>
                <div id="arabian" class="arabian">
                    <?php foreach($verses as $key => $verse): ?>
                        <label id='label_partition_arabian_<?= $key ?>' for='partition_arabian_<?= $key ?>'>
                            <input type='checkbox' id='partition_arabian_<?= $key ?>' onChange='setCheck();'>
                            &nbsp;<?= h($verse['text_ar']) ?>
                        </label>
                        <br />
                        <?php if ($verse['is_paragraph_end']): ?>
                            <br />
                        <?php endif; ?> 
                    <?php endforeach; ?>
                </div>   
            </div>
            <div class="col-md-4 col-sm-4 hidden-xs">
                <div style="margin-left: 40px;" id="player"> 
                    <h4>Язык</h4>
                    <span class="langRussianActive" onClick="LangSetRussian();" id="langRussian">Русский</span> | <span class="langArabian" onClick="LangSetArabian();" id="langArabian">Арабский</span>
                    <br />
                    <h4>Размер текста</h4>
                    <span class="textSizeNormalActive" onClick="TextSizeSetNormal();" id="textSizeNormal">Средний</span> | <span class="textSizeBig" onClick="TextSizeSetBig();" id="textSizeBig">Крупный</span>
                    <br /><br />
                    <span id='status'>пожалуйста, подождите...<br> загружается аудиофайл (<span id='percent'></span>)</span>
                    <input type='checkbox' id='loop' onChange="checkSync(this);" checked>&nbsp;
                    <label for='loop'>Зациклить</label><br /><br />
                    <input style="width:224px;" type="range" id="points" step="0.2" min="0.0" max="1" value="1" onchange="gainChange();">
                    <label>Громкость</label><br />
                    <input style="width:224px;" type="range" id="playbackRate" step="0.2" min="0.8" max="1.2" value="1" onchange="playbackRateChange();">
                    <label>Скорость</label>
                </div>
            </div>
        </div>
        <div class="row">
            <button name="playBtn" id="playBtn" onClick="play();" value="play" style="width:100%; position: fixed; bottom: 0px; right: 0px;" class="btn btn-lg btn-default" disabled="true">Запустить</button>
        </div>
        <!-- /.row -->

<?php include __DIR__ . '/footer.php'; ?>
