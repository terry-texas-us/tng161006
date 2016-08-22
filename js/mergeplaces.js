function validateForm1() {
    var rval = true;
    if (document.form1.place.value.length === 0) {
        alert(textSnippet('enterplace'));
        rval = false;
    }
    return rval;
}

function validateForm2(form) {
    var rval = false;
    var keepid = "";

    blankMsg();
    for (var i = 0; i < form.keep.length; i++) {
        if (form.keep[i].checked) {
            keepid = form.keep[i].value;
            rval = true;
            break;
        }
    }
    if (!rval)
        alert(textSnippet('enterkeep'));
    if (rval) {
        var checks = $('input.mc');
        var mergelist = new Array();
        checks.each(function (index, item) {
            if (item.checked && item.value != keepid)
                mergelist.push(item.value);
        });
        var keep = form.keep[i].value;
        $('#placespin').show();
        var params = {places: mergelist.join(','), keep: keep};
        $.ajax({
            url: 'admin_mergeplacesajax.php',
            data: params,
            dataType: 'json',
            success: function (vars) {
                $('#lat_' + keepid).html(vars.latitude);
                $('#long_' + keepid).html(vars.longitude);
                $.each(mergelist, function (index, item) {
                    $('#row_' + item).fadeOut(300);
                });
                $('#placespin').hide();
                $('#successmsg1').html(textSnippet('pmsucc'));
                $('#successmsg2').html(textSnippet('pmsucc'));
                var lastone = eval('form.mc' + keep);
                lastone.checked = false;
            }
        });
    }

    return false;
}

var delcolor = '#ff9999';
var keepcolor = '#99ff99';
var neutcolor = '#ffffff';
var lastradio;

function blankMsg() {
    $('#successmsg1').html('');
    $('#successmsg2').html('');
}

function handleCheck(id) {
    var check = eval('document.form2.mc' + id + '.checked');
    var newcolor = check ? delcolor : '';

    blankMsg();
    var tds = $('tr#row_' + id + ' td');
    var currRadioChecked = $('#r' + id).is(':checked');
    if (!currRadioChecked) {
        $.each(tds, function (index, item) {
            item.style.backgroundColor = newcolor;
        });
    }
}

function handleRadio(id) {
    var newcolor, tds, currID;

    blankMsg();
    var trs = $('tr.mergerows');
    $.each(trs, function (index, row) {
        newcolor = "";
        currID = parseInt(row.id.substr(4));
        if (id == currID)
            newcolor = keepcolor;
        else {
            currCheck = eval('document.form2.mc' + currID + '.checked');
            if (currCheck)
                newcolor = delcolor;
            else {
                if (currID == lastradio)
                    newcolor = neutcolor;
            }
        }
        if (newcolor) {
            tds = $('tr#' + row.id + ' td');
            $.each(tds, function (index, item) {
                item.style.backgroundColor = newcolor;
            })
        }
    })
    lastradio = id;
}
