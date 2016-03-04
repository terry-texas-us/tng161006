// [ts] global functions and variables for jsLint
/*global  checkID, generateID, textSnippet */
var tnglitbox;

function alphaNumericCheck(string) {
    'use strict';
    var regex = /^[0-9A-Za-z_\-]+$/;
    return regex.test(string);
}

$('#trees-search-reset').on('click', function () {
    'use strict';
    document.form1.searchstring.value = '';
});

$('#trees-add').on('submit', function () {
    'use strict';
    var form = document.treeform;
    var rval = true;
    if (form.gedcom.value.length === 0) {
        alert(textSnippet('entertreeid'));
        rval = false;
    } else if (!alphaNumericCheck(form.gedcom.value)) {
        alert(textSnippet('alphanum'));
        rval = false;
    } else if (form.treename.value.length === 0) {
        alert(textSnippet('entertreename'));
        rval = false;
    }
    return rval;
});

$('#treeschange #newtree').on('change', function () {
    'use strict';
    var entity = $(this).data('entity');
    if (document.treeschange.newtree.selectedIndex > 0) {
        generateID(entity, document.treeschange.newID, document.treeschange.newtree);
    }
});

$('#treeschange #newID').on('blur', function () {
    'use strict';
    this.value = this.value.toUpperCase();
});

$('#treeschange #generate').on('click', function () {
    'use strict';
    if (document.treeschange.newtree.selectedIndex > 0) {
        generateID('person', document.treeschange.newID, document.treeschange.newtree);
    }
});

$('#treeschange #check').on('click', function () {
    'use strict';
    if (document.treeschange.newtree.selectedIndex > 0) {
        checkID(document.treeschange.newID.value, 'person', 'checkmsg', document.treeschange.newtree);
    }
});

$('#treeschange button[name="cancel"]').on('click', function () {
    'use strict';
    tnglitbox.remove();
});

$('#treeschange').on('submit', function () {
    'use strict';
//    tnglitbox.remove();
    return document.treeschange.newtree.selectedIndex >= 1;
});