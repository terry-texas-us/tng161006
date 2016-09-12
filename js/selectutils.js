// [ts] global functions and/or variables for JSLint
/*global ModalDialog, textSnippet, updateChildrenOrder */
var allow_notes, allow_cites, persfamID, tnglitbox, tree;

function AddtoDisplay(source, dest) {
    'use strict';
    if (source.options[source.selectedIndex].selected) {
        if (navigator.appName === "Netscape") {
            dest.options[dest.options.length] = new Option(source.options[source.selectedIndex].text, source.options[source.selectedIndex].value, false, false);
        } else if (navigator.appName === "Microsoft Internet Explorer") {
            var newElem = document.createElement("OPTION");
            newElem.text = source.options[source.selectedIndex].text;
            newElem.value = source.options[source.selectedIndex].value;
            dest.options.add(newElem);
        }
    }
}

function RemovefromDisplay(fieldlist) {
    'use strict';
    if (fieldlist.options[fieldlist.selectedIndex].selected) {
        if (navigator.appName === "Netscape") {
            fieldlist.options[fieldlist.selectedIndex] = null;
        } else if (navigator.appName === "Microsoft Internet Explorer") {
            fieldlist.options.remove(fieldlist.selectedIndex);
        }
    }
}

function Move(fieldlist, dir) {
    'use strict';
    var tempval = fieldlist.options[fieldlist.selectedIndex].value,
        temptxt = fieldlist.options[fieldlist.selectedIndex].text;

    if (dir) {
        fieldlist.options[fieldlist.selectedIndex].value = fieldlist.options[fieldlist.selectedIndex - 1].value;
        fieldlist.options[fieldlist.selectedIndex - 1].value = tempval;
        fieldlist.options[fieldlist.selectedIndex].text = fieldlist.options[fieldlist.selectedIndex - 1].text;
        fieldlist.options[fieldlist.selectedIndex - 1].text = temptxt;
        fieldlist.selectedIndex -= 1;
    } else {
        fieldlist.options[fieldlist.selectedIndex].value = fieldlist.options[fieldlist.selectedIndex + 1].value;
        fieldlist.options[fieldlist.selectedIndex + 1].value = tempval;
        fieldlist.options[fieldlist.selectedIndex].text = fieldlist.options[fieldlist.selectedIndex + 1].text;
        fieldlist.options[fieldlist.selectedIndex + 1].text = temptxt;
        fieldlist.selectedIndex += 1;
    }
}

function TrimString(sInString) {
    'use strict';
    sInString = sInString.replace(/^\s+/g, "");// strip leading
    return sInString.replace(/\s+$/g, "");// strip trailing
}

function getTree(treefield) {
    'use strict';
    if (treefield) {
        if (treefield.options.length) {
            return treefield.options[treefield.selectedIndex].value;
        }
        alert(textSnippet('selecttree'));
        return false;
    }
    return tree; // !global
}

function generateID(type, dest) {
    'use strict';
    $.ajax({
        url: 'admin_generateID.php',
        data: {type: type},
        dataType: 'html',
        success: function (req) {
            $(dest).val(req);
        }
    });
}

function checkID(id, type, dest) {
    'use strict';
    $.ajax({
        url: 'admin_checkID.php',
        data: {checkID: id, type: type},
        dataType: 'html',
        success: function (req) {
            $('#' + dest).html(req);
        }
    });
}

function insertCell(row, index, classname, content) {
    'use strict';
    var cell = row.insertCell(index);
    cell.className = classname;
    cell.innerHTML = content ? content : content + '&nbsp;';
    return cell;
}

function getActionButtons(vars, type) {
    'use strict';
    var celltext = "",
        iconColor = type === "Citation" ? "icon-info" : "icon-muted";

    if (vars.allow_edit) {
        celltext += "<a href='#' onclick=\"return edit" + type + "('" + vars.id + "');\" title=\"" + textSnippet('edit') + "\">";
        celltext += "<img class='icon-sm' src='svg/new-message.svg'>";
        celltext += "</a>";
    }
    if (vars.allow_delete) {
        celltext += "<a href='#' onclick=\"return delete" + type + "('" + vars.id + "','" + vars.persfamID + "','" + vars.tree + "','" + vars.eventID + "');\" title=\"" + textSnippet('delete') + "\">";
        celltext += "<img class='icon-sm' src='svg/trash.svg'>";
        celltext += "</a>";
    }
    if (vars.allow_cite) {
        celltext += "<a class='icon-sm icon-citations iconColor' id=\"citesiconN" + vars.id + "\" href='#' onclick=\"return showCitationsInside('N" + vars.id + "','','" + vars.persfamID + "');\" title=\"" + textSnippet('citations') + "\">";
        celltext += "<img class='icon-sm icon-right " + iconColor + "' data-src='svg/archive.svg'>";
        celltext += "</a>";
    }
    return celltext;
}

