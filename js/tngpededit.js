var newpersongender = '';
var spouseorder = '';
function editPerson(personID, slot, gender) {
    if (personID) {
        allow_cites = true;
        allow_notes = true;
        newpersongender = gender;
        if (gender === 'M') {
            spouseorder = 'husborder';
        } else if (gender === 'F') {
            spouseorder = 'wifeorder';
        }
        persfamID = personID;
        tnglitbox = new ModalDialog('peopleEdit.modal.php?personID=' + personID + '&slot=' + slot, {size: 'modal-lg'});
        startSortPerson();
    }
    return false;
}

var nplitbox;
var activeidbox = null;
var activenamebox = null;
function newPerson(gender, type, father, familyID) {
    allow_cites = false;
    allow_notes = false;
    newpersongender = gender;
    nplitbox = new ModalDialog('peopleAdd.modal.php?gender=' + gender + '&type=' + type + '&father=' + father + '&familyID=' + familyID, {size: 'modal-lg'});
    generateIDajax('person', 'personID');
    $('#firstname').focus();
    return false;
}

function addPerson(form) {
    var params = $(form).serialize();
    var perstype = form.type.value;
    if (perstype === "child") {
        var childcount = parseInt($('#childcount').html());
        params += '&order=' + (childcount + 1);
    }
    $.ajax({
        url: 'peopleAddFormAction.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            if (req.indexOf('error') >= 0) {
                var vars = eval('(' + req + ')');
                alert(vars.error);
            } else {
                nplitbox.remove();
                if (perstype === "child") {
                    $('#childrenlist').append(req);
                    $('#child_' + form.personID.value).fadeIn(400);
                    $('#childcount').html(childcount + 1);
                    startSortFamily();
                } else if (perstype === "spouse") {
                    updateNameBox(req);
                }
            }
        }
    });
    return false;
}

function updateNameBox(jsonvars) {
    if (newpersongender === 'M') {
        activeidbox = 'husband';
        activenamebox = "husbnameplusid";
    } else if (newpersongender === 'F') {
        activeidbox = 'wife';
        activenamebox = "wifenameplusid";
    }
    var vars = eval('(' + jsonvars + ')');
    $('#' + activenamebox).val(vars.name + ' - ' + vars.id);
    $('#' + activenamebox).effect('highlight', {}, 400);
    $('#' + activeidbox).val(vars.id);
}

function updatePerson(form, slot) {
    checkDate(form.birthdate);
    checkDate(form.altbirthdate);
    checkDate(form.deathdate);
    checkDate(form.burialdate);
    checkDate(form.baptdate);
    checkDate(form.endldate);
    var params = $(form).serialize();
    $.ajax({
        url: 'peopleEditFormAction.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            var thispers = form.personID.value;
            if (people[thispers].backperson)
                var params = "&backpers1=" + people[thispers].backperson + "&famc1=" + people[people[thispers].backperson].famc;
            else
                var params = "&personID=" + thispers;
            if (slot) {
                var getgens = slot >= slotceiling_minus1 ? 1 : 2;
                fetchData(params, getgens);
            } else {
                updateNameBox(req);
            }
            tnglitbox.remove();
        }
    });
    return false;
}

var persfamID;
function editFamily(familyID, slot, lastperson) {
    allow_cites = true;
    allow_notes = true;
    persfamID = familyID;
    tnglitbox = new ModalDialog('familiesEdit.modal.php?familyID=' + familyID + '&lastperson=' + lastperson + '&slot=' + slot, {size: 'modal-lg'});
    startSortFamily();
    return false;
}

function newFamily(slot, child) {
    allow_cites = false;
    allow_notes = false;
    tnglitbox = new ModalDialog('familiesAdd.modal.php?child=' + child + '&slot=' + slot, {size: 'modal-lg'});
    generateIDajax('family', 'familyID');
    return false;
}

