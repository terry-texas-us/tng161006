// [ts] global functions and/or variables for JSLint
/*global attemptDelete, ModalDialog, textSnippet */

var tnglitbox;

$('#addnewstate').on('click', function () {
    'use strict';
    var url = 'admin_newentity.php?entity=state';
    tnglitbox = new ModalDialog(url, {size: 'modal-sm'});
    $('newitem').focus();
});

$('#deletestate').on('click', function () {
    'use strict';
    attemptDelete(document.form1.state, 'state');
    $('newitem').focus();
});

$('#addnewcountry').on('click', function () {
    'use strict';
    var url = 'admin_newentity.php?entity=country';
    tnglitbox = new ModalDialog(url, {size: 'modal-sm'});
    $('newitem').focus();
});

$('#deletecountry').on('click', function () {
    'use strict';
    attemptDelete(document.form1.country, 'country');
});

function validateForm() {
    'use strict';
    var rval = true;
    if (document.form1.country.value.length === 0) {
        alert(textSnippet('entercountry'));
        rval = false;
    } else if (document.form1.newfile.value.length > 0 && document.form1.maplink.value.length === 0) {
        alert(textSnippet('entermapfile'));
        rval = false;
    } else {
        document.form1.maplink.value = document.form1.maplink.value.replace(/\\/g, "/");
    }
    return rval;
}

function populatePath(source, dest) {
    'use strict';
    var lastslash;
    var temp;

    dest.value = "";
    temp = source.value.replace(/\\/g, "/");
    lastslash = temp.lastIndexOf("/") + 1;
    if (lastslash) {
        dest.value = source.value.slice(lastslash);
    }
}

function fillPlace(form) {
    'use strict';
    var place = form.cemname.value;

    if (place && form.city.value) {
        place += ", ";
    }
    place += form.city.value;

    if (place && form.county.value) {
        place += ", ";
    }
    place += form.county.value;

    if (place && form.state.options[form.state.selectedIndex].value) {
        place += ", ";
    }
    place += form.state.options[form.state.selectedIndex].value;

    if (place && form.country.selectedIndex > 0) {
        place += ", ";
    }
    place += form.country.options[form.country.selectedIndex].value;

    $('#place').val(place);
    $('#location').val(place);
    $('#place').effect('highlight', {}, 120);
}

$('#fillplace').on('click', function () {
    'use strict';
    fillPlace(document.form1);
});
