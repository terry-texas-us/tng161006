// [ts] global functions and/or variables for JSLint
/*global deepOpen, findItem, ModalDialog */
var activeidbox, activenamebox, tnglitbox;

$('#find-husband').on('click', function () {
    'use strict';
    var $tree = $(this).data('tree');
    var $assignedBranch = $(this).data('assignedBranch');
    return findItem('I', 'husband', 'husbnameplusid', $tree, $assignedBranch);
});

$('#create-husband').on('click', function () {
    'use strict';
    activeidbox = 'husband';
    activenamebox = 'husbnameplusid';
    var url = 'admin_newperson2.php?tree=' + document.form1.tree.value + '&type=spouse' + '&familyID=' + document.form1.familyID.value + '&father=&gender=M';
    tnglitbox = new ModalDialog(url);
//      generateID('person', document.npform.personID);
    $('#firstname').focus();
    return false;
});

$('#edit-husband').on('click', function () {
    'use strict';
    var field = document.form1.husband;
    if (field.value.length) {
        deepOpen('peopleEdit.php?personID=' + field.value + '&tree=' + document.form1.tree.value + '&cw=1', 'editspouse');
    }
});

$('#remove-husband').on('click', function () {
    'use strict';
    var spouse = document.form1.husband;
    var spousedisplay = document.form1.husbnameplusid;
    spouse.value = "";
    spousedisplay.value = "";
});

$('#find-wife').on('click', function () {
    'use strict';
    var $tree = $(this).data('tree');
    var $assignedBranch = $(this).data('assignedBranch');
    return findItem('I', 'wife', 'wifenameplusid', $tree, $assignedBranch);
});

$('#create-wife').on('click', function () {
    'use strict';
    activeidbox = 'wife';
    activenamebox = 'wifenameplusid';
    var url = 'admin_newperson2.php?tree=' + document.form1.tree.value + '&type=spouse' + '&familyID=' + document.form1.familyID.value + '&father=&gender=F';
    tnglitbox = new ModalDialog(url);
//      generateID('person', document.npform.personID);
    $('#firstname').focus();
    return false;
});

$('#edit-wife').on('click', function () {
    'use strict';
    var field = document.form1.wife;
    if (field.value.length) {
        deepOpen('peopleEdit.php?personID=' + field.value + '&tree=' + document.form1.tree.value + '&cw=1', 'editspouse');
    }
});

$('#remove-wife').on('click', function () {
    'use strict';
    var spouse = document.form1.wife;
    var spousedisplay = document.form1.wifenameplusid;
    spouse.value = "";
    spousedisplay.value = "";
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
    deepOpen('peopleEdit.php?personID=' + $childId + '&tree=' + document.form1.tree.value + '&cw=1', 'editchild');
});

$('#find-children').on('click', function () {
    'use strict';
    var $tree = $(this).data('tree');
    var $assignedBranch = $(this).data('assignedBranch');
    return findItem('I', 'children', null, $tree, $assignedBranch);
});

$('#create-child').on('click', function () {
    'use strict';
    activeidbox = '';
    activenamebox = '';
    var url = 'admin_newperson2.php?tree=' + document.form1.tree.value + '&type=child' + '&familyID=' + document.form1.familyID.value + '&father=' + document.form1.husband.value + '&gender=';
    tnglitbox = new ModalDialog(url);
//      generateID('person', document.npform.personID);
    $('#firstname').focus();
    return false;
});
