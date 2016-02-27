// [ts] global functions and variables for jsLint
var album, entity, tree;

function numberWithCommas(x) {
    'use strict';
    var parts = x.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}

if (!String.prototype.trim) {
    String.prototype.trim = function () {
        'use strict';
        return this.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
    };
}

function deleteIt(type, id, tree) {
    'use strict';
    var tds = $('#row_' + id + ' td'), params;
    $.each(tds, function (index, item) {
        $(item).effect('highlight', {color: '#ff9999'}, 1500);
    });
    params = {t: type, id: id, desc: tree};
    $.ajax({
        url: 'ajx_delete.php',
        data: params,
        dataType: 'html',
        success: function (entity) {
            if ($('#row_' + entity).length) {
                $('#row_' + entity).fadeOut(400);
                var allTotals = $('.restotal'), allPageTotals;
                $.each(allTotals, function (index, item) {
                    var total = $(item);
                    total.html(numberWithCommas(parseInt(total.html().replace(/,/g, ''), 10) - 1));
                });
                allPageTotals = $('.pagetotal');
                $.each(allPageTotals, function (index, item) {
                    var pagetotal = $(item);
                    pagetotal.html(numberWithCommas(parseInt(pagetotal.html().replace(/,/g, ''), 10) - 1));
                });
            }
        }
    });
    return false;
}

function toggleSection(section, img, display) {
    'use strict';
    var doit = true, agent;
    if (display === 'on') {
        $('#' + img).attr('src', 'img/tng_collapse.gif');
        if (section === "modifyexisting") {
            agent = navigator.userAgent.toLowerCase();
            if (agent.indexOf('safari') !== -1) {
                doit = false;
            }
        }
        if (doit) {
            $('#' + section).fadeIn(300);
        } else {
            $('#' + section).show();
        }
    } else if (display === 'off') {
        $('#' + img).attr('src', 'img/tng_expand.gif');
        $('#' + section).fadeOut(300);
    } else {
        $('#' + img).attr('src', $('#' + img).attr('src').indexOf('collapse') > 0 ? 'img/tng_expand.gif' : 'img/tng_collapse.gif');
        if (section === "addmedia") {
            agent = navigator.userAgent.toLowerCase();
            if (agent.indexOf('safari') !== -1 && agent.indexOf('version/3') === -1) {
                doit = false;
            }
        }
        if (doit) {
            $('#' + section).toggle(300);
        } else {
            $('#' + section.css('display', $('#' + section).css('display') === 'none' ? '' : 'none'));
        }
    }
    return false;
}

function makeFolder(folder, name) {
    'use strict';
    $('#msg_' + folder).html('<img src="img/spinner.gif">');
    var params = {folder: name};
    $.ajax({
        url: 'admin_makefolder.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            $('#msg_' + folder).html(req);
            $('#msg_' + folder).effect('highlight', {}, 200);
        }
    });

    return false;
}

function makeDefault(photo) {
    'use strict';
    var params = {
        media: photo.value.substr(1),
        entity: entity,
        tree: tree,
        album: album,
        action: 'setdef'
    };
    $.ajax({
        url: 'ajx_updateorder.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            $('#removedefault').show();
            if (req !== "1") {
                $('#thumbholder').html(req);
                $('#thumbholder').fadeIn(400);
                $('#removedefault').css('visibility', 'visible');
            }
        }
    });
}

function removeDefault() {
    'use strict';
    $('#removedefault').hide();
    $('#thumbholder').fadeOut(400, function () {
        $('#thumbholder').html('');
    });
    var i, params;
    for (i = 0; i < document.form1.rthumbs.length; i += 1) {
        if (document.form1.rthumbs[i].checked) {
            document.form1.rthumbs[i].checked = '';
        }
    }
    params = {entity: entity, tree: tree, album: album, action: 'deldef'};
    $.ajax({
        url: 'ajx_updateorder.php',
        data: params
    });
    return false;
}

function deepOpen(url, winName) {
    'use strict';
    window.open('deepindex.php?page=' + encodeURIComponent(url, winName));
}