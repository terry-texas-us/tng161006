// [ts] global functions and/or variables for JSLint
/*global checkDate, closeBranchEdit, ModalDialog, newEvent, openFindPlaceForm, getTree, quitBranchEdit,
         removePrefixFromArray, showBranchEdit, showCitations, textSnippet */
var branchtimer, tnglitbox;

$('#addmedia-person').on('click', function () {
    'use strict';
    if (confirm(textSnippet('savefirst'))) {
        $('#newmedia').val(1);
        document.form1.submit();
    }
    // [ts] form reset needed ?
    return false;
});

$('#expandall-editperson').on('click', function () {
    'use strict';
//    $('#plus0').attr('src', 'img/tng_collapse.gif');
//    $('#names').fadeIn();
    $('#plus1').attr('src', 'img/tng_collapse.gif');
    $('#person-events').fadeIn();
    $('#plus2').attr('src', 'img/tng_collapse.gif');
    $('#parents').fadeIn();
    $('#plus3').attr('src', 'img/tng_collapse.gif');
    $('#spouses').fadeIn();
    return false;
});

$('#collapseall-editperson').on('click', function () {
    'use strict';
    $('#plus3').attr('src', 'img/tng_expand.gif');
    $('#spouses').fadeOut();
    $('#plus2').attr('src', 'img/tng_expand.gif');
    $('#parents').fadeOut();
    $('#plus1').attr('src', 'img/tng_expand.gif');
    $('#person-events').fadeOut();
//    $('#plus0').attr('src', 'img/tng_expand.gif');
//    $('#names').fadeOut();
});

$('#change-tree').on('click', function () {
    'use strict';
    var $personId = $(this).data('personId');
    var url = 'treesChange.php?entity=person&entityID=' + $personId;
    tnglitbox = new ModalDialog(url);
    return false;
});

$('#show-branchedit-person').on('click', function () {
    'use strict';
    showBranchEdit('branchedit-person');
    quitBranchEdit('branchedit-person');
    return false;
});

$('#pbranchedit').on('click', function () {
    'use strict';
    showBranchEdit('pbranchedit');
    quitBranchEdit('pbranchedit');
    return false;
});

$('#pbranchedit').on('mouseover', function () {
    'use strict';
    clearTimeout(branchtimer);
});

$('#pbranchedit').on('mouseout', function () {
    'use strict';
    closeBranchEdit('pbranch', 'pbranchedit', 'pbranchlist');
});

$('#branchedit-person').on('mouseover', function () {
    'use strict';
    clearTimeout(branchtimer);
});

$('#branchedit-person').on('mouseout', function () {
    'use strict';
    closeBranchEdit('branch', 'branchedit-person', 'branchlist');
});

$('#addnew-event-person').on('click', function () {
    'use strict';
    var $personId = $(this).data('personId');
    newEvent('I', $personId);
});

$('#parents .sortrow').on('mouseover', function () {
    'use strict';
    var $familyId = $(this).data('familyId');
    $('#unlinkp_' + $familyId).show();
});

$('#parents .sortrow').on('mouseout', function () {
    'use strict';
    var $familyId = $(this).data('familyId');
    $('#unlinkp_' + $familyId).hide();
});

$('#spouses .sortrow').on('mouseover', function () {
    'use strict';
    var $familyId = $(this).data('familyId');
    $('#unlinks_' + $familyId).show();
});

$('#spouses .sortrow').on('mouseout', function () {
    'use strict';
    var $familyId = $(this).data('familyId');
    $('#unlinks_' + $familyId).hide();
});

function checkPersonId(personIdInput, treeSelect) {
    'use strict';
    var tree = getTree(treeSelect);
    if (tree !== false) {
        var $personIdInput = $(personIdInput);

        $.ajax({
            url: '_/components/ajax/checkPersonId.php',
            data: {checkID: $personIdInput.val(), tree: tree},
            success: function (data) {
                var result = JSON.parse(data).result;
                personIdInput.dataset.checkResult = result;

                var parentElement = $personIdInput.parent();
                if (result === 'idok') {
                    parentElement.removeClass('has-warning').addClass('has-success');
                    $personIdInput.removeClass('form-control-warning').addClass('form-control-success');
                } else {
                    parentElement.removeClass('has-success').addClass('has-warning');
                    $personIdInput.removeClass('form-control-success').addClass('form-control-warning');
                    $personIdInput.attr('placeholder', $personIdInput.val() + ' ' + JSON.parse(data).message);
                    $personIdInput.val('');
                }
            },
            dataType: 'html'
        });
    }
}

