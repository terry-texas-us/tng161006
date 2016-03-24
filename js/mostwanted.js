function updateMostWantedOrder(mwtype) {
    if (mwtype === "person")
        var linklist = removePrefixFromArray($('#orderpersondivs').sortable('toArray'), 'orderpersondivs_');
    else
        var linklist = removePrefixFromArray($('#orderphotodivs').sortable('toArray'), 'orderphotodivs_');

    var params = {sequence: linklist.join(','), mwtype: mwtype, action: 'mworder'};
    $.ajax({
        url: 'ajx_updateorder.php',
        data: params,
        dataType: 'html'
    });
}

function updatePersonOrder(event, ui) {
    updateMostWantedOrder('person');
}

function updatePhotoOrder(event, ui) {
    updateMostWantedOrder('photo');
}

function startMostWanted() {
    $('#orderpersondivs').sortable({dropOnEmpty: true, tag: 'div', connectWith: '#orderphotodivs', update: updatePersonOrder});
    $('#orderphotodivs').sortable({dropOnEmpty: true, tag: 'div', connectWith: '#orderpersondivs', update: updatePhotoOrder});
}

function openMostWanted(mwtype, ID) {
    mwlitbox = new ModalDialog('admin_editmostwanted.php?mwtype=' + mwtype + '&ID=' + ID, {size: 'modal-lg'});
    return false;
}

function openMostWantedMediaFind(tree) {
    tnglitbox = new ModalDialog('admin_findmwmedia.php?tree=' + tree);
    return false;
}

function updateMostWanted(form) {
    if (form.title.value.length === 0)
        alert(textSnippet('entertitle'));
    else if (form.description.value.length === 0)
        alert(textSnippet('enterdesc'));
    else {
        var params = $(form).serialize();
        $.ajax({
            url: 'admin_updatemostwanted.php',
            data: params,
            type: 'post',
            dataType: 'json',
            success: function (vars) {
                if (form.ID.value) {
                    //if its old, just update existing row and highlight
                    $('#title_' + vars.ID).html(vars.title);
                    $('#desc_' + vars.ID).html(vars.description);
                    //update thumbnail if necessary
                    if (vars.thumbpath) {
                        $('#img_' + vars.ID).attr('src', vars.thumbpath);
                        $('#img_' + vars.ID).css('width', vars.width + 'px');
                        $('#img_' + vars.ID).css('height', vars.height + 'px');
                    }
                } else {
                    //if it's new, then insert row at bottom
                    var newcontent = '<div class="sortrow" id="order' + vars.mwtype + 'divs_' + vars.ID + '" style="clear:both" onmouseover="showEditDelete(\'' + vars.ID + '\');" onmouseout="hideEditDelete(\'' + vars.ID + '\');">\n';
                    newcontent += '<table width="100%" cellpadding="5" cellspacing="1"><tr id="row_' + vars.ID + '">\n';
                    newcontent += '<td class="dragarea">\n';
                    newcontent += '<img src="img/admArrowUp.gif" alt=""><br>' + textSnippet('drag') + '<br>\n';
                    newcontent += '<img src="img/admArrowDown.gif" alt="">\n';
                    newcontent += '</td>\n';
                    newcontent += '<td style="width:' + thumbwidth + 'px;text-align:center;">\n';
                    if (vars.thumbpath)
                        newcontent += '<img src="' + vars.thumbpath + '" width="' + vars.width + '" height="' + vars.height + '" id="img_' + vars.ID + '" alt="' + vars.description + '">\n';
                    else
                        newcontent += "&nbsp;";

                    newcontent += '</td>\n';
                    newcontent += '<td>\n';
                    if (vars.edit)
                        newcontent += '<a href="#" onclick="return openMostWanted(\'' + vars.mwtype + '\',\'' + vars.ID + '\');" id="title_' + vars.ID + '">' + vars.title + '</a>\n';
                    else
                        newcontent += '<u id="title_' + vars.ID + '">' + vars.title + '</u>\n';
                    newcontent += '<br><span id="desc_' + vars.ID + '">' + vars.description + '</span><br>\n';
                    newcontent += '<div id="del_' + vars.ID + '" class="small" style="color:gray;visibility:hidden">\n';
                    if (vars.edit) {
                        newcontent += '<a href="#" onclick="return openMostWanted(\'' + vars.mwtype + '\',\'' + vars.ID + '\');">' + textSnippet('edit') + '</a>\n';
                        if (vars.del)
                            newcontent += ' | ';
                    }
                    if (vars.del)
                        newcontent += '<a href="#" onclick="return removeFromMostWanted(\'' + vars.mwtype + '\',\'' + vars.ID + '\');">' + textSnippet('delete') + '</a>\n';
                    newcontent += '</div>\n</td>\n</tr></table>\n</div>\n';
                    $('#order' + vars.mwtype + 'divs').html(newcontent + $('#order' + vars.mwtype + 'divs').html());
                    if (vars.mwtype == 'person')
                        $('#orderpersondivs').sortable({dropOnEmpty: true, tag: 'div', connectWith: '#orderphotodivs', update: updatePersonOrder});
                    else
                        $('#orderphotodivs').sortable({dropOnEmpty: true, tag: 'div', connectWith: '#orderpersondivs', update: updatePhotoOrder});
                }

                var tds = $('tr#row_' + vars.ID + ' td');
                mwlitbox.remove();
                $.each(tds, function (index, item) {
                    $(item).effect('highlight', {}, 2000);
                });
            }
        });
    }
    return false;
}

