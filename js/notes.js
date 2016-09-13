// [ts] global functions and variables for jsLint
/*global getActionButtons, gotoSection, insertCell, ModalDialog, removePrefixFromArray, SVGInjector, textSnippet */
var tnglitbox;

function updateNoteOrder(event, ui) {
    'use strict';
    var notelist = removePrefixFromArray($('#notes').sortable('toArray'), 'notes_'),
        params = {sequence: notelist.join(','), action: 'noteorder'};
    $.ajax({
        url: 'ajx_updateorder.php',
        data: params,
        dataType: 'html'
    });
}

function initNoteSort() {
    'use strict';
    $('#notes').sortable({tag: 'div', update: updateNoteOrder});
}

function showNotes(eventID, persfamID) {
    'use strict';
    tnglitbox = new ModalDialog('admin_notes.php?eventID=' + eventID + '&persfamID=' + persfamID, {doneLoading: initNoteSort});
    return false;
}

function addNote(form) {
    'use strict';
    if (form.note.value.length === 0) {
        alert(textSnippet('enternote'));
    } else {
        var params = $(form).serialize();
        $.ajax({
            url: 'admin_addnote.php',
            data: params,
            type: 'POST',
            dataType: 'json',
            success: function (vars) {
                vars.allow_cite = 1;

                var div = $('<div id="notes_' + vars.id + '" class="sortrow"></div>'),
                    newnotetbl = document.createElement("table"),
                    newtr;
                newtr = newnotetbl.insertRow(0);
                newtr.id = "row_" + vars.id;
                insertCell(newtr, 0, "dragarea", '<img src="img/admArrowUp.gif" alt=""><br><img src="img/admArrowDown.gif" alt="">');
                insertCell(newtr, 1, '', getActionButtons(vars, 'Note'));
                insertCell(newtr, 2, '', vars.display);
                div.append(newnotetbl);
                $('#notes').append(div);
                initNoteSort();

                $('#notestbl').show();
                gotoSection('addnote', 'notelist');

                $('svg[data-event-id=' + form.eventID.value + ']').removeClass('icon-muted').addClass('icon-info');
            }
        });
    }
    return false;
}

function deleteNote(noteID, personID, eventID) {
    'use strict';
    if (confirm(textSnippet('confdeletenote'))) {
        var tds = $('tr#row_' + noteID + ' td');
        tds.each(function (index, item) {
            $(item).effect('highlight', {color: '#ff9999'}, 100);
        });
        $.ajax({
            url: 'admin_deletenote.php',
            data: {noteID: noteID, personID: personID, eventID: eventID},
            dataType: 'html',
            success: function (req) {
                $('#row_' + noteID).fadeOut(200);
                if (req === '0') {
                    $('svg[data-event-id=' + eventID + ']').removeClass('icon-info').addClass('icon-muted');
                }
            }
        });
    }
    return false;
}

function editNote(noteID) {
    'use strict';
    var params = {noteID: noteID};
    $.ajax({
        url: 'admin_editnote.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            $('#editnote').html(req);
            gotoSection('notelist', 'editnote');
        }
    });
    return false;
}

function updateNote(form) {
    'use strict';
    if (form.note.value.length === 0) {
        alert(textSnippet('enternote'));
    } else {
        var noteID = form.ID.value,
            params = $(form).serialize();
        $.ajax({
            url: 'admin_updatenote.php',
            data: params,
            type: 'POST',
            dataType: 'json',
            success: function (vars) {
                var tds = $('tr#row_' + noteID + ' td');
                tds.eq(2).html(vars.display);
                gotoSection('editnote', 'notelist');
                tds.each(function (index, item) {
                    $(item).effect('highlight', {}, 2500);
                });
            }
        });
    }
    return false;
}

$('#person-notes').on('click', function () {
    'use strict';
    var $personId = $(this).data('personId');
    return showNotes('', $personId);
});

$('#person-notes-name').on('click', function () {
    'use strict';
    var $personId = $(this).data('personId');
    return showNotes('NAME', $personId);
});

$('#family-notes').on('click', function () {
    'use strict';
    var $familyId = $(this).data('familyId');
    return showNotes('', $familyId);
});

$('#repository-notes').on('click', function () {
    'use strict';
    var $repositoryId = $(this).data('repositoryId');
    return showNotes('', $repositoryId);
});

$('#sources-notes').on('click', function () {
    'use strict';
    var $sourceId = $(this).data('sourceId');
    return showNotes('', $sourceId);
});

$('.event-notes').on('click', function () {
    'use strict';
    var $eventId = $(this).data('eventId'),
        $persfamId = $(this).data('persfamId');
    return showNotes($eventId, $persfamId);
});

var noteIcon = $('img.icon-notes');
SVGInjector(noteIcon);