function addEvent(form) {
    'use strict';
    if (form.eventtypeID.selectedIndex === 0) {
        alert(textSnippet('entereventtype'));
    } else if (form.eventdate.value.length === 0 && form.eventplace.value.length === 0 && form.info.value.length === 0) {
        alert(textSnippet('entereventinfo'));
    } else {
        var params = $(form).serialize();
        $.ajax({
            url: 'admin_addevent.php',
            data: params,
            type: 'POST',
            dataType: 'json',
            success: function (vars) {
                var eventtbl = document.getElementById('custeventstbl'),
                    newtr = eventtbl.insertRow(eventtbl.rows.length),
                    buttons;
                newtr.id = "row_" + vars.id;
                buttons = getActionButtons(vars, 'Event', allow_notes, allow_cites);
                insertCell(newtr, 0, "nw", buttons);
                insertCell(newtr, 1, "", vars.display);
                insertCell(newtr, 2, "", vars.eventdate + "&nbsp;");
                insertCell(newtr, 3, "", vars.eventplace + "&nbsp;");
                insertCell(newtr, 4, "", vars.info + "&nbsp;");

                eventtbl.style.display = '';
                tnglitbox.remove();
            }
        });
    }
    return false;
}

function updateEvent(form) {
    'use strict';
    var eventID = form.eventID.value,
        params = $(form).serialize();
    $.ajax({
        url: 'admin_updateevent.php',
        data: params,
        type: 'POST',
        dataType: 'json',
        success: function (vars) {
            var tds = $('tr#row_' + eventID + ' td');
            tds.eq(1).html(vars.display + "&nbsp;");
            tds.eq(2).html(vars.eventdate + "&nbsp;");
            tds.eq(3).html(vars.eventplace + "&nbsp;");
            tds.eq(4).html(vars.info + "&nbsp;");
            tnglitbox.remove();
            $.each(tds, function (index, item) {
                $(item).effect('highlight', {}, 200);
            });
        }
    });
    return false;
}

function editEvent(eventID) {
    'use strict';
    tnglitbox = new ModalDialog('admin_editevent.php?eventID=' + eventID);
    return false;
}

function newEvent(prefix, persfamID) {
    'use strict';
    tnglitbox = new ModalDialog('admin_newevent.php?prefix=' + prefix + '&persfamID=' + persfamID);
    return false;
}

function deleteEvent(eventID) {
    'use strict';
    if (confirm(textSnippet('confdeleteevent'))) {
        var tds = $('tr#row_' + eventID + ' td');
        $.each(tds, function (index, item) {
            $(item).effect('highlight', {color: '#ff9999'}, 200);
        });
        $.ajax({
            url: 'admin_deleteevent.php',
            data: {eventID: eventID},
            dataType: 'html',
            success: function (req) {
                $('#row_' + eventID).fadeOut(200);
            }
        });
    }
    return false;
}

function removePrefixFromArray(arr, prefix) {
    'use strict';
    var i;
    for (i = 0; i < arr.length; i += 1) {
        if (arr[i].indexOf(prefix) === 0) {
            arr[i] = arr[i].substring(prefix.length);
        }
    }
    return arr;
}

function updateCitationOrder(event, ui) {
    'use strict';
    var citelist = removePrefixFromArray($('#cites').sortable('toArray'), 'citations_');
    $.ajax({
        url: 'ajx_updateorder.php',
        data: {sequence: citelist.join(','), action: 'citeorder'},
        dataType: 'html'
    });
}

function initCitationSort() {
    'use strict';
    $('#cites').sortable({tag: 'div', update: updateCitationOrder});
}