function addNewFamily(radioval, args) {
    'use strict';
    if (confirm(textSnippet('savefirst'))) {
        $('#radio' + radioval).attr('checked', true);
        document.form1.submit();
    }
    return false;
}

$('#addnew-parents').on('click', function () {
    'use strict';
    var $personId = $(this).data('personId');
    var $cw = $(this).data('cw');
    return addNewFamily('child', 'child=' + $personId + '&cw=' + $cw);
});

$('#addnew-family-spouses').on('click', function () {
    'use strict';
    var $self = $(this).data('self');
    var $personId = $(this).data('personId');
    var $cw = $(this).data('cw');
    return addNewFamily($self, $self + '=' + $personId + '&cw=' + $cw);
});

$('#parent-sealdate').on('blur', function () {
    'use strict';
    checkDate(this);
});

$('#find-place-seal').on('click', function () {
    'use strict';
    var $familyId = $(this).data('familyId');
    return openFindPlaceForm('sealpplace' + $familyId, 1);
});

$('#parents .lds-seal-citations').on('click', function () {
    'use strict';
    var $personId = $(this).data('personId');
    var $familyId = $(this).data('familyId');
    return showCitations('SLGC', $personId + '::' + $familyId);
});

function startPersonSorts(tree, spouseOrder) {
    'use strict';
    if ($('div#parents div').length > 1) {
        $('#parents').sortable({
            helper: 'clone',
            axis: 'y',
            scroll: false,
            items: '.sortrow',
            update: function (event, ui) {
                var parentlist = removePrefixFromArray($('#parents').sortable('toArray'), 'parents_');

                var params = {
                    sequence: parentlist.join(','),
                    action: 'parentorder',
                    personID: document.form1.personID.value,
                    tree: tree
                };
                $.ajax({
                    url: 'ajx_updateorder.php',
                    data: params,
                    dataType: 'html'
                });
            },
            create: function () {
                $(this).height($(this).height());
            }
        });
    }
    if ($('div#spouses div').length > 1) {
        $('#spouses').sortable({
            helper: 'clone',
            axis: 'y',
            scroll: false,
            items: '.sortrow',
            update: function (event, ui) {
                var spouselist = removePrefixFromArray($('#spouses').sortable('toArray'), 'spouses_');

                var params = {
                    sequence: spouselist.join(','),
                    action: 'spouseorder',
                    tree: tree,
                    spouseorder: spouseOrder
                };
                $.ajax({
                    url: 'ajx_updateorder.php',
                    data: params,
                    dataType: 'html'
                });
            },
            create: function () {
                $(this).height($(this).height());
            }
        });
    }
}

function unlinkSpouse(familyID) {
    'use strict';
    if (confirm(textSnippet('confunlinkspouse'))) {
        var params = {
            action: 'spouseunlink',
            familyID: familyID,
            personID: document.form1.personID.value
        };
        $.ajax({
            url: 'ajx_updateorder.php',
            data: params,
            dataType: 'html',
            success: function (data) {
                $('#spouses_' + familyID).fadeOut(300, function () {
                    $('#spouses_' + familyID).remove();
                    $('#marrcount').html(parseInt($('#marrcount').html(), 10) - 1);
                });
            }
        });
    }
    return false;
}

$('#spouses #unlink-from-family').on('click', function () {
    'use strict';
    var $familyId = $(this).data('familyId');
    return unlinkSpouse($familyId);
});

function unlinkChildFamily(familyID) {
    'use strict';
    if (confirm(textSnippet('confunlinkchild'))) {
        var params = {
            action: 'parentunlink',
            familyID: familyID,
            personID: document.form1.personID.value,
        };
        $.ajax({
            url: 'ajx_updateorder.php',
            data: params,
            dataType: 'html',
            success: function (data) {
                $('#parents_' + familyID).fadeOut(300, function () {
                    $('#parents_' + familyID).remove();
                    $('#parentcount').html(parseInt($('#parentcount').html(), 10) - 1);
                });
            }
        });
    }
    return false;
}

$('#parents #unlink-from-family').on('click', function () {
    'use strict';
    var $familyId = $(this).data('familyId');

    return unlinkChildFamily($familyId);
});
