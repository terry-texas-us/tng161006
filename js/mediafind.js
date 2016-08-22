function getPotentialLinks(linktype) {
    'use strict';
    var form = document.find2;
    var strParams = {linktype: linktype};

    switch (linktype) {
    case 'I':
        strParams.lastname = form.mylastname.value;
        strParams.firstname = form.myfirstname.value;
        break;
    case 'F':
        strParams.husbname = form.myhusbname.value;
        strParams.wifename = form.mywifename.value;
        break;
    case 'S':
        strParams.title = form.mysourcetitle.value;
        break;
    case 'R':
        strParams.title = form.myrepotitle.value;
        break;
    case 'L':
        strParams.place = form.myplace.value;
        break;
    }
    if (searchstring) {
        doSpinner('find');

        if (type === "album") {
            strParams.albumID = album;
        } else {
            strParams.mediaID = media;
        }
        $.ajax({
            url: 'admin_medialinksxml.php',
            data: strParams,
            dataType: 'html',
            success: function (req) {
                $('#newlines').html(req);
                lastspinner.hide();
            }
        });
    }
    return false;
}

function doSpinner(id) {
    'use strict';
    lastspinner = $('#spinner' + id);
    lastspinner.show();
}

function toggleEventLink(index) {
    'use strict';
    var eventlink = document.find.eventlink1;
    var event = document.find.event1;
    var newlink = document.find.newlink1;

    //blank out & deselect Event box
    if (event) {
        event.selectedIndex = 0;
        event.options.length = 0;
    }
    //blank out ID box
    if (index >= 0) {
        newlink.value = "";
    }

    //hide/reveal checkbox
    if (index > 3) {
        eventlink.style.display = 'none';
        $('#eventlink1').hide();
    } else if (index >= 0) {
        eventlink.style.display = '';
        $('#eventlink1').show();
    }

    //hide event row and uncheck box
    if (!findform)
        findform = "form1";
    if (event) {
        toggleEventRow(0);
        var check = document.find.eventlink1;
        check.checked = false;
    }
}

function toggleEventRow(check, entrynum) {
    var eventrow = $('#eventrow1');
    if (check) {
        var entity = document.find.newlink1;
        if (!entity.value)
            return false;
        eventrow.show();
        var tree = document.find.tree1;
        var linktype = document.find.linktype1;
        fetchData(linktype.options[linktype.selectedIndex].value, entity.value, entrynum);
    } else
        eventrow.hide();
    return true;
}

function fetchData(linktype, persfamID, count) {
    var strParams = "persfamID=" + persfamID + "&linktype=" + linktype + "&count=" + count;
    var loader1 = new net.ContentLoader('admin_mediaeventxml.php', fillList, null, "POST", strParams);
}

function createOption(olist, ovalue, otext) {
    if (navigator.appName === "Netscape") {
        olist.options[olist.options.length] = new Option(otext, ovalue, false, false);
    } else if (navigator.appName === "Microsoft Internet Explorer") {
        var newElem = document.createElement("OPTION");
        newElem.text = otext;
        newElem.value = ovalue;
        olist.options.add(newElem);
    }
}

function fillList() {
    var xmlDoc = this.req.responseXML.documentElement;
    var xCount = xmlDoc.getElementsByTagName('targetlist');
    var countnode = getTextNodes(xCount[0]);
    var count = countnode['target'].firstChild.nodeValue;

    var xEvent = xmlDoc.getElementsByTagName('event');
    var evnodes, eventID, displayval, info, displaystr, dest;

    //know which list to fill! (dest)
    dest = document.find.event1;

    //blank out list
    dest.options.length = 0;
    createOption(dest, '', '');

    for (i = 0; i < xEvent.length; i++) {
        evnodes = getTextNodes(xEvent[i]);
        eventID = evnodes['eventID'].firstChild.nodeValue;
        displayval = evnodes['display'].firstChild.nodeValue;
        info = evnodes['info'].firstChild.nodeValue;
        if (info !== "-1")
            displaystr = displayval + ": " + info;
        else
            displaystr = displayval;

        //fill list
        createOption(dest, eventID, displaystr);
    }
}

