function swapBranches(branchValues, branchText, branchSelectControl) {
    'use strict';
    var tree = $('#gedcom').val();
    var len = 0;
    branchSelectControl.options.length = 0;
    if ($('#branchlist').length) {
        $('#branchlist').html('');
    }
    var newOptionElement;
    var i;
    for (i = 0; i < branchValues[tree].length; i += 1) {
        newOptionElement = document.createElement('option');
        len = len + 1;
        newOptionElement.text = branchText[tree][i];
        newOptionElement.value = branchValues[tree][i];
        if (!i) {
            newOptionElement.selected = true;
        }
        branchSelectControl.options.add(newOptionElement);
    }
}