function showCitations(eventID, persfamID) {
    'use strict';
    tnglitbox = new ModalDialog('admin_citations.php?eventID=' + eventID + '&persfamID=' + persfamID, {doneLoading: initCitationSort});
    return false;
}

var prevsection = null;
function gotoSection(start, end) {
    'use strict';
    prevsection = start;
    if (start && end) {
        $('#' + start).fadeOut(200, function () {
            $('#' + end).fadeIn(200, function () {
                if ($('#mytitle').length) {
                    $('#mytitle').focus();
                }
            });
        });
    } else {
        $('#mlbox').remove();
        start.remove();
    }
    return false;
}

var subpage = false;
function showCitationsInside(eventID, noteID, persfamID) {
    'use strict';
    subpage = true;
    var xnote = noteID !== "" ? noteID : "";
    $.ajax({
        url: 'admin_citations.php',
        data: {eventID: eventID, persfamID: persfamID, noteID: xnote},
        dataType: 'html',
        success: function (req) {
            $('#citationslist').html(req);
            gotoSection('notelist', 'citationslist');
            initCitationSort();
        }
    });
    return false;
}

function editCitation(citationID) {
    'use strict';
    var params = {citationID: citationID};
    $.ajax({
        url: 'admin_editcitation.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            $('#editcitation').html(req);
            gotoSection('citations', 'editcitation');
        }
    });
    return false;
}

function updateCitation(form) {
    'use strict';
    var citationID = form.citationID.value,
        params = $(form).serialize();
    $.ajax({
        url: 'admin_updatecitation.php',
        data: params,
        type: 'POST',
        dataType: 'json',
        success: function (vars) {
            var tds = $('tr#row_' + citationID + ' td');
            tds.eq(2).html(vars.display);
            gotoSection('editcitation', 'citations');
            $.each(tds, function (index, item) {
                $(item).effect('highlight', {}, 2500);
            });
        }
    });
    return false;
}

var branchtimer;
function showBranchEdit(branchdiv) {
    'use strict';
    $('#' + branchdiv).slideToggle(200);
    return false;
}

function updateBranchList(branchselect, branchdiv, branchlistdiv) {
    'use strict';
    var branchlist = "",
        gotnone = false,
        firstone = null;
    $('#' + branchselect + ' >option:selected').each(function (index, option) {
        if (!option.value) {
            gotnone = true;
            firstone = option;
        }
        if (branchlist) {
            if (gotnone) {
                branchlist = "";
                firstone.selected = false;
                gotnone = false;
            } else {
                branchlist += ", ";
            }
        }
        branchlist += option.text;
    });
    $('#' + branchlistdiv).html(branchlist);
    showBranchEdit(branchdiv);
}

function quitBranchEdit(branchdiv) {
    'use strict';
    branchtimer = setTimeout("showBranchEdit('" + branchdiv + "')", 3000);
}

function closeBranchEdit(branchselect, branchdiv, branchlistdiv) {
    'use strict';
    branchtimer = setTimeout("updateBranchList('" + branchselect + "','" + branchdiv + "','" + branchlistdiv + "')", 500);
}

var lastFilter = "";
var lastCriteria = "c";
var filterStartSection, filterEndSection, itemIDField, itemTitleDiv;
var timeoutId = 0;

function initFilter(start, end, idfield, titlediv) {
    'use strict';
    lastCriteria = "";
    filterStartSection = end;
    filterEndSection = start;
    itemIDField = idfield;
    itemTitleDiv = titlediv;

    if (start && end) {
        gotoSection(start, end);
    }
    return false;
}

function applyFilter(options) {
    'use strict';
    var form = document.getElementById(options.form);
    options.criteria = document.getElementById(options.fieldId).value;
    if (form.filter) {
        options.filter = form.filter[0].checked ? form.filter[0].value : form.filter[1].value;
    } else {
        options.filter = "c";
    }

    if (options.criteria === lastCriteria && options.filter === lastFilter) {
        return false; //don't search because it's the same as it was the last time
    }
    $('#' + options.destdiv).html('<h4>' + textSnippet('loading') + "</h4>");
    lastCriteria = options.criteria;
    lastFilter = options.filter;

    $.ajax({
        url: 'finditems.php',
        data: options,
        dataType: 'html',
        type: 'get',
        success: function (req) {
            $('#' + options.destdiv).html(req);
        }
    });

    return false;
}

