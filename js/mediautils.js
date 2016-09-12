// [ts] global functions and/or variables for JSLint
/*global ModalDialog */
var tnglitbox;

var gsControlName = "";
function FilePicker(sControl, collection, folders) {
    'use strict';
    gsControlName = sControl;

    var origsearch = document.getElementById(sControl + "_org");
    var lastsearch;
    var searchstring;
    var sendstring;
    var folderstr;
    if (origsearch) {
        lastsearch = document.getElementById(sControl + "_last");
        searchstring = document.getElementById(sControl);
        sendstring = searchstring.value;

        if (searchstring.value) {
            if (searchstring.value === origsearch.value || searchstring.value === lastsearch.value) {
                sendstring = "";
                lastsearch.value = "";
            } else {
                lastsearch.value = searchstring.value;
            }
        }
    } else {
        sendstring = "";
    }
    folderstr = folders ? '&folders=1' : '';
    var url = 'admin_filepicker.php?path=' + collection + '&searchstring=' + sendstring + folderstr;
    tnglitbox = new ModalDialog(url, {size: 'modal-lg'});
}

function ReturnFile(sFileName) {
    'use strict';
    $('#' + gsControlName).val(sFileName);
    tnglitbox.remove();
}

function moreFilepicker(args) {
    'use strict';
    $.ajax({
        url: 'admin_filepicker.php',
        data: args,
        dataType: 'html',
        success: function (req) {
            tnglitbox.d4.innerHTML = req;
        }
    });
    return false;
}

function ShowFile(sFileName) {
    'use strict';
    window.open(escape(sFileName), "File", "width=400,height=250,status=no,resizable=yes,scrollbars=yes");
}

function confirmDeleteFile(row, filepath) {
    'use strict';
    if (confirm(textSnippet('confdeletefile'))) {
        deleteIt('file', row);
    }
    return false;
}

function setDefault(linkID, entity) {
    'use strict';
    var params = {action: 'setdef2', entity: entity, media: media};
    $.ajax({
        url: 'ajx_updateorder.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            $('#sdef_' + entity).hide();
            $('#defc' + linkID).attr('checked', true);
        }
    });
    return false;
}

function populatePath(source, dest) {
    'use strict';
    var lastslash, temp;
    var lastperiod;
    var ext;
    dest.value = "";
    temp = source.value.replace(/\\/g, "/");
    lastslash = temp.lastIndexOf("/") + 1;
    if (lastslash > 0) {
        dest.value = source.value.slice(lastslash);
    } else {
        dest.value = source.value;
    }
    lastperiod = source.value.lastIndexOf(".") + 1;
    ext = source.value.slice(lastperiod);
    ext = ext.toUpperCase();
    if (ext === "JPG" || ext === "GIF" || ext === "PNG" || ext === "JPEG" || ext === "GIFF") {
        //imgmap.style.display='';
        document.form1.thumbcreate[1].style.visibility = 'visible';
    } else {
        imgmap.style.display = 'none';
        document.form1.thumbcreate[0].checked = 'true';
        document.form1.thumbcreate[1].style.visibility = 'hidden';
        document.form1.newthumb.style.visibility = 'visible';
        document.form1.thumbselect.style.visibility = 'visible';
    }
}

function prepopulateThumb() {
    'use strict';
    var path = document.form1.path;
    var lastslash = path.value.lastIndexOf("/") + 1;
    var lastperiod = path.value.lastIndexOf(".");
    var thumbpath = document.form1.thumbpath;

    thumbpath.value = "";
    if (lastslash) {
        thumbpath.value = path.value.slice(0, lastslash);
    }
    thumbpath.value = thumbpath.value + thumbPrefix;
    if (lastperiod >= 0) {
        thumbpath.value = thumbpath.value + path.value.slice(lastslash, lastperiod) + thumbSuffix + path.value.slice(lastperiod);
    } else {
        thumbpath.value = thumbpath.value + path.value.slice(lastslash) + thumbSuffix;
    }
}

function toggleCemSelect() {
    'use strict';
    $('#cemchoice').hide();
    $('#cemselect').show();

    return false;
}

function switchOnType(mtypeIndex) {
    'use strict';
    var mediatypeID = like[mtypeIndex];
    if (mediatypeID === "headstones") {
        $('#maprow').fadeIn(200);
        $('#linktocemrow').fadeIn(200);
        //new Effect.Appear('cemrow',{duration:.2});
        $('#hsplotrow').fadeIn(200);
        $('#hsstatrow').fadeIn(200);
        toggleCemSelect();
    } else {
        $('#maprow').fadeOut(200);
        $('#linktocemrow').fadeOut(200);
        //new Effect.Fade('cemrow',{duration:.2});
        $('#hsplotrow').fadeOut(200);
        $('#hsstatrow').fadeOut(200);
    }
    if (mediatypeID === "videos") {
        $('#vidrow1').fadeIn(200);
        $('#vidrow2').fadeIn(200);
    } else {
        $('#vidrow1').fadeOut(200);
        $('#vidrow2').fadeOut(200);
    }
    if (mtypeIndex && stmediatypes.indexOf(mtypeIndex) === -1) {
        if ($('#editmediatype').length) {
            $('#editmediatype').show();
        }
        if ($('#delmediatype').length) {
            $('#delmediatype').show();
        }
    } else {
        if ($('#editmediatype').length) {
            $('#editmediatype').hide();
        }
        if ($('#delmediatype').length) {
            $('#delmediatype').hide();
        }
    }
}

