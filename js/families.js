// [ts] global functions and/or variables for JSLint
/*global deepOpen, findItem */
var activeidbox, activenamebox, tnglitbox;

$('#find-husband').on('click', function () {
    'use strict';
    var $assignedBranch = $(this).data('assignedBranch');
    return findItem('I', 'husband', 'husbnameplusid', $assignedBranch);
});

$('#create-husband').on('click', function () {
    'use strict';
    activeidbox = 'husband';
    activenamebox = 'husbnameplusid';
    var url = 'peopleAdd2.modal.php?type=spouse' + '&familyID=' + document.form1.familyID.value + '&father=&gender=M';
    tnglitbox = new ModalDialog(url);
//      generateID('person', document.npform.personID);
    $('#firstname').focus();
    return false;
});

$('#edit-husband').on('click', function () {
    'use strict';
    var field = document.form1.husband;
    if (field.value.length) {
        deepOpen('peopleEdit.php?personID=' + field.value + '&cw=1', 'editspouse');
    }
});

$('#remove-husband').on('click', function () {
    'use strict';
    var spouse = document.form1.husband,
        spousedisplay = document.form1.husbnameplusid;
    spouse.value = '';
    spousedisplay.value = '';
});

$('#find-wife').on('click', function () {
    'use strict';
    var $assignedBranch = $(this).data('assignedBranch');
    return findItem('I', 'wife', 'wifenameplusid', $assignedBranch);
});

$('#create-wife').on('click', function () {
    'use strict';
    activeidbox = 'wife';
    activenamebox = 'wifenameplusid';
    var url = 'peopleAdd2.modal.php?type=spouse' + '&familyID=' + document.form1.familyID.value + '&father=&gender=F';
    tnglitbox = new ModalDialog(url);
//      generateID('person', document.npform.personID);
    $('#firstname').focus();
    return false;
});

$('#edit-wife').on('click', function () {
    'use strict';
    var field = document.form1.wife;
    if (field.value.length) {
        deepOpen('peopleEdit.php?personID=' + field.value + '&cw=1', 'editspouse');
    }
});

$('#remove-wife').on('click', function () {
    'use strict';
    var spouse = document.form1.wife,
        spousedisplay = document.form1.wifenameplusid;
    spouse.value = '';
    spousedisplay.value = '';
});

$('#childrenlist .sortrow').on('mouseover', function () {
    'use strict';
    if ($(this).data('allowDelete')) {
        var $childId = $(this).data('childId');
        $('#unlinkc_' + $childId).css('visibility', 'visible');
    }
});

$('#childrenlist .sortrow').on('mouseout', function () {
    'use strict';
    if ($(this).data('allowDelete')) {
        var $childId = $(this).data('childId');
        $('#unlinkc_' + $childId).css('visibility', 'hidden');
    }
});

$('#remove-child').on('click', function () {
    'use strict';
    var element = document.getElementById('remove-child');
    return unlinkChild(element.dataset.childId, 'child_unlink');
});

$('#delete-child').on('click', function () {
    'use strict';
    var element = document.getElementById('delete-child');
    return unlinkChild(element.dataset.childId, 'child_delete');
});

$('#edit-child').on('click', function () {
    'use strict';
    var $childId = $(this).data('childId');
    deepOpen('peopleEdit.php?personID=' + $childId + '&cw=1', 'editchild');
});

$('#find-children').on('click', function () {
    'use strict';
    var $assignedBranch = $(this).data('assignedBranch');
    return findItem('I', 'children', null, $assignedBranch);
});

$('#create-child').on('click', function () {
    'use strict';
    activeidbox = '';
    activenamebox = '';
    var url = 'peopleAdd2.modal.php?type=child' + '&familyID=' + document.form1.familyID.value + '&father=' + document.form1.husband.value + '&gender=';
    tnglitbox = new ModalDialog(url);
//      generateID('person', document.npform.personID);
    $('#firstname').focus();
    return false;
});