var activebox;
var seclitbox;
function openFindPlaceForm(field, temple) {
    'use strict';
    activebox = field;
    var value = $('#' + field).val(),
        templestr = temple ? "&temple=1" : "";
    seclitbox = new ModalDialog('findplaceform.php?tree=' + tree + '&place=' + encodeURIComponent(value) + templestr);
    initFilter(null, seclitbox, field, null);
    if (value) {
        $('#myplace').val(value);
        applyFilter({form: 'form1', fieldId: 'myplace', type: 'L', tree: tree, destdiv: 'placeresults', temple: temple});
    }
    document.findform1.myplace.focus();

    return false;
}

function findItem(type, field, titlediv, branch, media) {
    'use strict';
    var url,
        branchstr = branch ? 'branch=' + branch : '',
        mediastr = '',
        mediaparts,
        startfield;

    if (media) {
        if (branch) {
            mediastr = '&amp;';
        }
        mediaparts = media.split('_');
        if (mediaparts[0] === 'm') {
            mediastr += 'mediaID=' + mediaparts[1];
        } else {
            mediastr += 'albumID=' + mediaparts[1];
        }
    }
    switch (type) {
    case 'I':
        url = "findpersonform.php";
        startfield = "myflastname";
        break;
    case 'F':
        url = "findfamilyform.php";
        startfield = "myhusbname";
        break;
    case 'S':
        url = "findsourceform.php";
        startfield = "mytitle";
        break;
    case 'R':
        url = "findrepoform.php";
        startfield = "mytitle";
        break;
    case 'L':
        url = "findplaceform.php";
        startfield = "myplace";
        break;
    }
    if (branch || media) {
        url += '?';
    }
    seclitbox = new ModalDialog(url + branchstr + mediastr);
    initFilter(null, seclitbox, field, titlediv);
    $('#' + startfield).focus();

    return false;
}

function fillCemetery(value) {
    'use strict';
    //explode place
    var parts = value.split(','),
        ptr,
        current;
    if (parts.length > 0) {
        ptr = parts.length - 1;
        current = parts[ptr].trim();
        if ($('#country').prop('selectedIndex') < 1 && $('#state').prop('selectedIndex') < 1 && !$('#county').val() && !$('#city').val() && !$('#cemname').val()) {
            $('#country > option').each(function (index, option) {
                if (this.value === current) {
                    $('#country').prop('selectedIndex', index);
                    ptr -= 1;
                    current = parts[ptr].trim();
                    return false;
                }
            });
            $('#state > option').each(function (index, option) {
                if (this.value === current) {
                    $('#state').prop('selectedIndex', index);
                    ptr -= 1;
                    if (ptr >= 0) {
                        $('#county').val(parts[ptr].trim());
                        ptr -= 1;
                    }
                    if (ptr >= 0) {
                        $('#city').val(parts[ptr].trim());
                        ptr -= 1;
                    }
                    $('#cemname').val(parts[ptr].trim());
                    return false;
                }
            });
        }
    }
}

function returnValue(value) {
    'use strict';
    $('#' + activebox).val(value);
    seclitbox.remove();

    if ($('#country').length && !$('#country').prop('selectedIndex') && !$('#state').prop('selectedIndex')) {
        fillCemetery(value);
    }
    return false;
}

var assocType = 'I';
function activateAssocType(type) {
    'use strict';
    if (type === 'I') {
        $('#person_label').show();
        $('#family_label').hide();
    } else if (type === 'F') {
        $('#person_label').hide();
        $('#family_label').show();
    }
    assocType = type;
}

function filterChanged(event, options) {
    'use strict';
    clearTimeout(timeoutId);

    var keycode;
    if (event) {
        keycode = event.keyCode;
    } else if (e) {
        keycode = e.which;
    } else {
        return true;
    }

    if (keycode === 9 || keycode === 13) {
        return false;
    }
    timeoutId = setTimeout(function () {
        applyFilter(options);
    }, 500);
}

