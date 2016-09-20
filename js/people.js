// [ts] global functions and/or variables for JSLint
/*global checkDate, closeBranchEdit, newEvent, openFindPlaceForm, quitBranchEdit,
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

function checkPersonId(personIdInput) {
    'use strict';
    var $personIdInput = $(personIdInput);

    $.ajax({
        url: '_/components/ajax/checkPersonId.php',
        data: {checkID: $personIdInput.val()},
        success: function (data) {
            var result = JSON.parse(data).result,
                parentElement = $personIdInput.parent();

            personIdInput.dataset.checkResult = result;

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
    var $personId = $(this).data('personId'),
        $cw = $(this).data('cw');
    return addNewFamily('child', 'child=' + $personId + '&cw=' + $cw);
});

$('#addnew-family-spouses').on('click', function () {
    'use strict';
    var $self = $(this).data('self'),
        $personId = $(this).data('personId'),
        $cw = $(this).data('cw');
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
    var $personId = $(this).data('personId'),
        $familyId = $(this).data('familyId');
    return showCitations('SLGC', $personId + '::' + $familyId);
});

function startPersonSorts(spouseOrder) {
    'use strict';
    if ($('div#parents div').length > 1) {
        $('#parents').sortable({
            helper: 'clone',
            axis: 'y',
            scroll: false,
            items: '.sortrow',
            update: function (event, ui) {
                var parentlist = removePrefixFromArray($('#parents').sortable('toArray'), 'parents_'),
                    params = {
                        sequence: parentlist.join(','),
                        action: 'parentorder',
                        personID: document.form1.personID.value
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
                var spouselist = removePrefixFromArray($('#spouses').sortable('toArray'), 'spouses_'),
                    params = {
                        sequence: spouselist.join(','),
                        action: 'spouseorder',
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
            personID: document.form1.personID.value
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