function toggleMediaURL() {
    'use strict';
    var abspath = document.getElementById("abspathrow");
    var path = document.getElementById("pathrow");
    var img = document.getElementById("imgrow");
    var imgmap = document.getElementById("imgmaprow");

    if (document.form1.abspath.checked) {
        abspath.style.display = '';
        path.style.display = 'none';
        img.style.display = 'none';
        if (imgmap) {
            imgmap.style.display = 'none';
        }
        if (document.form1.thumbcreate) {
            document.form1.thumbcreate[0].checked = 'true';
            document.form1.thumbcreate[1].style.visibility = 'hidden';
        }
        document.form1.newthumb.style.visibility = 'visible';
        document.form1.thumbselect.style.visibility = 'visible';
    } else {
        abspath.style.display = 'none';
        path.style.display = '';
        img.style.display = '';
        if (imgmap) {
            imgmap.style.display = '';
        }
        if (document.form1.thumbcreate) {
            document.form1.thumbcreate[1].style.visibility = 'visible';
        }
    }
}

var firstclick = true;
var x1, y1, x2, y2, radius;
var Coordinate_X_InImage;
var Coordinate_Y_InImage;
Coordinate_X_InImage = Coordinate_Y_InImage = 0;

function imageClick(photoID) {
    'use strict';
    var shapeobj = document.form1.shape;
    var shape;

    //GetCoordinatesInImage();

    if (shapeobj[0].checked) {
        shape = shapeobj[0].value;
    } else {
        shape = shapeobj[1].value;
    }
    if (firstclick) {
        x1 = Coordinate_X_InImage;
        y1 = Coordinate_Y_InImage;
        firstclick = false;
    } else {
        if (shape === "circle") {
            x2 = "";
            y2 = "";
            radius = Math.ceil(Math.sqrt(Math.pow(Coordinate_X_InImage - x1, 2) + Math.pow(Coordinate_Y_InImage - y1, 2)));
        } else {
            x2 = Coordinate_X_InImage;
            y2 = Coordinate_Y_InImage;
            radius = "";
        }
        findItem('I', 'imagemap', '', assignedbranch);
        firstclick = true;
    }
}

function GetCoordinatesInImage(evt) {
    'use strict';
    if (window.event && !window.opera && typeof event.offsetX === "number") {
        // IE 5+
        Coordinate_X_InImage = event.offsetX;
        Coordinate_Y_InImage = event.offsetY;
    } else if (document.addEventListener && evt && typeof evt.pageX === "number") {
        // Mozilla-based browsers
        var Element = evt.target;
        var CalculatedTotalOffsetLeft = 0;
        var CalculatedTotalOffsetTop = 0;
        while (Element.offsetParent) {
            CalculatedTotalOffsetLeft += Element.offsetLeft;
            CalculatedTotalOffsetTop += Element.offsetTop;
            Element = Element.offsetParent;
        }
        Coordinate_X_InImage = evt.pageX - CalculatedTotalOffsetLeft;
        Coordinate_Y_InImage = evt.pageY - CalculatedTotalOffsetTop;
    }
}

function init() {
    'use strict';
    if (document.getElementById('pic')) {
        if (document.addEventListener) {
            document.getElementById('pic').addEventListener("mousedown", GetCoordinatesInImage, false);
        } else if (window.event && document.getElementById) {
            document.getElementById('pic').onmousedown = GetCoordinatesInImage;
        }
    }
}

function generateThumbs(form) {
    'use strict';
    $('#thumbresults').html("");
    $('#thumbresults').hide();
    var regenerate = form.regenerate.checked ? 1 : "";
    var repath = form.repath.checked ? 1 : "";
    $('#thumbspin').show();
    var params = {regenerate: regenerate, repath: repath};
    $.ajax({
        url: 'admin_generatethumbs.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            $('#thumbresults').html(req);
            $('#thumbresults').fadeIn(400);
            $('#thumbspin').hide();
        }
    });
    return false;
}