function retItem(id, place) {
    'use strict';
    var returntext = $('#item_' + id).text(),
        childcount,
        params,
        current,
        pos,
        imgpos,
        x1,
        y1,
        x2,
        y2,
        area;
    if (itemIDField === "children") {
        childcount = parseInt($('#childcount').html(), 10) + 1;
        returntext += "| - " + id + "<br>" + $('#birth_' + id).html();

        params = {
            personID: id,
            display: returntext,
            familyID: persfamID,
            order: childcount,
            action: 'addchild'
        };
        $.ajax({
            url: 'ajx_updateorder.php',
            data: params,
            type: 'POST',
            dataType: 'html',
            success: function (req) {
                $('#childrenlist').append(req);
                $('#child_' + id).fadeIn(400);
                $('#childcount').html(childcount);
                $('#childrenlist').sortable({tag: 'div', update: updateChildrenOrder}); // [ts] function defined multiple times, check if same 
            }
        });
    } else if (itemIDField === "imagemap") {
        current = $('#mlbox');
        pos = current.position();
        imgpos = $('#myimg').position();

        x1 = Math.round(pos.left - imgpos.left);
        y1 = Math.round(pos.top - imgpos.top);
        x2 = x1 + current.width();
        y2 = y1 + current.height();

        area = '<area coords=\"' + x1 + ',' + y1 + ',' + x2 + ',' + y2 + '\" href=\"' + 'peopleShowPerson.php?personID=' + id + '\" title=\"' + returntext.replace(/\"/g, "'") + '\" />';
        $('#imagemap').val($('#imagemap').val() + area);

        current.remove();
    } else {
        $('#' + itemIDField).val(place ? returntext : id);
        if (itemTitleDiv && $('#' + itemTitleDiv).length) {
            if ($('#birth_' + id).length && $('#birth_' + id).html()) {
                returntext += " (" + $('#birth_' + id).html() + ")";
            }
            if ($('#id_' + id).length) {
                returntext += " - " + id;
            }
            if ($('#' + itemTitleDiv).attr('type') === "text") {
                $('#' + itemTitleDiv).val(returntext);
                $('#' + itemTitleDiv).effect('highlight', {}, 400);
            } else {
                $('#' + itemTitleDiv).html(returntext);
            }
        }
    }
    gotoSection(filterStartSection, filterEndSection);
    if ($('#country').length && !$('#country').prop('selectedIndex') && !$('#state').prop('selectedIndex')) {
        fillCemetery(returntext);
    }

    return false;
}

function initNewItem(type, destid, idfield, titlediv, start, end) {
    'use strict';
    itemIDField = idfield;
    itemTitleDiv = titlediv;

    generateID(type, destid);
    return gotoSection(start, end);
}

function saveSource(form) {
    'use strict';
    if (form.sourceID.value) {
        var params = $(form).serialize();
        params.ajax = 1;
        $.ajax({
            url: 'admin_addsource.php',
            data: params,
            type: 'POST',
            dataType: 'html',
            success: function (req) {
                if (req.indexOf("error:") === 0) {
                    $('#source_error').html(substr(req, 6));
                } else {
                    $('#' + itemIDField).val(form.sourceID.value);
                    $('#' + itemTitleDiv).html(form.shorttitle.value ? form.shorttitle.value : form.title.value);
                    var dest = itemIDField === 'sourceID' ? 'addcitation' : 'editcitation';
                    gotoSection('newsource', dest);
                    $('#source_error').html("");
                    form.reset();
                }
            }
        });
    }
    return false;
}

function getTempleCheck() {
    'use strict';
    return ($("#temple").length && $("#temple").prop('checked') ? 1 : 0);
}

function copylast(form, citationID) {
    'use strict';
    $('#lastspinner').show();
    var params = {citationID: citationID};
    $.ajax({
        url: 'ajx_getlastcite.php',
        data: params,
        dataType: 'json',
        success: function (vars) {
            //fill in form values
            form.sourceID.value = vars.sourceID;
            form.citepage.value = vars.citepage;
            form.quay.selectedIndex = vars.quay === "" ? 0 : parseInt(vars.quay, 10) + 1;
            form.citedate.value = vars.citedate;
            form.citetext.value = vars.citetext;
            form.citenote.value = vars.citenote;
            $('#sourceTitle').html(vars.title);
            $('#lastspinner').hide();
        }
    });
    return false;
}