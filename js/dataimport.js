// [ts] global functions and/or variables for JSLint
/*global ModalDialog, FilePicker, textSnippet */
var branchcounts, branches, helpLang;
var saveimport;

var tnglitbox;
var timecheck;
var lastptr;
var treeval;

var done = false;
var started = false;
var suspended = false;
var submitted = false;

function resetimport() {
    'use strict';
    done = false;
    started = false;
    suspended = false;
    submitted = true;
}

function suspendimport() {
    'use strict';
    if (document.all) {
        document.execCommand("stop");
    } else {
        window.stop();
    }
    $('#importmsg').html(textSnippet('stopped'));
    suspended = true;
    return false;
}

function stopimport() {
    'use strict';
    suspendimport();
    done = true;
}

function checkFile(form) {
    'use strict';
    var rval = true;
    var treeselect = document.form1.tree1;
    if (form.remotefile.value.length === 0 && form.database.value.length === 0) {
        alert(textSnippet('selectimportfile'));
        rval = false;
    } else if (form.tree1.selectedIndex === 0 && form.tree1.options[form.tree1.selectedIndex].value === "" && !form.eventsonly.checked) {
        alert(textSnippet('selectdesttree'));
        rval = false;
    }

    if (rval && form.target) {
        if (form.action.indexOf("dataImportGedcomFormAction.php") >= 0) {
            resetimport();
            var popup = '<div class="impcontainer">\n';
            popup += '<div class="impheader"><h4 id="importmsg">';
            if (form.remotefile.value.length) {
                popup += textSnippet('uploading') + ' ' + form.remotefile.value;
            } else {
                popup += textSnippet('opening') + ' ' + form.database.value;
            }
            popup += '... &nbsp;<img src="img/spinner.gif"></h4></div>\n';
            popup += '<div id="impdata" style="visibility:hidden">\n';
            popup += '<p id="recordcount">\n<span class="imp">&nbsp;<span class="implabel">' + textSnippet('people') + ': </span><span id="personcount" class="impctr">0</span></span>\n';
            popup += '<div class="imp">&nbsp;<span class="implabel">' + textSnippet('families') + ': </span><span id="familycount" class="impctr">0</span></div>\n';
            popup += '<div class="imp">&nbsp;<span class="implabel">' + textSnippet('sources') + ': </span><span id="sourcecount" class="impctr">0</span></div>\n';
            popup += '<div class="imp">&nbsp;<span class="implabel">' + textSnippet('notes') + ': </span><span id="notecount" class="impctr">0</span></div>\n';
            popup += '<div class="imp">&nbsp;<span class="implabel">' + textSnippet('media') + ': </span><span id="mediacount" class="impctr">0</span></div>\n';
            popup += '<div class="imp">&nbsp;<span class="implabel">' + textSnippet('places') + ': </span><span id="placecount" class="impctr">0</span></div>\n</p><br><br>';
            popup += '<div class="progcontainer"><div id="progress" class="emptybar">\n<div id="bar" class="colorbar"></div>\n</div>\n</div>\n';
            popup += '</div>\n';
            popup += '<br><div id="implinks"><a href="#" onclick="return suspendimport();">' + textSnippet('stop') + '</a>';
            if (saveimport === "1") {
                treeval = treeselect.options[treeselect.selectedIndex].value;
                popup += ' |  <a href="dataImportGedcomFormAction.php?tree=' + treeval + '&resuming=1" id="resumelink" target="results" onclick="resumeimport();">' + textSnippet('resume') + '</a>';
            }
            popup += '</div>\n<div id="errormsg"></div>';
            popup += '</div>';

            tnglitbox = new ModalDialog(popup, {
                type: 'alert', // [ts] no alert type in ModalDialog
                onremove: function () {
                    if (!done) {
                        stopimport();
                    }
                }
            });
            lastptr = '';
            //may not be necessary with "onload"
            //timecheck = setTimeout(checkIfDone, checksecs);
        } else {
            document.form1.target = "main";
        }
    }
    return rval;
}

function iframeLoaded() {
    'use strict';
    if (submitted && started && !done && !suspended) {
        //restart if that is an option
        var treeselect = document.form1.tree1;
        self.frames[0].location.href = "dataImportGedcomFormAction.php?tree=" + treeselect.options[treeselect.selectedIndex].value + "&resuming=1";
    }
}

function resumeimport() {
    'use strict';
    $('#importmsg').html(textSnippet('reopen') + ' ' + treeval + '...');
    suspended = false;
}

function checkIfDone() {
    'use strict';
    if (started && !done && !suspended) {
        if (lastptr === $('bar').style.width) {
            var treeselect = document.form1.tree1;
            self.frames[0].location.href = "dataImportGedcomFormAction.php?tree=" + treeselect.options[treeselect.selectedIndex].value + "&resuming=1";
        } else {
            lastptr = $('bar').style.width;
            timecheck = setTimeout(checkIfDone, checksecs);
        }
    }
}

