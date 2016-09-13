// [ts] global functions and variables for jsLint
/*global getActionButtons, gotoSection, initCitationSort, insertCell, showCitations, SVGInjector, textSnippet */

function addCitation(form) {
    'use strict';
    if (form.sourceID.value === '') {
        alert(textSnippet('selectsource'));
    } else {
        var params = $(form).serialize();
        $.ajax({
            url: 'admin_addcitation.php',
            data: params,
            type: 'POST',
            dataType: 'json',
            success: function (vars) {
                var div = $('<div id="citations_' + vars.id + '" class="sortrow"></div>'),
                    newcitetbl = document.createElement("table"),
                    newtr;

                newtr = newcitetbl.insertRow(0);
                newtr.id = "row_" + vars.id;
                insertCell(newtr, 0, "dragarea", '<img src="img/admArrowUp.gif" alt=""><br><img src="img/admArrowDown.gif" alt="">');
                insertCell(newtr, 1, '', getActionButtons(vars, 'Citation'));
                insertCell(newtr, 2, '', vars.display);
                div.append(newcitetbl);
                $('#cites').append(div);
                initCitationSort();

                $('#citationstbl').show();
                gotoSection('addcitation', 'citations');

                $('svg[data-event-id=' + form.eventID.value + ']').removeClass('icon-muted').addClass('icon-info');
            }
        });
    }
    return false;
}

function deleteCitation(citationID, personID, eventID) {
    'use strict';
    if (confirm(textSnippet('confdeletecite'))) {
        var tds = $('tr#row_' + citationID + ' td');
        $.each(tds, function (index, item) {
            $(item).effect('highlight', {color: '#ff9999'}, 2500);
        });
        $.ajax({
            url: 'admin_deletecitation.php',
            data: {citationID: citationID, personID: personID, eventID: eventID},
            dataType: 'html',
            success: function (req) {
                $('#row_' + citationID).fadeOut(200);
                if (req === '0') {
                    $('svg[data-event-id=' + eventID + ']').removeClass('icon-info').addClass('icon-muted');
                }
            }
        });
    }
    return false;
}

$('#person-citations').on('click', function () {
    'use strict';
    var $personId = $(this).data('personId');
    return showCitations('', $personId);
});

$('#person-citations-name').on('click', function () {
    'use strict';
    var $personId = $(this).data('personId');
    return showCitations('NAME', $personId);
});

$('#family-citations').on('click', function () {
    'use strict';
    var $familyId = $(this).data('familyId');
    return showCitations('', $familyId);
});

$('.event-citations').on('click', function () {
    'use strict';
    var $eventId = $(this).data('eventId'),
        $persfamId = $(this).data('persfamId');
    return showCitations($eventId, $persfamId);
});

var citationIcon = $('img.icon-citations');
SVGInjector(citationIcon);