function updateFamily(form, slot, script) {
    var params = $(form).serialize();
    $.ajax({
        url: script,
        data: params,
        datatype: 'html',
        success: function (req) {
            var getgens = 1;
            var startfam = form.familyID.value;

            var scm1 = slotceiling_minus1;
            while (scm1 > slot) {
                getgens += 1;
                scm1 /= 2;
                scm1 = Math.floor(scm1);
            }
            var params = {generations: getgens, display: display, backpers1: form.lastperson.value, famc1: startfam};
            $.ajax({
                url: 'ajx_pedjson.php?',
                data: params,
                dataType: 'json',
                success: function (req) {
                    addNewPeople(req);
                    if (script === "familiesAddFormAction.php")
                        people[form.lastperson.value].famc = startfam;
                    else {
                        var notfound = true;
                        if (families[startfam] && families[startfam].children) {
                            for (var i = 0; i < families[startfam].children.length && notfound; i++) {
                                if (families[startfam].children[i].childID === form.lastperson.value)
                                    notfound = false;
                            }
                        }
                        if (notfound)
                            people[form.lastperson.value].famc = -1;
                    }
                    displayChart();
                }
            });
            tnglitbox.remove();
        }
    });
    return false;
}

function startSortFamily() {
    Sortable.create('childrenlist', {tag: 'div', onUpdate: updateChildrenOrder});
    Position.includeScrollOffsets = true;
}

function startSortPerson() {
    if ($('div#parents div').length > 1)
        $('#parents').sortable({tag: 'div', update: updateParentsOrder});
    if ($('div#spouses div').length > 1)
        $('#spouses').sortable({tag: 'div', update: updateSpousesOrder});
    //not sure if the following line needs to be converted to $
    //Position.includeScrollOffsets = true;
}

function updateParentsOrder(id) {
    var parentlist = $('#parents').sortable('toArray');

    var params = {sequence: parentlist.join(','), action: 'parentorder', personID: persfamID};
    $.ajax({
        url: 'ajx_updateorder.php',
        data: params,
        dataType: 'html'
    });
}

function updateSpousesOrder(id) {
    var spouselist = $('#spouses').sortable('toArray');

    var params = {sequence: spouselist.join(','), action: 'spouseorder', spouseorder: spouseorder};
    $.ajax({
        url: 'ajx_updateorder.php',
        data: params,
        dataType: 'html'
    });
}

function unlinkParents(familyID) {
    if (confirm(textSnippet('confunlinkchild'))) {
        var params = {action: 'parentunlink', familyID: familyID, personID: persfamID};
        $.ajax({
            url: 'ajx_updateorder.php',
            data: params,
            dataType: 'html',
            success: function (req) {
                $('#parents_' + familyID).fadeOut(300, function () {
                    $('#parents_' + familyID).remove();
                    $('#parentcount').html(parseInt($('#parentcount').html()) - 1);
                });
            }
        });
    }
    return false;
}

function updateChildrenOrder(id) {
    var childlist = $('#childrenlist').sortable('toArray');

    var params = {sequence: childlist.join(','), action: 'childorder', familyID: persfamID};
    $.ajax({
        url: 'ajx_updateorder.php',
        data: params,
        dataType: 'html'
    });
}

function unlinkChild(personID, action) {
    var confmsg = action === "child_delete" ? textSnippet('confdeletepers') : textSnippet('confremchild');
    if (confirm(confmsg)) {
        var params = {personID: personID, familyID: persfamID, desc: tree, t: action};
        $.ajax({
            url: 'ajx_delete.php',
            data: params,
            dataType: 'html',
            success: function (req) {
                $('#child_' + personID).fadeOut(300, function () {
                    $('#child_' + personID).remove();
                    $('#childcount').html(parseInt($('#childcount').html()) - 1);
                });
            }
        });
    }
    return false
}

function removeSpouse(spouse, spousedisplay) {
    if (spouse.value) {
        spouse.value = '';
        spousedisplay.value = '';
    }
}

function validateFamily(form, slot) {
    form.familyID.value = TrimString(form.familyID.value);
    if (form.familyID.value.length === 0) {
        alert(textSnippet('enterfamilyid'));
        return false;
    }
    return updateFamily(form, slot, 'familiesAddFormAction.php');
}

function validatePerson(form) {
    form.personID.value = TrimString(form.personID.value);
    if (form.personID.value.length === 0) {
        alert(textSnippet('enterpersonid'));
        return false;
    }
    return addPerson(form);
}

function generateIDajax(type, dest) {
    var params = {type: type};
    $.ajax({
        url: 'admin_generateID.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            $('#' + dest).val(req);
        }
    });
}

function checkIDajax(checkID, type, dest) {
    var params = {checkID: checkID, type: type};
    $.ajax({
        url: 'admin_checkID.php',
        data: params,
        dataType: 'html',
        success: function (req) {
            $('#' + dest).html(req);
        }
    });
}