function removeFile(filename) {
    'use strict';
    var params = {filename: filename};
    $.ajax({
        url: 'admin_deletefile.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            $('#toremove').html(req);
        }
    });
    return false;
}

function alphaNumericCheck(string) {
    'use strict';
    var regex = /^[0-9A-Za-z_-]+$/;
    return regex.test(string);
}

function validateTreeForm(form) {
    'use strict';
    if (form.gedcom.value.length === 0) {
        alert(textSnippet('entertreeid'));
    } else if (!alphaNumericCheck(form.gedcom.value)) {
        alert(textSnippet('alphanum'));
    } else if (form.treename.value.length === 0) {
        alert(textSnippet('entertreename'));
    } else {
        var params = $(form).serialize();
        $.ajax({
            url: 'admin_addtree.php',
            data: params,
            dataType: 'html',
            success: function (req) {
                if (req === "1") {
                    // tnglitbox.remove();
                    $('#myModal').modal('hide');
                    var treeselect = document.form1.tree1;
                    var i = treeselect.options.length;
                    if (navigator.appName === "Netscape") {
                        treeselect.options[i] = new Option(form.treename.value, form.gedcom.value, false, false);
                    } else if (navigator.appName === "Microsoft Internet Explorer") {
                        treeselect.add(document.createElement("OPTION"));
                        treeselect.options[i].text = form.treename.value;
                        treeselect.options[i].value = form.gedcom.value;
                    }
                    treeselect.selectedIndex = i;
                } else {
                    $('#treemsg').html(req);
                }
            }
        });
    }
    return false;
}

function toggleAppenddiv(flag) {
    'use strict';
    if (flag) {
        $('#appenddiv').fadeIn(200);
    } else {
        $('#appenddiv').fadeOut(200);
    }
}

function toggleNorecalcdiv(flag) {
    'use strict';
    if (flag) {
        $('#norecalcdiv').fadeIn(200);
    } else {
        $('#norecalcdiv').fadeOut(200);
    }
}

function toggleSections(flag) {
    'use strict';
    $('#desttree').toggle(400);
    $('#replace').toggle(400);
    $('#ioptions').toggle(400);
    document.form1.action = flag ? 'dataImportGedcomFormActionOnlyEventTypes.php' : 'dataImportGedcomFormAction.php';
    if (flag) {
        document.form1.allevents.checked = "";
    }
}

// [ts] duplicate of code in mediautils
var gsControlName = "";
function filePicker(sControl, collection, folders) {
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

$('#gedselect').on('click', function () {
    'use strict';
    FilePicker('database', 'gedcom');
});

$('#allevents').on('click', function () {
    'use strict';
    if (document.form1.allevents.checked && document.form1.eventsonly.checked) {
        document.form1.eventsonly.checked = '';
        toggleSections(false);
    }
});

$('#eventsonly').on('click', function () {
    'use strict';
    toggleSections(this.checked);
});

$('#addnewtree').on('click', function () {
    'use strict';
    var url = 'admin_dataImportNewTree.php?beforeimport=yes&helplang=' + helpLang;

    tnglitbox = new ModalDialog(url);
});

$('#allcurrentdata').on('click', function () {
    'use strict';
    document.form1.norecalc.checked = false;
    toggleNorecalcdiv(0);
    toggleAppenddiv(0);
});

$('#matchingonly').on('click', function () {
    'use strict';
    toggleNorecalcdiv(1);
    toggleAppenddiv(0);
});

$('#donotreplace').on('click', function () {
    'use strict';
    document.form1.norecalc.checked = false;
    toggleNorecalcdiv(0);
    toggleAppenddiv(0);
});

$('#appendall').on('click', function () {
    'use strict';
    document.form1.norecalc.checked = false;
    toggleNorecalcdiv(0);
    toggleAppenddiv(1);
});

$('#oldimport').on('click', function () {
    'use strict';
    if (document.form1.target) {
        document.form1.target = "";
    } else {
        document.form1.target = "results";
    }
});

function showBranches(treeidx) {
    'use strict';
    if (branchcounts[treeidx] === 1) {
        $('#branch1div').html('<select name="branch1" id="branch1">' + branches[treeidx] + '</select>');
        $('#destbranch').fadeIn(200);
    } else {
        $('#destbranch').fadeOut(200);
    }
}

function getBranches(treeselect, selected) {
    'use strict';
    if (selected) {
        var tree = treeselect.options[treeselect.selectedIndex].value;
        var treeidx = tree ? tree : 'none';

        if (branchcounts[treeidx] === -1) {
            var params = {tree: tree};
            $.ajax({
                url: 'admin_branchoptions.php',
                data: params,
                dataType: 'html',
                success: function (req) {
                    branchcounts[treeidx] = req === "0" ? 0 : 1;
                    if (branchcounts[treeidx]) {
                        branches[treeidx] = req;
                    }
                    showBranches(treeidx);
                }
            });
        } else {
            showBranches(treeidx);
        }
    } else {
        $('#destbranch').fadeOut(200);
    }
}