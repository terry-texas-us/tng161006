// [ts] global functions and variables for jsLint
/*global ModalDialog, SVGInjector */

var tnglitbox;

function updateMore(form) {
    'use strict';
    var params = $(form).serialize();
    $.ajax({
        url: 'admin_updatemore.php',
        data: params,
        dataType: 'html',
        success: function (data) {
            var dataJson = $.parseJSON(data);
            if (dataJson.status === 1) {
                $('svg[data-event-id=' + dataJson.eventTypeId + ']').removeClass('icon-muted').addClass('icon-info');
            } else {
                $('svg[data-event-id=' + dataJson.eventTypeId + ']').removeClass('icon-info').addClass('icon-muted');
            }
            tnglitbox.remove();
        }
    });
    return false;
}

$('.event-more').on('click', function () {
    'use strict';
    var eventId = $(this).data('eventId'),
        persfamId = $(this).data('persfamId');

    tnglitbox = new ModalDialog('admin_editmore.php?eventID=' + eventId + '&persfamID=' + persfamId);
    return false;
});

var moreIcon = $('img.icon-more');
SVGInjector(moreIcon);
