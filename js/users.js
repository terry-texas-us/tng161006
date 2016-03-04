// [ts] global functions and variables for jsLint
/*global checkEmail, deleteIt, findItem, textSnippet */
var orgrealname, orgusername, orgpassword;

function grantAdmin(status) {
    'use strict';
    var ref = $('#restrictions');
    if (status === 'hidden') {
        document.form1.gedcom.selectedIndex = 0;
        document.form1.branch.selectedIndex = 0;
    }
    if (ref.length) {
        ref.css('visibility', status);
    }
}

function assignRightsFromRole(role) {
    'use strict';
    var form = document.form1;

    switch (role) {
    case "guest":
        form.form_allow_add[2].checked = "checked";
        form.form_allow_edit[3].checked = "checked";
        form.form_allow_delete[2].checked = "checked";
        break;
    case "subm":
        form.form_allow_add[2].checked = "checked";
        form.form_allow_edit[2].checked = "checked";
        form.form_allow_delete[2].checked = "checked";
        break;
    case "contrib":
        form.form_allow_add[0].checked = "checked";
        form.form_allow_edit[3].checked = "checked";
        form.form_allow_delete[2].checked = "checked";
        break;
    case "mcontrib":
        form.form_allow_add[1].checked = "checked";
        form.form_allow_edit[3].checked = "checked";
        form.form_allow_delete[2].checked = "checked";
        break;
    case "editor":
        form.form_allow_add[0].checked = "checked";
        form.form_allow_edit[0].checked = "checked";
        form.form_allow_delete[0].checked = "checked";
        form.administrator[1].checked = "checked";
        grantAdmin('visible');
        break;
    case "meditor":
        form.form_allow_add[1].checked = "checked";
        form.form_allow_edit[1].checked = "checked";
        form.form_allow_delete[1].checked = "checked";
        break;
    case "custom":
        $('.rights').each(function (index, item) {
          item.disabled = '';
        });
        break;
    case "admin":
        form.form_allow_add[0].checked = "checked";
        form.form_allow_edit[0].checked = "checked";
        form.form_allow_delete[0].checked = "checked";
        form.administrator[0].checked = "checked";
        form.form_allow_living.checked = "checked";
        form.form_allow_private.checked = "checked";
        form.form_allow_ged.checked = "checked";
        form.form_allow_pdf.checked = "checked";
        form.form_allow_lds.checked = "checked";
        form.form_allow_profile.checked = "checked";
        grantAdmin('hidden');
        break;
    }
}

function handleAdmin(option) {
    'use strict';
    if (option === "allow") {
        if (document.form1.role[4].checked) {   //editor
            document.form1.role[7].checked = "checked";
            assignRightsFromRole('admin');
        } else {
            grantAdmin('hidden');
        }
    } else {
        if (document.form1.role[7].checked) {   //admin
            document.form1.role[4].checked = "checked";
            assignRightsFromRole('editor');
        } else {
            grantAdmin('visible');
        }
    }
}

function replaceText() {
    'use strict';
    var form = document.form1;

    if (document.form1.notify.checked) {
        var welcome = document.form1.welcome;
        var realname = new RegExp(orgrealname);
        var username = new RegExp(orgusername);
        var password = new RegExp(orgpassword);

        orgrealname = form.realname.value;
        orgusername = form.username.value;
        orgpassword = form.password.value;

        welcome.value = welcome.value.replace(realname, orgrealname);
        welcome.value = welcome.value.replace(username, orgusername);
        welcome.value = welcome.value.replace(password, orgpassword);
        form.welcome.style.display = '';
    } else {
        form.welcome.style.display = 'none';
    }
}

function checkIfUnique(emailfield) {
    'use strict';
    if (emailfield.value) {
        if (checkEmail(emailfield.value)) {
            var params = {checkemail: emailfield.value};
            $.ajax({
                url: 'admin_checkemail.php',
                data: params,
                type: 'POST',
                dataType: 'json',
                success: function (vars) {
                    $('#emailmsg').attr('class', vars.result).html(vars.message);
                }
            });
        } else {
            $('#emailmsg').attr('class', 'msgerror').html(textSnippet('enteremail'));
        }
    }
}

var newuserok = false;
function checkNewUser(userfield, olduserfield, submitform) {
    'use strict';
    if (olduserfield && userfield.value === olduserfield.value) {
        newuserok = true;
        return true;
    }
    $.ajax({
        url: 'usersValidateUsernameJSON.php',
        data: {checkuser: userfield.value},
        dataType: 'json',
        success: function (vars) {
            newuserok = vars.rval;
            if (newuserok) {
                if (submitform) {
                    document.editprofile.submit();
                } else {
                    $('#checkmsg').removeClass('msgerror').addClass('msgapproved').html(vars.html);
                }
            } else {
                $('#checkmsg').removeClass('msgapproved').addClass('msgerror').html(vars.html);
            }
        }
    });
    return false;
}

$('#users-add input[name="username"]').on('blur', function () {
    'use strict';
    checkNewUser(this, null);
});

$('#users-add input[name="email"]').on('blur', function () {
    'use strict';
    checkIfUnique(this);
});

$('#users-add #findPerson').on('click', function () {
    'use strict';
    var assignedBranch = $(this).data('assignedBranch');
    return findItem('I', 'personID', '', document.form1.mynewgedcom.options[document.form1.mynewgedcom.selectedIndex].value, assignedBranch);
});

$('#users-add input[name="role"]').on('click', function () {
    'use strict';
    var role = $(this).data('role');
    assignRightsFromRole(role);
});

$('#users-add input[name="form_allow_add"]').on('change', function () {
    'use strict';
    document.form1.role[6].checked = 'checked';
});

