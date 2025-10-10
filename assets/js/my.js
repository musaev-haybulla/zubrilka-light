function TextSizeSetNormal() {
    textSizeNormal  = document.getElementById('textSizeNormal');
    textSizeNormal2 = document.getElementById('textSizeNormal2');
    textSizeBig     = document.getElementById('textSizeBig');
    textSizeBig2    = document.getElementById('textSizeBig2');
    textSizeNormal.className = 'textSizeNormalActive';
    textSizeNormal2.className = 'textSizeNormalActive';
    textSizeBig.className = 'textSizeBig';
    textSizeBig2.className = 'textSizeBig';

    allChecked = isAllChecked();
    for (i = 0; i < countCheck; i++) {
        var current = document.getElementById('partition_'+i);
        var currentLabel = document.getElementById('label_partition_'+i);

        if(!current.checked && !allChecked) {
            currentLabel.className = 'label_partition_uncheck';
        } else {
            currentLabel.className = 'label_partition_check';
        }                         
    }
    textSizeFlag = 0;
    setCookie('textSizeFlag', textSizeFlag);
}

function TextSizeSetBig() {
    textSizeNormal  = document.getElementById('textSizeNormal');
    textSizeNormal2 = document.getElementById('textSizeNormal2');
    textSizeBig     = document.getElementById('textSizeBig');
    textSizeBig2    = document.getElementById('textSizeBig2');
    textSizeNormal.className  = 'textSizeNormal';
    textSizeNormal2.className = 'textSizeNormal';
    textSizeBig.className  = 'textSizeBigActive';
    textSizeBig2.className = 'textSizeBigActive';

    allChecked = isAllChecked();
    for (i = 0; i < countCheck; i++) {
        var current = document.getElementById('partition_'+i);
        var currentLabel = document.getElementById('label_partition_'+i);

        if(!current.checked && !allChecked) {
            currentLabel.className = 'label_partition_uncheck_big';
        } else {
            currentLabel.className = 'label_partition_check_big';
        }                         
    }
    textSizeFlag = 1;
    setCookie('textSizeFlag', textSizeFlag);
}

function isAllChecked() {
    for (i = 0; i < countCheck; i++) {
        var current = document.getElementById('partition_'+i);
        if(current && current.checked) {
            return false;
        }
    }
    return true;
}

function checkSync(looper) {
    document.getElementById('loop').checked = looper.checked;
    document.getElementById('loop2').checked = looper.checked;
}