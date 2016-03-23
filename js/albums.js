// [ts] global functions and/or variables for JSLint
/*global ModalDialog, textSnippet, updateMediaOrder */
var album, mediacount, remove_text, thumbmaxw, tnglitbox;

function validateForm() {
    'use strict';
    var rval = true;
    if (document.form1.albumname.value.length === 0) {
        alert("<?php echo uiTextSnippet('enteralbumname'); ?>");
        rval = false;
    }
    return rval;
}

function toggleHeadstoneCriteria(form, mediatypeID) {
    'use strict';
    var hsstatus = $('#hsstatrow');
    var cemrow = $('#cemrow');
    if (mediatypeID === 'headstones') {
        $('#newmedia').css('height', '380px');
        cemrow.show();
        hsstatus.show();
    } else {
        $('#newmedia').css('height', '430px');
        cemrow.hide();
        form.cemeteryID.selectedIndex = 0;
        hsstatus.hide();
        form.hsstat.selectedIndex = 0;
    }
    return false;
}

function showMedia(req) {
    'use strict';
    $('#newmedia').html(req);
    $('#spinner1').hide();
}

function getNewMedia(form, flag) {
    'use strict';
    var searchstring = form.searchstring.value;
    if (searchstring) {
        $('#spinner1').show();
        $('#newmedia').html('');
        var searchtree = form.searchtree.options[form.searchtree.selectedIndex].value;
        var mediatypeID = form.mediatypeID.options[form.mediatypeID.selectedIndex].value;
        var params = {
            albumID: album,
            searchstring: searchstring,
            searchtree: searchtree,
            mediatypeID: mediatypeID
        };
        if (mediatypeID === "headstones") {
            var hsstat = form.hsstat.options[form.hsstat.selectedIndex].value;
            var cemeteryID = form.cemeteryID.options[form.cemeteryID.selectedIndex].value;
            params.hsstat = hsstat;
            params.cemeteryID = cemeteryID;
        }

        $.ajax({
            url: 'admin_add2albumxml.php',
            data: params,
            dataType: 'html',
            success: showMedia
        });
    } else if (flag) {
        alert(textSnippet('entersearchvalue'));
    }
}

function getMoreMedia(searchstring, mediatypeID, hsstat, cemeteryID, offset, tree, page, albumID) {
    'use strict';
    var params = {
        searchstring: searchstring,
        mediatypeID: mediatypeID,
        hsstat: hsstat,
        cemeteryID: cemeteryID,
        offset: offset,
        tree: tree,
        page: page,
        albumID: albumID
    };
    $.ajax({
        url: 'admin_add2albumxml.php',
        data: params,
        dataType: 'html',
        success: showMedia
    });
    return false;
}

function finishAddToAlbum(req) {
    'use strict';
    var newrow;

    var pairs = req.split('&');
    var media = parseInt(pairs[0], 10);
    var albumlink = parseInt(pairs[1], 10);

    var newnum = $('.sortrow').length + 1;

    newrow = '<table width="100%" cellpadding="5" cellspacing="1"><tr>\n';
    newrow += '<td class="dragarea">\n';
    newrow += '<img src="img/admArrowUp.gif" alt=""><br>' + textSnippet('drag') + '<br>\n';
    newrow += '<img src="img/admArrowDown.gif" alt="">\n';
    newrow += '</td>\n';

    newrow += '<td class="small" style="width:35px;text-align:center\">';
    newrow += '<div style=\"padding-bottom:5px\"><a href="#" onclick="return moveItemInList(\'' + albumlink + '\',1);" title="' + textSnippet('movetop') + '"><img src="img/admArrowUp.gif" alt=""><br>Top</a></div>\n';
    newrow += '<input style="width:30px" class="movefields" name="move' + albumlink + '" id="move' + albumlink + '" value="' + newnum + '" onkeypress="return handleMediaEnter(\'' + albumlink + '\',$(\'#move' + albumlink + '\').val(),event);" />\n';
    newrow += '<a href="#" onclick="return moveItemInList(\'' + albumlink + '\',$(\'#move' + albumlink + '\').val());" title="' + textSnippet('movetop') + '">Go</a>';
    newrow += '</td>\n';

    newrow += '<td style="width:' + (thumbmaxw + 6) + 'px;text-align:center;">' + $('#thumbcell_' + media).html() + '</td>\n';
    newrow += '<td>' + $('#desc_' + media).html();
    newrow += '<div id="del_' + albumlink + '" class="small" style="color:gray;visibility:hidden">';
    if ($('#thumbcell_' + media).html() !== "&nbsp;") {
        newrow += '<input name="rthumbs" type="radio" value="r' + media + '" onclick="makeDefault(this);">' + textSnippet('makedefault');
        newrow += ' &nbsp;|&nbsp; ';
    }
    newrow += '<a href="#" onclick="return removeFromAlbum(\'' + media + '\',\'' + albumlink + '\');">' + remove_text + '</a></div></td>\n';
    newrow += '<td style="width:150px;">' + $('#date_' + media).html() + '</td>';
    newrow += '<td style="width:100px;">' + $('#mtype_' + media).html() + '</td>\n';
    newrow += '</tr></table>';

    $('#add_' + media).hide();
    if ($('#added_' + media).html() === "") {
        $('#added_' + media).html('<img class="icon-sm" src="svg/eye.svg" alt="">');
    }
    $('#added_' + media).fadeIn(400);

    var div = document.createElement("div");
    div.id = "orderdivs_" + albumlink;
    div.className = "sortrow";
    div.style.clear = "both";
    div.style.position = "relative";
    div.onmouseover = function () {
        $('#del_' + albumlink).css('visibility', 'visible');
    };
    div.onmouseout = function () {
        $('#del_' + albumlink).css('visibility', 'hidden');
    };
    div.innerHTML = newrow;
    $('#orderdivs').append(div);

    mediacount = mediacount + 1;
    $('#mediacount').html(mediacount);
    $('#nomedia').hide();
    $('#orderdivs').sortable({tag: 'div', update: updateMediaOrder});
}

function addToAlbum(media) {
    'use strict';
    var params = {media: media, album: album, action: 'add'};
    $.ajax({
        url: 'ajx_updateorder.php',
        data: params,
        dataType: 'html',
        success: finishAddToAlbum
    });
    return false;
}

function removeFromAlbum(media, albumlink) {
    'use strict';
    if (confirm(textSnippet('confremmedia'))) {
        var params = {
            media: media,
            albumlink: albumlink,
            action: 'remalb'
        };
        $.ajax({
            url: 'ajx_updateorder.php',
            data: params,
            dataType: 'html',
            success: function (req) {
                var pairs = req.split('&');
                media = parseInt(pairs[0], 10);
                albumlink = parseInt(pairs[1], 10);
                $('#orderdivs_' + albumlink).fadeOut(400, function () {
                    $('#orderdivs_' + albumlink).remove();
                });
                if ($('#added_' + media).length) {
                    $('#added_' + media).hide();
                    $('#add_' + media).fadeIn(400);
                }
                mediacount = mediacount - 1;
                $('#mediacount').html(mediacount);
            }
        });
    }
    return false;
}

function openAlbumMediaFind() {
    'use strict';
    tnglitbox = new ModalDialog('findalbummedia.php', {size: 'modal-lg'});
    return false;
}