function assignDefaults(form) {
    'use strict';
    $('#defresults').html("");
    $('#defresults').hide();
    var overwrite = form.overwritedefs.checked ? 1 : "";
    $('#defspin').show();
    var params = {overwritedefs: overwrite};
    $.ajax({
        url: 'admin_defphotos.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            $('#defresults').html(req);
            $('#defresults').fadeIn(400);
            $('#defspin').hide();
        }
    });
    return false;
}

function attemptDelete(entity, entityname) {
    'use strict';
    if (entity.options[entity.selectedIndex].value.length === 0) {
        alert(textSnippet('nothingtodelete'));
    } else if (confirm(textSnippet('confdeleteentity') + ' ' + entityname + '?')) {
        params = {entity: entityname, delitem: entity.options[entity.selectedIndex].value};
        $.ajax({
            url: 'admin_deleteentity.php',
            data: params,
            dataType: 'html',
            success: function (req) {
                RemovefromDisplay(entity);
                entity.selectedIndex = 0;
            }
        });
    }
}

function addEntity(form) {
    'use strict';
    if (form.newitem.value.length === 0) {
        alert(textSnippet('pleaseenter') + ' ' + form.entity.value);
    } else {
        var params = $(form).serialize();
        $.ajax({
            url: 'admin_addentity.php',
            data: params,
            dataType: 'html',
            success: function (req) {
                $('#entitymsg').html(req);
                var entity = form.entity.value === 'state' ? document.form1.state : document.form1.country;
                var i = entity.options.length;
                if (navigator.appName === "Netscape") {
                    entity.options[i] = new Option(form.newitem.value, form.newitem.value, false, false);
                } else if (navigator.appName === "Microsoft Internet Explorer") {
                    entity.add(document.createElement("OPTION"));
                    entity.options[i].text = form.newitem.value;
                    entity.options[i].value = form.newitem.value;
                }
                entity.selectedIndex = i;
                form.newitem.value = '';
            }
        });
    }
    return false;
}

function addCollection(form) {
    'use strict';
    if (form.collid.value === "") {
        alert(textSnippet('entercollid'));
    } else if (form.display.value === "") {
        alert(textSnippet('entercolldisplay'));
    } else if (form.path.value === "") {
        alert(entercollpath);
    } else if (form.icon.value === "") {
        alert(textSnippet('entercollicon'));
    } else {
        $('#cerrormsg').hide();
        var params = $(form).serialize();
        $.ajax({
            url: 'admin_addcollection.php',
            data: params,
            type: 'POST',
            dataType: 'html',
            success: function (req) {
                if (req !== "0") {
                    var field = document.form1.mediatypeID;
                    var i = field.options.length;
                    if (navigator.appName === "Netscape") {
                        field.options[i] = new Option(form.display.value, req, false, false);
                    } else if (navigator.appName === "Microsoft Internet Explorer") {
                        field.add(document.createElement("OPTION"));
                        field.options[i].text = form.display.value;
                        field.options[i].value = req;
                    }
                    field.selectedIndex = i;
                    if (allow_edit) {
                        $('#editmediatype').show();
                    }
                    if (allow_delete) {
                        $('#delmediatype').show();
                    }
                    if (!manage) {
                        var likeidx = form.liketype.options[form.liketype.selectedIndex].value;
                        like[form.collid.value] = likeidx;
                        switchOnType(form.collid.value);
                    }
                    tnglitbox.remove();
                } else {
                    $('#cerrormsg').show();
                }
            }
        });
    }
    return false;
}

function editMediatype(field) {
    'use strict';
    var mediatypeID = field.options[field.selectedIndex].value;
    var fieldname = field.name;
    tnglitbox = new ModalDialog('admin_editcollection.php?field=' + fieldname + '&mediatypeID=' + mediatypeID + '&selidx=' + field.selectedIndex);
}

function updateCollection(form) {
    'use strict';
    if (form.display.value === "") {
        alert(textSnippet('entercolldisplay'));
    } else if (form.path.value === "") {
        alert(entercollpath);
    } else if (form.icon.value === "") {
        alert(textSnippet('entercollicon'));
    } else {
        var params = $(form).serialize();
        $.ajax({
            url: 'admin_updatecollection.php',
            data: params,
            type: 'POST',
            dataType: 'html',
            success: function (req) {
                var field = eval('document.form1.' + form.field.value);
                field.options[form.selidx.value].text = form.display.value;
                tnglitbox.remove();
                if (!manage) {
                    var likeidx = form.liketype.options[form.liketype.selectedIndex].value;
                    like[form.collid.value] = likeidx;
                    switchOnType(form.collid.value);
                }
            }
        });
    }
    return false;
}