function getTextNodes(node) {
    var textNodes = new Array();

    for (var i = 0; i < node.childNodes.length; i++) {
        if (node.childNodes[i].nodeType === "1") {
            textNodes[node.childNodes[i].nodeName] = node.childNodes[i];
        }
    }
    return textNodes;
}

function selectEntity(field, entity) {
    field.value = entity;
    field.focus();
    tnglitbox.remove();
    //document.find.submit();
}

function deleteMedia2EntityLink(linkID) {
    if (confirm(textSnippet('confdellink'))) {
        var tds = $('tr#alink_' + linkID + ' td');
        $.each(tds, function (index, item) {
            $(item).effect('highlight', {color: '#ff9999'}, 200);
        });
        var params = {linkID: linkID, type: type, action: 'dellink'};
        $.ajax({
            url: 'ajx_updateorder.php',
            data: params,
            dataType: 'html',
            success: function (req) {
                var pairs = req.split('&');
                var link = parseInt(pairs[0]);
                var entityID = pairs[1];
                $('#alink_' + link).fadeOut(400);
                if ($('#linked_' + entityID).length) {
                    $('#linked_' + entityID).hide();
                    $('#link_' + entityID).fadeIn(400);
                }
                linkcount = linkcount - 1;
                $('#linkcount').html(linkcount);
            }
        });
    }
    return false;
}

function editMedia2EntityLink(linkID) {
    tnglitbox = new ModalDialog('admin_editmedialink.php?linkID=' + linkID + '&type=' + type, {size: 'modal-lg'});
    return false;
}

function updateMedia2EntityLink(form) {
    var params = $(form).serialize() + '&action=updatelink';
    $.ajax({
        url: 'ajx_updateorder.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            var eventID = form.eventID;
            var linkID = form.linkID.value;
            $('#event_' + linkID).html(eventID.selectedIndex ? eventID.options[eventID.selectedIndex].text : '&nbsp;');
            if (form.type.value !== "album") {
                $('#alt_' + linkID).html((form.altdescription.value || form.altnotes.value) ? textSnippet('yes') : '&nbsp;');
                $('#defc' + linkID).attr('checked', form.defphoto.checked);
                $('#show' + linkID).attr('checked', form.show.checked);
            }
            tnglitbox.remove();
            $('#event_' + linkID).effect('highlight', {}, 1400);
            if (form.type.value !== "album") {
                $('#alt_' + linkID).effect('highlight', {}, 1400);
                $('#def_' + linkID).effect('highlight', {}, 1400);
                $('#show_' + linkID).effect('highlight', {}, 1400);
            }
        }
    });
    return false;
}