$('#users-add input[name="form_allow_edit"]').on('change', function () {
    'use strict';
    document.form1.role[6].checked = 'checked';
});

$('#users-add input[name="form_allow_delete"]').on('change', function () {
    'use strict';
    document.form1.role[6].checked = 'checked';
});

$('#users-add input[name="administrator"]').on('change', function () {
    'use strict';
    var adminAccess = $(this).data('adminAccess');
    handleAdmin(adminAccess);
});

$('#users-add input[name="notify"]').on('click', function () {
    'use strict';
    replaceText();
});

$('#users-add').on('submit', function () {
    'use strict';
    var form = document.form1;
    var rval = true;
    if (form.description.value.length === 0) {
        alert(textSnippet('enteruserdesc'));
        form.description.focus();
        rval = false;
    } else if (form.username.value.length === 0) {
        alert(textSnippet('enterusername'));
        form.username.focus();
        rval = false;
    } else if (form.password.value.length === 0) {
        alert(textSnippet('enterpassword'));
        form.password.focus();
        rval = false;
    } else if (form.email.value.length !== 0 && !checkEmail(form.email.value)) {
        alert(textSnippet('enteremail'));
        form.email.focus();
        rval = false;
    } else if (form.administrator[1].checked && form.gedcom.selectedIndex < 1) {
        alert(textSnippet('selecttree'));
        form.gedcom.focus();
        rval = false;
    }
    return rval;
});

$('#users-edit input[name="username"]').on('change', function () {
    'use strict';
    checkNewUser(document.form1.username, document.form1.orguser);
});

$('#users-edit input[name="email"]').on('blur', function () {
    'use strict';
    checkIfUnique(this);
});

$('#users-edit #findPerson').on('click', function () {
    'use strict';
    var assignedBranch = $(this).data('assignedBranch');
    return findItem('I', 'personID', '', document.form1.mynewgedcom.options[document.form1.mynewgedcom.selectedIndex].value, assignedBranch);
});

$('#users-edit input[name="role"]').on('click', function () {
    'use strict';
    var role = $(this).data('role');
    assignRightsFromRole(role);
});

$('#users-edit input[name="form_allow_add"]').on('change', function () {
    'use strict';
    document.form1.role[6].checked = 'checked';
});

$('#users-edit input[name="form_allow_edit"]').on('change', function () {
    'use strict';
    document.form1.role[6].checked = 'checked';
});

$('#users-edit input[name="form_allow_delete"]').on('change', function () {
    'use strict';
    document.form1.role[6].checked = 'checked';
});

$('#users-edit input[name="administrator"]').on('change', function () {
    'use strict';
    var adminAccess = $(this).data('adminAccess');
    handleAdmin(adminAccess);
});

$('#users-edit').on('submit', function () {
    'use strict';
    var form = document.form1;
    var rval = true;
    if (document.form1.username.value.length === 0) {
        alert(textSnippet('enterusername'));
        document.form1.username.focus();
        rval = false;
    } else if (document.form1.password.value.length === 0) {
        alert(textSnippet('enterpassword'));
        document.form1.password.focus();
        rval = false;
    } else if (form.email.value.length !== 0 && !checkEmail(form.email.value)) {
        alert(textSnippet('enteremail'));
        form.email.focus();
        rval = false;
    } else if (document.form1.administrator[1].checked && document.form1.gedcom.selectedIndex < 1) {
        alert(textSnippet('selecttree'));
        document.form1.gedcom.focus();
        rval = false;
    }
    return rval;
});

// users-browse

$('#users-search-reset').on('click', function () {
    'use strict';
    document.form1.searchstring.value = '';
    document.form1.adminonly.checked = false;
});

$('#users-browse button[name="selectall"]').on('click', function () {
    'use strict';
    var i;
    for (i = 0; i < document.form2.elements.length; i += 1) {
        if (document.form2.elements[i].type === 'checkbox') {
            document.form2.elements[i].checked = true;
        }
    }
});

$('#users-browse button[name="clearall"]').on('click', function () {
    'use strict';
    var i;
    for (i = 0; i < document.form2.elements.length; i += 1) {
        if (document.form2.elements[i].type === 'checkbox') {
            document.form2.elements[i].checked = false;
        }
    }
});

$('#users-browse button[name="xuseraction"]').on('click', function () {
    'use strict';
    return confirm(textSnippet('confdeleterecs'));
});

$('#users-send-mail').on('submit', function () {
    'use strict';
    var rval = true;
    if (document.form1.subject.value.length === 0) {
        alert(textSnippet('entersubject'));
        rval = false;
    } else if (document.form1.messagetext.value.length === 0) {
        alert(textSnippet('entermsgtext'));
        rval = false;
    }
    return rval;
});

// users-review

$('#users-review button[name="selectall"]').on('click', function () {
    'use strict';
    var i;
    for (i = 0; i < document.form2.elements.length; i += 1) {
        if (document.form2.elements[i].type === 'checkbox') {
            document.form2.elements[i].checked = true;
        }
    }
});

$('#users-review button[name="clearall"]').on('click', function () {
    'use strict';
    var i;
    for (i = 0; i < document.form2.elements.length; i += 1) {
        if (document.form2.elements[i].type === 'checkbox') {
            document.form2.elements[i].checked = false;
        }
    }
});

$('#users-review button[name="xuseraction"]').on('click', function () {
    'use strict';
    return confirm(textSnippet('confdeleterecs'));
});

$('#users-review #delete').on('click', function () {
    'use strict';
    var userId = $(this).data('userId');
    if (confirm(textSnippet('confuserdelete'))) {
        deleteIt('user', userId);
    }
    return false;
});