function confirmDeleteMediatype(mediatypeID) {
    'use strict';
    if (confirm(textSnippet('confmtdelete'))) {
        var params = {t: 'mediatype', id: mediatypeID.options[mediatypeID.selectedIndex].value};
        $.ajax({
            url: 'ajx_delete.php',
            data: params,
            dataType: 'html',
            success: function (req) {
                if (navigator.appName === "Netscape") {
                    mediatypeID.options[mediatypeID.selectedIndex] = null;
                } else if (navigator.appName === "Microsoft Internet Explorer") {
                    mediatypeID.options.remove(mediatypeID.selectedIndex);
                }
                mediatypeID.selectedIndex = 0;
                toggleHeadstoneCriteria('');
            }
        });
    }
}

function updateMediaOrder(event, ui) {
    'use strict';
    var linklist = removePrefixFromArray($('#orderdivs').sortable('toArray'), 'orderdivs_');

    $('input.movefields').each(function (index, item) {
        item.value = index + 1;
    });

    var params = {sequence: linklist.join(','), album: album, action: orderaction};
    $.ajax({
        url: 'ajx_updateorder.php',
        data: params,
        dataType: 'html'
    });
}

// form startup
function startMediaSort() {
    'use strict';
    $('#orderdivs').sortable({
        helper: 'clone',
        axis: 'y',
        scroll: false,
        items: '.sortrow',
        update: updateMediaOrder
    });
}

function moveItemInList(elname, pos) {
    'use strict';
    var el = $('#orderdivs_' + elname);
    var sortrows = $('div.sortrow');
    var current = 1;
    var count = 0;
    var found = false;
    var needAppend = false;
    $.each(sortrows, function (index, item) {
        if (item.id === 'orderdivs_' + elname) {
            found = true;
        }
        if (!found) {
            current += 1;
        }
        count += 1;
    });
    var posnum = parseInt(pos, 10);
    if (current > posnum) {
        posnum -= 1;
    } else if (posnum >= count) {
        posnum -= 1;
        needAppend = true;
    }
    var targetrow = $(sortrows[posnum]);
    if (el !== targetrow) {
        var newnode = el.clone(true);

        var sourcePos = el.offset();
        var targetPos = targetrow.offset();

        var targetVert = sourcePos.top - targetPos.top;
        el.animate({'top': '-=' + targetVert}, 1000, 'swing', function () {
            el.remove();
            if (needAppend) {
                $('#orderdivs').append(newnode);
            } else {
                newnode.insertBefore(targetrow);
            }
            $('#orderdivs').sortable('destroy');
            startMediaSort();
            updateMediaOrder('orderdivs');
        });
    }
    return false;
}

function handleMediaEnter(elname, pos, e) {
    'use strict';
    var keycode;
    if (window.event) {
        keycode = window.event.keyCode;
    } else if (e) {
        keycode = e.which;
    } else {
        return true;
    }
    if (keycode === 13) {
        moveItemInList(elname, pos);
        return false;
    }
    return true;
}

function removeFromSort(type, link) {
    'use strict';
    var params = {type: type, link: link, action: 'remsort'};
    $.ajax({
        url: 'ajx_updateorder.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            item = parseInt(req, 10);
            $('#orderdivs_' + item).fadeOut(400, function () {
                $('#orderdivs_' + item).remove();
            });
        }
    });
    return false;
}

function openFindAny(linktype) {
    'use strict';
    var linktypes = ['I', 'F', 'S', 'R', 'L'];

    $.each(linktypes, function (index, thistype) {
        if (thistype !== linktype && $('#findform' + thistype).css('display') !== 'none') {
            $('#findform' + thistype).hide();
        }
    });
    if ($('#findform' + linktype).css('display') === 'none') {
        $('#findform' + linktype).fadeIn(400);
    }
}

function switchLinktypes(select) {
    'use strict';
    if (findopen) {
        openFindAny(select.options[select.selectedIndex].value);
        $('#newlines').html(resheremsg);
        $('#find2').children().each(function (index, item) {
            if (item.type === "text") {
                item.value = "";
            }
        });
    }
}

function resetFindFields(section, fields) {
    'use strict';
    $('#newlines').html('');
    var field;
    var i;
    for (i = 0; i < fields.length; i += 1) {
        field = eval("document.find2." + fields[i]);
        field.value = '';
    }
    $('#' + section).fadeOut(200);
}

function toggleShow(checkbox) {
    'use strict';
    var toggle = checkbox.checked ? 0 : 1;
    var medialinkID = checkbox.name.substr(4);
    var params = {medialinkID: medialinkID, toggle: toggle, action: 'show'};
    $.ajax({
        url: 'ajx_updateorder.php',
        data: params
    });
}

function toggleDefault(checkbox, entityID) {
    'use strict';
    var toggle = checkbox.checked ? 1 : 0;
    var medialinkID = checkbox.name.substr(4);
    var params = {medialinkID: medialinkID, entity: entityID, toggle: toggle, action: 'setdef3'};
    $.ajax({
        url: 'ajx_updateorder.php',
        data: params
    });
}
