
function sendLogin(form, url) {
    'use strict';
    var params = $(form).serialize();
    $.ajax({
        url: url,
        data: params,
        dataType: 'json',
        success: function (vars) {
            if ($('#' + vars.div).length) {
                $('#' + vars.div).html(vars.msg);
                $('#' + vars.div).effect('highlight', {}, 400);
            }
        }
    });
    return false;
}

$('div.form-login #resetpass').on('click', function () {
    'use strict';
    if (this.checked) {
        document.getElementById('resetrow').style.display = '';
    } else {
        document.getElementById('resetrow').style.display = 'none';
    }
});
