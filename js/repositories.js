// [ts] global functions and/or variables for JSLint
/*global */

$('#change-tree').on('click', function () {
    'use strict';
    var $personId = $(this).data('personId');
    var $tree = $(this).data('tree');
    var url = 'treesChange.php?entity=person&oldtree=' + $tree + '&entityID=' + $personId;
    tnglitbox = new ModalDialog(url);
    return false;
});