function addMedia2EntityLink(form, newEntityID, num) {
    if (newEntityID) {
        var entityID = decodeURIComponent(newEntityID);
        //form.newlink1.value = decodeURIComponent(entityID).replace(/\+/g,' ');
    } else
        var entityID = form.linktype1.options[form.linktype1.selectedIndex].value === 'L' ? form.newlink1.value : form.newlink1.value.toUpperCase();
    if (!entityID)
        alert(textSnippet('enterid'));
    else {
        var tree = form.tree1.options[form.tree1.selectedIndex].value;
        var linktype = form.linktype1.options[form.linktype1.selectedIndex].value;
        var params = {tree: tree, linktype: linktype, entityID: entityID, type: type, action: 'addlink'};
        if (type === "album")
            params.albumID = album;
        else
            params.mediaID = media;
        $.ajax({
            url: 'ajx_updateorder.php',
            data: params,
            dataType: 'html',
            success: function (req) {
                if (req === "1") {
                    $('#alink_error').html(textSnippet('duplinkmsg'));
                    $('#alink_error').fadeIn(200);
                } else if (req.responseText === "2") {
                    $('#alink_error').html(textSnippet('invlinkmsg'));
                    $('#alink_error').fadeIn(200);
                } else {
                    $('#alink_error').html('');
                    $('#alink_error').fadeOut(200);

                    var vars = req.split('|');
                    var linkID = parseInt(vars[0]);
                    var name = decodeURIComponent(vars[1]);
                    var hasthumb = parseInt(vars[2]);
                    var mediatypeID = vars[3];

                    var linkID = parseInt(req);
                    var newrow;
                    var displayID = linktype !== 'L' ? ' (' + entityID + ')' : "";
                    var dims = "width=\"20\" height=\"20\" class=\"icon-sm\"";
                    var treename = form.tree1.options[form.tree1.selectedIndex].text;
                    var treeID = form.tree1.options[form.tree1.selectedIndex].value;
                    var linktext = form.linktype1.options[form.linktype1.selectedIndex].text;

                    if ($('#linkcount').length) {
                        linkcount = linkcount + 1;
                        $('#linkcount').html(linkcount);
                    }

                    var linktable = document.getElementById('linktable');
                    //var newtr = linktable.insertRow(linktable.rows.length);
                    var newtr = linktable.insertRow(1);
                    newtr.id = 'alink_' + linkID;
                    newtr.setAttribute('style', 'display:none');

                    var actionbuttons = '<a href="#" title="' + textSnippet('edit') + '" onclick="return editMedia2EntityLink(' + linkID + ');">';
                    actionbuttons += '<img class="icon-sm" src="svg/new-message.svg" alt="' + textSnippet('edit') + '">';
                    actionbuttons += '</a>';
                    actionbuttons += '<a href="#" title="' + remove_text + '" onclick="return deleteMedia2EntityLink(' + linkID + ');">';
                    actionbuttons += '<img class="icon-sm" src="svg/trash.svg" alt="' + remove_text + '">';
                    actionbuttons += '</a>';
                    var td0 = insertCell(newtr, 0, "normal", actionbuttons);
                    td0.setAttribute('align', 'center');
                    insertCell(newtr, 1, "normal", linktext);
                    var sortlink = type !== "album" ? ' (<a href="mediaSortFormAction.php?linktype1=' + linktype + '&mediatypeID=' + mediatypeID + '&newlink1=' + entityID + '&event1=">' + textSnippet('text_sort') + '</a>)' : '';
                    insertCell(newtr, 2, "normal", name + displayID + sortlink);
                    insertCell(newtr, 3, "normal", treename + '&nbsp;');

                    var td4 = insertCell(newtr, 4, "normal", '&nbsp;')
                    td4.id = 'event_' + linkID;

                    if (type === "media") {
                        var td5 = insertCell(newtr, 5, "normal", '&nbsp;')
                        td5.id = 'alt_' + linkID;
                        //var defphoto = vars[2] == "1" ? textSnippet('yes') : "";
                        var td6 = insertCell(newtr, 6, "normal", '<input id="defc' + linkID + '" name="defc' + linkID + '" type="checkbox" onclick="toggleDefault(this,\'' + entityID + '\');" value="1"/>');
                        td6.id = 'def_' + linkID;
                        td6.setAttribute('align', 'center');
                        var td7 = insertCell(newtr, 7, "normal", '<input id="show' + linkID + '" name="show' + linkID + '" type="checkbox" onclick="toggleShow(this);" value="1" checked>');
                        td7.id = 'show_' + linkID;
                        td7.setAttribute('align', 'center');
                    }

                    $('#alink_' + linkID).fadeIn(400, function () {
                        var tds = $('tr#alink_' + linkID + ' td');
                        $.each(tds, function (index, item) {
                            $(item).effect('highlight', {}, 1200);
                        });
                    });

                    //strip slashes and apostrophes
                    var id = linktype === 'L' ? num : entityID;
                    if ($('#link_' + id).length) {
                        $('#link_' + id).hide();
                        if (hasthumb === "1" && type === "media")
                            $('#sdef_' + entityID).html('<a href="#" onclick="return setDefault(' + linkID + ',\'' + entityID + '\');" class="smallest">' + textSnippet('makedefault') + '</a>');
                        $('#linked_' + id).fadeIn(400);
                    }
                    $('#nolinks').hide();
                }
            }
        });
    }
    return false;
}

function newlinkEnter(form, field, e) {
    var keycode;
    if (window.event)
        keycode = window.event.keyCode;
    else if (e)
        keycode = e.which;
    else
        return true;
    if (keycode === 13)
        return addMedia2EntityLink(form);
    else
        return true;
}