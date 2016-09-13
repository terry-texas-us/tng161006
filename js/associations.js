// [ts] global functions and variables for jsLint
/*global textSnippet, insertCell, getActionButtons, gotoSection, ModalDialog, SVGInjector */
var tnglitbox;

function addAssociation(form) {
    'use strict';
    if (form.passocID.value === '') {
        alert(textSnippet('enterassocpersonid'));
    } else if (form.relationship.value === '') {
        alert(textSnippet('enterrela'));
    } else {
        var params = $(form).serialize();
        $.ajax({
            url: 'admin_addassoc.php',
            data: params,
            dataType: 'json',
            success: function (vars) {
                var associationstbl = document.getElementById('associationstbl'),
                    newtr = associationstbl.insertRow(associationstbl.rows.length);
                newtr.id = "row_" + vars.id;
                insertCell(newtr, 0, '', getActionButtons(vars, 'Association'));
                insertCell(newtr, 1, '', vars.display);

                associationstbl.style.display = '';
                gotoSection('addassociation', 'associations');
                $('.icon-associations').removeClass('icon-muted');
                $('.icon-associations').addClass('icon-info');
            }
        });
    }
    return false;
}

function editAssociation(assocID) {
    'use strict';
    var params = {assocID: assocID};
    $.ajax({
        url: 'admin_editassoc.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            $('#editassociation').html(req);
            gotoSection('associations', 'editassociation');
        }
    });
    return false;
}

function updateAssociation(form) {
    'use strict';
    var assocID = form.assocID.value,
        params = $(form).serialize();
    $.ajax({
        url: 'admin_updateassoc.php',
        data: params,
        dataType: 'json',
        success: function (vars) {
            var tds = $('tr#row_' + assocID + ' td');
            tds.eq(1).html(vars.display);
            gotoSection('editassociation', 'associations');
            $.each(tds, function (index, item) {
                $(item).effect('highlight', {}, 2500);
            });
        }
    });
    return false;
}

function deleteAssociation(assocID, personID) {
    'use strict';
    if (confirm(textSnippet('confdeleteassoc'))) {
        var tds = $('tr#row_' + assocID + ' td'),
            params;
        $.each(tds, function (index, item) {
            $(item).effect('highlight', {color: '#ff9999'}, 200);
        });
        params = {assocID: assocID, personID: personID};
        $.ajax({
            url: 'admin_deleteassoc.php',
            data: params,
            dataType: 'html',
            success: function (req) {
                $('#row_' + assocID).fadeOut(200);
                if (req === '0') {
                    $('.icon-associations').removeClass('icon-info');
                    $('.icon-associations').addClass('icon-muted');
                }
            }
        });
    }
    return false;
}

$('#person-associations').on('click', function () {
    'use strict';
    var $personId = $(this).data('personId');
    tnglitbox = new ModalDialog('admin_associations.php?orgreltype=I&personID=' + $personId);
});

$('#family-associations').on('click', function () {
    'use strict';
    var $familyId = $(this).data('familyId');
    tnglitbox = new ModalDialog('admin_associations.php?orgreltype=I&personID=' + $familyId);
});

var associationIcon = $('img.icon-associations');
SVGInjector(associationIcon);
