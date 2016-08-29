// [ts] global functions and/or variables for JSLint

var searchtimer;

function closePersonPreview(personID, event) {
    'use strict';
    clearTimeout(searchtimer);
    var entitystr = '_' + personID;
    if (event) {
        entitystr += "_" + event;
    }

    $('#prev' + entitystr).css('visibility', 'hidden');
}

$(document).ready(function () {
    'use strict';
    $('a.pers').each(function (index, item) {
        var matches = /p(\w*)_t(\w*):*(\w*)/.exec(item.id),
            personID = matches[1],
            event = matches[3];
        item.onmouseover = function () {
            searchtimer = setTimeout('showPersonPreview(\'' + personID + '\',\'' + event + '\')', 1000);
        };
        item.onmouseout = function () {
            closePersonPreview(personID, event);
        };
        item.onclick = function () {
            closePersonPreview(personID, event);
        };
    });
    $('a.pl').each(function (index, item) {
        item.title = "<?php echo $text['findplaces']; ?>";
    });
});

function showPersonPreview(personID, event) {
    'use strict';
    var entitystr = '_' + personID,
        params;
    if (event) {
        entitystr += "_" + event;
    }
    $('#prev' + entitystr).css('visibility', 'visible');
    if (!$('#prev' + entitystr).html()) {
        $('#prev' + entitystr).html('<div class="person-inner" id="ld' + entitystr + '"><img src="img/spinner.gif" style="border:0" alt=""> ' + textSnippet('loading') + '</div>');

        params = {personID: personID};
        $.ajax({
            url: 'ajx_perspreview.php',
            data: params,
            dataType: 'html',
            success: function (req) {
                $('#ld' + entitystr).html(req);
            }
        });
    }
    return false;
}