function removeFromMostWanted(type, id) {
    if (confirm(textSnippet('confremmw'))) {
        var params = {id: id, action: 'remmostwanted'};
        $.ajax({
            url: 'ajx_updateorder.php',
            data: params,
            dataType: 'html',
            success: function () {
                $('#order' + type + 'divs_' + id).fadeOut(400, function () {
                    $('#order' + type + 'divs_' + id).remove();
                    if (type == 'person')
                        $('#orderpersondivs').sortable({dropOnEmpty: true, tag: 'div', connectWith: '#orderphotodivs', update: updatePersonOrder});
                    else
                        $('#orderphotodivs').sortable({dropOnEmpty: true, tag: 'div', connectWith: '#orderpersondivs', update: updatePhotoOrder});
                });
            }
        });
    }
    return false;
}

function showEditDelete(id) {
    if ($('#del_' + id).length)
        $('#del_' + id).css('visibility', 'visible');
}

function hideEditDelete(id) {
    if ($('#del_' + id).length)
        $('#del_' + id).css('visibility', 'hidden');
}

function getNewMwMedia(form) {
    var hsstring;
    var searchstring = form.searchstring.value;

    doSpinner(1);
    $('#newmedia').html('');
    var searchtree = form.tree.value;
    var mediatypeID = form.mediatypeID.value;

    var strParams = {searchstring: searchstring, searchtree: searchtree, mediatypeID: mediatypeID};
    $.ajax({
        url: 'admin_add2albumxml.php',
        data: strParams,
        dataType: 'html',
        success: showMedia
    });
}

function getMoreMedia(searchstring, mediatypeID, hsstat, cemeteryID, offset, tree, page, albumID) {
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

function showMedia(req) {
    $('#newmedia').html(req);
    $('#spinner1').hide();
}

function doSpinner(id) {
    lastspinner = $('#spinner' + id);
    $('#spinner' + id).show();
}

function selectMedia(mediaID) {
    document.editmostwanted.mediaID.value = mediaID;
    $('#mwthumb').html("&nbsp;");
    $('#mwdetails').html(textSnippet('loading'));

    $.ajax({
        url: 'admin_getphotodetails.php',
        data: {mediaID: mediaID},
        dataType: 'html',
        success: function (req) {
            $('#mwphoto').html(req);
        }
    });

    tnglitbox.remove();
    return false;
}