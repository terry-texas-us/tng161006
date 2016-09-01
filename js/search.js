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

    $('.popover-dismiss').popover({
        trigger: 'focus'
    });
    
    $(".person-popover").popover({
        title: 'Default title',
        content: 'Default content'
    });

    $('.person-popover').on('click', function () {
        var personId = $(this).data('personId');
        var id = $(this).attr('aria-describedby');
        var titleStart, titleEnd, title, content;

        var params = {personID : personId};

        $.ajax({
            url: 'ajx_BuildPersonContent.php',
            data: params,
            dataType: 'html',
            success: function (req) {
                titleStart = req.search('>') + 1;
                titleEnd = req.search('</div>');
                title = req.slice(titleStart, titleEnd);
                content = req.slice(titleEnd + 6);
                $('#' + id + ' .popover-title').html(title.trim());
                $('#' + id + ' .popover-content').html(content.trim());
            }
        });
    });
});
