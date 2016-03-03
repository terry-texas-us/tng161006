// [ts] global functions and variables for jsLint
/*global checkEmail, textSnippet */
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

function toggleRights(status) {
    'use strict';
    $('.rights').each(function (index, item) {
        item.disabled = status;
    });
}

function assignRightsFromRole(role) {
    'use strict';
    var form = document.form1;

    switch (role) {
    case "guest":
        //toggleRights('disabled');
        form.form_allow_add[2].checked = "checked";
        form.form_allow_edit[3].checked = "checked";
        form.form_allow_delete[2].checked = "checked";
        break;
    case "subm":
        //toggleRights('disabled');
        form.form_allow_add[2].checked = "checked";
        form.form_allow_edit[2].checked = "checked";
        form.form_allow_delete[2].checked = "checked";
        break;
    case "contrib":
        //toggleRights('disabled');
        form.form_allow_add[0].checked = "checked";
        form.form_allow_edit[3].checked = "checked";
        form.form_allow_delete[2].checked = "checked";
        break;
    case "mcontrib":
        //toggleRights('disabled');
        form.form_allow_add[1].checked = "checked";
        form.form_allow_edit[3].checked = "checked";
        form.form_allow_delete[2].checked = "checked";
        break;
    case "editor":
        //toggleRights('disabled');
        form.form_allow_add[0].checked = "checked";
        form.form_allow_edit[0].checked = "checked";
        form.form_allow_delete[0].checked = "checked";
        form.administrator[1].checked = "checked";
        grantAdmin('visible');
        break;
    case "meditor":
        //toggleRights('disabled');
        form.form_allow_add[1].checked = "checked";
        form.form_allow_edit[1].checked = "checked";
        form.form_allow_delete[1].checked = "checked";
        break;
    case "custom":
        toggleRights('');
        break;
    case "admin":
        //toggleRights('disabled');
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