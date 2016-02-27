// [ts] global functions and/or variables for JSLint

var searchtimer;

function closePersonPreview(personID, tree, event) {
    'use strict';
    clearTimeout(searchtimer);
    var entitystr = tree + '_' + personID;
    if (event) {
        entitystr += "_" + event;
    }
    //new Effect.Fade('prev'+entitystr,{duration:.01});
    jQuery('#prev' + entitystr).css('visibility', 'hidden');
}

jQuery(document).ready(function () {
    'use strict';
    jQuery('a.pers').each(function (index, item) {
        var matches = /p(\w*)_t(\w*):*(\w*)/.exec(item.id),
          personID = matches[1],
          tree = matches[2],
          event = matches[3];
        item.onmouseover = function () {
            searchtimer = setTimeout('showPersonPreview(\'' + personID + '\',\'' + tree + '\',\'' + event + '\')', 1000);
        };
        item.onmouseout = function () {
            closePersonPreview(personID, tree, event);
        };
        item.onclick = function () {
            closePersonPreview(personID, tree, event);
        };
    });
    jQuery('a.pl').each(function (index, item) {
        item.title = "<?php echo $text['findplaces']; ?>";
    });
});

function showPersonPreview(personID, tree, event) {
    'use strict';
    var entitystr = tree + '_' + personID,
      params;
    if (event) {
        entitystr += "_" + event;
    }
    jQuery('#prev' + entitystr).css('visibility', 'visible');
    if (!jQuery('#prev' + entitystr).html()) {
        jQuery('#prev' + entitystr).html('<div class="person-inner" id="ld' + entitystr + '"><img src="img/spinner.gif" style="border:0" alt=""> ' + textSnippet('loading') + '</div>');

        params = {personID: personID, tree: tree};
        jQuery.ajax({
            url: 'ajx_perspreview.php',
            data: params,
            dataType: 'html',
            success: function (req) {
                jQuery('#ld' + entitystr).html(req);
            }
        });
    }
    return false;
}
