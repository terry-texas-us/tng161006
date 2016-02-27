function grantAdmin(status) {
    var ref = jQuery('#restrictions');
    if (status == 'hidden') {
        document.form1.gedcom.selectedIndex = 0;
        document.form1.branch.selectedIndex = 0;
    }
    if (ref.length) {
        ref.css('visibility', status);
    }
}

function handleAdmin(option) {
    if (option == "allow") {
        if (document.form1.role[4].checked) {   //editor
            document.form1.role[7].checked = "checked";
            assignRightsFromRole('admin');
        } else
            grantAdmin('hidden');
    } else {
        if (document.form1.role[7].checked) {   //admin
            document.form1.role[4].checked = "checked";
            assignRightsFromRole('editor');
        } else
            grantAdmin('visible');
    }
}

function replaceText() {
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
    } else
        form.welcome.style.display = 'none';
}

function toggleRights(status) {
    jQuery('.rights').each(function (index, item) {
        item.disabled = status;
    });
}

function assignRightsFromRole(role) {
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

function checkIfUnique(emailfield) {
    if (emailfield.value) {
        if (checkEmail(emailfield.value)) {
            var params = {checkemail: emailfield.value};
            jQuery.ajax({
                url: 'admin_checkemail.php',
                data: params,
                type: 'POST',
                dataType: 'json',
                success: function (vars) {
                    jQuery('#emailmsg').attr('class', vars.result).html(vars.message);
                }
            });
        } else
            jQuery('#emailmsg').attr('class', 'msgerror').html(textSnippet('enteremail'));
    }
}