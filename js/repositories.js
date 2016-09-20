// [ts] global functions and/or variables for JSLint
/*global */

$('#change-tree').on('click', function () {
    'use strict';
    var $personId = $(this).data('personId');
    var url = 'treesChange.modal.php?entity=person&entityID=' + $personId;
    tnglitbox = new ModalDialog(url);
    return false;
});

