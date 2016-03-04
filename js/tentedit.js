function saveTentEdit(form) {
    $('#tspinner').show();
    var params = $(form).serialize();
    $.ajax({
        url: 'ajx_savetentedit.php',
        data: params,
        success: function (req) {
            $('#tentedit').hide();
            $('#finished').show();
        }
    });
    return false;
}