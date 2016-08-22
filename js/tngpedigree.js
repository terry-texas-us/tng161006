for (var h = 1; h < slotceiling; h++) {
    eval('var timer' + h + '=false');
}
var timerleft = false;

function setPopup(slot, tall, high) {
    eval("timer" + slot + "=setTimeout(\"showPopup(" + slot + "," + tall + "," + high + ")\",150);");
}

function showPopup(slot, tall, high) {
// hide any other currently visible popups
    if (lastpopup) {
        cancelTimer(lastpopup);
        hidePopup(lastpopup);
    }
    lastpopup = slot;

// show current
    var ref = $("#popup" + slot);
    var box = $("#box" + slot);
    ref.html(getPopup(slot));

    var vOffset, hOffset, hDisplace;

    if (tall + high < 0)
        vOffset = 0;
    else {
        vOffset = tall + high + pedborderwidth;
        var vDisplace = box.position().top + high + pedborderwidth + ref.height() - $('#outer').height() + 20; //20 is for the scrollbar
        if (vDisplace > 0)
            vOffset -= vDisplace;
    }
    hDisplace = box.position().left + ref.width() - $('#outer').width();
    if (hDisplace > 0)
        ref.offset({left: box.offset().left - hDisplace});
    ref.css('top', vOffset);
    ref.css('z-index', 8);
    ref.css('visibility', 'visible');
}

function hidePopup(slot) {
    var ref = $("#popup" + slot);
    if (ref.length) {
        ref.css('visibility', 'hidden');
    }
    eval("timer" + slot + "=false;");
}

function showBackPopup() {
    if (lastpopup) {
        cancelTimer(lastpopup);
        hidePopup(lastpopup);
    }
    lastpopup = '';

    var ref = $("#popupleft");
    ref.html(getBackPopup());

    if (ref.css('visibility') !== "show" && ref.css('visibility') !== "visible") {
        ref.css('z-index', 8);
        ref.css('visibility', 'visible');
    }
}

function setTimer(slot) {
    eval("timer" + slot + "=setTimeout(\"hidePopup('" + slot + "')\",popuptimer);");
}

function cancelTimer(slot) {
    eval("clearTimeout(timer" + slot + ");");
    eval("timer" + slot + "=false;");
}

function setFirstPerson(newperson) {
    if (newperson != firstperson) {
        firstperson = newperson;
        if (!tngprint) {
            var params = 'personID=' + newperson + '&parentset=' + parentset + '&generations=' + generations;
            $("#stdpedlnk").attr('href', 'pedigree.php?' + params + '&display=standard');
            $("#compedlnk").attr('href', 'pedigree.php?' + params + '&display=compact');
            $("#boxpedlnk").attr('href', 'pedigree.php?' + params + '&display=box');
            $("#textlnk").attr('href', 'pedigreetext.php?' + params);
            $("#ahnlnk").attr('href', 'ahnentafel.php?' + params);
            $("#extralnk").attr('href', 'extrastree.php?' + params + '&showall=1');
        }
    }
}

function fetchData(famParams, newgens) {
    var loading = $("#loading");
    loading.css('visibility', 'visible');

    var strParams = "generations=" + newgens + '&display=' + display + famParams;
    var loader1 = new net.ContentLoader('ajx_pedjson.php?', FillChart, null, "POST", strParams);
}

function getNewChart(personID, newgens, newparentset) {
    setFirstPerson(personID);
    fetchData('&personID=' + personID + '&parentset=' + newparentset, newgens);
}

function getNewFamilies(famParams, newgens, gender) {
    //set first person
    var nextfamily = people[firstperson].famc;
    if (gender == 'F')
        setFirstPerson(families[nextfamily].wife);
    else
        setFirstPerson(families[nextfamily].husband);

    if (famParams)
        fetchData(famParams, newgens);
    else
        DisplayChart();
}

function goBack(backperson) {
    setFirstPerson(backperson);
    DisplayChart();
}

function addNewPeople(incoming) {
    var vars = eval('(' + incoming + ')');
    if (vars.people) {
        for (var i = 0; i < vars.people.length; i++) {
            //var p = new Person(vars.people[i]);
            var p = vars.people[i];
            var pID = vars.people[i].personID;
            people[pID] = p;
        }
    }
    if (vars.families) {
        for (var i = 0; i < vars.families.length; i++) {
            var family = vars.families[i];
            var famID = vars.families[i].famID;
            families[famID] = family;
        }
    }
}

function getGenderIcon(gender) {
    var genderstr, icon = "";
    var valign = display == "compact" ? -2 : -1;
    if (gender) {
        if (gender == 'M')
            genderstr = "male";
        else if (gender == 'F')
            genderstr = "female";
        if (genderstr)
            icon = " <img src=\"img/tng_" + genderstr + ".gif\" width=\"11\" height=\"11\" alt=\"" + genderstr + "\" style=\"vertical-align: " + valign + "px;\">";
    }

    return icon;
}

function FillChart() {
    addNewPeople(this.req.responseText);
    var loading = $("#loading");
    DisplayChart();
    loading.css('visibility', 'hidden');
}

function DisplayChart() {
    toplinks = "";
    botlinks = "";
    endslotctr = 0;
    endslots = new Array();

    var slot = 1;
    FillSlot(slot, firstperson, 0);

    var offpage;
    var leftarrow = $('#leftarrow');
    if (people[firstperson].backperson) {
        leftarrow.html('<a href="javascript:goBack(' + "'" + people[firstperson].backperson + "'" + ');">' + leftarrowimg + '</a>');
        leftarrow.css('visibility', 'visible');
    } else {
        var gotkids = 0;
        var activeperson = people[firstperson];
        var spFamID;
        if (activeperson.spfams) {
            for (var i = 0; i < activeperson.spfams.length; i++) {
                spFamID = activeperson.spfams[i].spFamID;
                if (families[spFamID].children) {
                    gotkids = 1;
                    break;
                }
            }
        }
        if (gotkids) {
            leftarrow.html('<a href="javascript:showBackPopup();">' + leftarrowimg + '</a>');
            leftarrow.css('visibility', 'visible');
        } else {
            leftarrow.html('');
            leftarrow.css('visibility', 'hidden');
        }
    }

    topparams = getParams(toplinks);
    botparams = getParams(botlinks);

    for (var i = 0; i < endslots.length; i++) {
        offpage = $('#offpage' + endslots[i]);
        offpage.css('visibility', 'visible');
    }
}

function FillSlot(slot, currperson, lastperson) {
    var currentBox = document.getElementById('box' + slot);
    var content = "";
    var slotperson, husband, wife;

    if (people[currperson])
        slotperson = people[currperson];
    else {
        slotperson = new Object;
        slotperson.famc = -1;
        slotperson.personID = 0;
    }
    slots[slot] = slotperson;
    var dnarrow = $('#downarrow' + slot);
    var popup = $('#popup' + slot);
    var popupcontent = "";
    var shadow, border, icons = "";

    if (slotperson.personID) {
        //save primary marriage
        if (lastperson)
            slotperson.famID = people[lastperson].famc;
        else
            slotperson.famID = "";
        if (hideempty) {
            currentBox.style.visibility = 'visible';
            toggleLines(slot, slotperson.famc, 'visible');
        }
        if (slotperson.photosrc && slotperson.photosrc != "-1") {
            content = '<img src="' + slotperson.photosrc + '" id="img' + slot + '"' + ' class="smallimg">';
            if (slotperson.photolink && slotperson.photolink != "-1")
                content = '<a href="' + slotperson.photolink + '">' + content + '</a>';
            content = '<td class="lefttop">' + content + '</td>';
        }
        content += '<td class="pboxname" id="td' + slot + '">' + namepad + '<a href="peopleShowPerson.php?personID=' + slotperson.personID + '" id="tdlink' + slot + '">' + slotperson.name + '</a>';
        content += getGenderIcon(slotperson.gender);

        //put small pedigree link in every box except for primary individual
        if (!tngprint) {
            if (popupchartlinks && slotperson.famc != -1 && slotperson.personID != personID)
                icons += pedIcon(slotperson.personID);
            if (allow_edit)
                icons += editIcon('P', slot, slotperson.personID, '', slotperson.gender);
            if (display != "box") {
                var w = parseInt(currentBox.style.width) - 35;
                var h = parseInt(currentBox.style.height) - 15;
                icons = '<div class="floverlr" id="ic' + slot + '" style="left:' + w + 'px;top:' + h + 'px;display:none;background-color:' + currentBox.oldcolor + '">' + icons + '</div>';
            } else {
                content += icons;
                icons = "";
            }
        }
        if (display == "box") {
            var bmd = doBMD(slot, slotperson);
            if (bmd)
                content += '<table>' + bmd + '</table>';
        }
        content += '</td>';
        currentBox.style.backgroundColor = currentBox.oldcolor;

        if (usepopups) {
            if (slotperson.spfams || slotperson.bdate || slotperson.bplace || slotperson.ddate || slotperson.dplace || slotperson.parents) {
                dnarrow.css('visibility', 'visible');
                popup.html(popupcontent);
            } else
                dnarrow.css('visibility', 'hidden');
        }
    }
    //no person
    else {
        if (hideempty) {
            content = '';
            currentBox.style.visibility = "hidden";
            toggleLines(slot, 0, 'hidden');
        } else {
            if (allow_edit && lastperson && people[lastperson].famc != -1) {
                var twoback = people[lastperson].backperson
                var twobackfam = people[twoback] ? people[twoback].famc : "";
                content = '<td class="pboxname" id="td' + slot + '" align="' + pedboxalign + '">' + namepad + '<a href="#" onclick="return editFamily(\'' + people[lastperson].famc + '\', ' + slot + ',\'' + people[lastperson].personID + '\',\'' + twobackfam + '\');">' + textSnippet('editfam') + '</a></td>';
            } else if (allow_add && lastperson && people[lastperson].famc == -1)
                content = '<td class="pboxname" id="td' + slot + '" align="' + pedboxalign + '">' + namepad + '<a href="#" onclick="return newFamily(' + slot + ',\'' + people[lastperson].personID + '\');">' + textSnippet('addnewfam') + '</a></td>';
            else
                content = '<td class="pboxname" id="td' + slot + '" align="' + pedboxalign + '">' + namepad + unknown + '</td>';
            currentBox.style.backgroundColor = emptycolor;
        }
        if (usepopups) {
            dnarrow.css('visibility', 'hidden');
            popup.html("");
        }
    }
    currentBox.innerHTML = content ? icons + '<table class="pedboxtable" cellpadding="' + pedcellpad + '" align="' + pedboxalign + '"><tr>' + content + '</tr></table>' : "";

    var nextslot = slot * 2;
    if (slotperson.famc != -1 && families[slotperson.famc]) {
        husband = families[slotperson.famc].husband;
        wife = families[slotperson.famc].wife;
    } else {
        husband = 0;
        wife = 0;
    }
    if (nextslot < slotceiling) {
        FillSlot(nextslot, husband, slotperson.personID);
        nextslot++;
        FillSlot(nextslot, wife, slotperson.personID);
    } else if (slotperson.famc != "-1") {
        if (slot < (slotceiling_minus1 * 3 / 2))
            toplinks = addToList(toplinks, slotperson.personID);
        else
            botlinks = addToList(botlinks, slotperson.personID);
        endslots[endslotctr] = slot;
        endslotctr++;
    } else {
        offpage = $('#offpage' + slot);
        offpage.css('visibility', 'hidden');
    }
}

function toggleLines(slot, nextperson, visibility) {
    var newvis;

    for (var i = 1; i <= 5; i++) {
        shadow = $('#shadow' + slot + '_' + i);
        border = $('#border' + slot + '_' + i);
        newvis = (i == 3 && nextperson <= 0) ? "hidden" : visibility;
        if (shadow.length)
            shadow.css('visibility', newvis);
        if (border.length)
            border.css('visibility', newvis);
    }
}

function addToList(linklist, backperson) {
    if (linklist.indexOf(backperson) < 0) {
        if (linklist)
            linklist += ",";
        linklist += backperson;
    }
    return linklist;
}

function getParams(personstr) {
    var params = "", currperson, nextfamily;

    if (personstr) {
        var pers = personstr.split(",")
        for (var i = 0; i < pers.length; i++) {
            currperson = pers[i];
            nextfamily = people[currperson].famc;
            if (!families[nextfamily] || needspouses(nextfamily)) {
                ctr = i + 1;
                params += "&backpers" + ctr + "=" + currperson + "&famc" + ctr + "=" + people[currperson].famc;
            }
        }
        params += "&l=" + pers.length;
    }
    return params;
}

function needspouses(nextfamily) {
    var husb = families[nextfamily].husband;
    var wife = families[nextfamily].wife;

    return (!husb || !wife || !people[husb] || !people[wife]) ? true : false;
}

var tdclasstxt = 'class="pboxpopup"';
var divtxt = '<div class="pboxpopupdiv">\n<table cellpadding="1" width="100%">\n';
var tabletxt = '<table cellpadding="1" width="100%">\n';
function doRow(slot, slotabbr, slotevent1, slotevent2) {
    var rstr = "";
    slotabbr += ":";
    if (slotevent1)
        rstr += '<tr><td ' + tdclasstxt + ' align="right" id="popabbr' + slot + '">' + slotabbr + '</td><td ' + tdclasstxt + ' colspan="3" id="pop' + slot + '">' + slotevent1 + '</td></tr>';
    if (slotevent2) {
        if (slotevent1)
            slotabbr = '&nbsp;';
        rstr += '<tr><td ' + tdclasstxt + ' align="right" id="popabbr' + slot + '">' + slotabbr + '</td><td ' + tdclasstxt + ' colspan="3" id="pop' + slot + '">' + slotevent2 + '</td></tr>';
    }
    return rstr;
}

function getBackPerson(nxtpersonID) {
    hidePopup('left');
    getNewChart(nxtpersonID, generations, 0);
}

function getBackPopup() {
    var popupcontent = "", spouselink, count, kidlink;

    var slotperson = slots[1];

    if (slotperson.spfams) {
        popupcontent += divtxt;
        for (var i = 0; i < slotperson.spfams.length; i++) {
            var fam = slotperson.spfams[i];
            var children = families[fam.spFamID].children;
            count = i + 1;

            //do each spouse
            if (fam.spID && fam.spID != -1)
                spouselink = fam.spname;
            else
                spouselink = unknown;

            popupcontent += '<tr><td ' + tdclasstxt + ' id="popabbrleft"><b>' + count + '</b></td>';
            popupcontent += '<td ' + tdclasstxt + ' colspan="2" id="popleft">' + spouselink + '</td></tr>';

            if (popupkids && children) {
                //these might not need nowrap
                popupcontent += '<tr><td ' + tdclasstxt + ' align="right" id="popabbrleft">&nbsp;</td><td ' + tdclasstxt + ' colspan="3" id="popleft"><b>' + textSnippet('children') + ':</b></td></tr>\n';
                for (var j = 0; j < children.length; j++) {
                    var spchild = children[j];

                    kidlink = '<a href="javascript:getBackPerson(' + "'" + spchild.childID + "'" + ')">';
                    popupcontent += '<tr><td ' + tdclasstxt + ' id="popabbrleft">' + kidlink + '<img src="img/ArrowLeft.gif" width="10" height="16"></a></td>';
                    popupcontent += '<td ' + tdclasstxt + ' id="popleft">' + kidlink + spchild.name + '</a></td></tr>';
                }
            }
        }
        popupcontent += "</table></div>\n";
    }
    if (popupcontent)
        popupcontent = '<div><div class="popinner">' + popupcontent + '</div></div>\n';
    return popupcontent;
}

function doBMD(slot, slotperson) {
    var famID = slotperson.famID;
    var content = "";
    var icons = "";
    if (popupchartlinks && slotperson.famc != -1 && slotperson.personID != personID)
        icons += pedIcon(slotperson.personID);
    if (allow_edit)
        editIcon('P', slot, slotperson.personID, '', slotperson.gender);
    if (display == "standard")
        content += divtxt + '<tr><td ' + tdclasstxt + ' colspan="4"><b>' + slotperson.name + '</b>' + icons + '</td></tr>\n';
    else
        content += tabletxt;
    content += doRow(slot, slotperson.babbr, slotperson.bdate, slotperson.bplace);
    if (famID)
        content += doRow(slot, families[famID].mabbr, families[famID].mdate, families[famID].mplace);
    content += doRow(slot, slotperson.dabbr, slotperson.ddate, slotperson.dplace);
    content += '</table>';
    if (display == "standard")
        content += '</div>';
    return content;
}

function getPopup(slot) {
    var popupcontent = "", spouselink, sppedlink, count, kidlink, kidpedlink, parpedlink, parentlink;

    var slotperson = slots[slot];

    if (display == "standard")
        popupcontent += doBMD(slot, slotperson);

    if (slotperson.parents) {
        if (popupcontent)
            popupcontent += '<div class="popdivider"></div>\n';
        popupcontent += divtxt;
        popupcontent += '<tr><td class="pboxpopup" colspan="4" id="pop' + slot + '"><b>' + textSnippet('parents') + ':</b></td></tr>\n';
        for (var i = 0; i < slotperson.parents.length; i++) {
            var par = slotperson.parents[i];
            count = i + 1;
            parentlink = '';

            if (par.fatherID)
                parentlink += '<a href="peopleShowPerson.php?personID=' + par.fatherID + '">' + par.fathername + '</a>';
            if (par.motherID) {
                if (parentlink)
                    parentlink += ", ";
                parentlink += '<a href="peopleShowPerson.php?personID=' + par.motherID + '">' + par.mothername + '</a>';
            }
            if (par.famID != slotperson.famc)
                parpedlink = '<a href="pedigree.php?personID=' + slotperson.personID + '&amp;parentset=' + count + '&amp;display=' + display + '&amp;generations=' + generations + '">' + chartlink + '</a>';
            else
                parpedlink = '';
            popupcontent += '<tr><td ' + tdclasstxt + ' id="popabbr' + slot + '"><b>' + count + '</b></td>';
            popupcontent += '<td ' + tdclasstxt + ' colspan="2" id="pop' + slot + '">' + parentlink + '</td>';
            popupcontent += '<td ' + tdclasstxt + ' align="right">&nbsp;' + parpedlink + '</td></tr>';
        }
        popupcontent += '</table></div>\n';
    }

    if (popupspouses && slotperson.spfams) {
        for (var i = 0; i < slotperson.spfams.length; i++) {
            var fam = slotperson.spfams[i];
            var children = families[fam.spFamID].children;
            count = i + 1;

            //this one might not need "nowrap"
            if (popupcontent)
                popupcontent += '<div class="popdivider"></div>';
            popupcontent += divtxt;
            popupcontent += '<tr><td ' + tdclasstxt + ' colspan="4" id="pop' + slot + '"><B>' + textSnippet('family') + ':</B> [<a href=\"familiesShowFamily.php?familyID=' + fam.spFamID + '">' + textSnippet('groupsheet') + '</a>]';
            if (allow_edit)
                popupcontent += editIcon('F', slot, slotperson.backperson, fam.spFamID, slotperson.gender);
            popupcontent += '</td></tr>';
            //do each spouse
            sppedlink = '';
            if (fam.spID && fam.spID != -1) {
                spouselink = '<a href="peopleShowPerson.php?personID=' + fam.spID + '">' + fam.spname + '</a>';
                if (popupchartlinks)
                    sppedlink = pedIcon(fam.spID);
            } else
                spouselink = unknown;

            popupcontent += '<tr><td ' + tdclasstxt + ' id="popabbr' + slot + '"><b>' + count + '</b></td>';
            popupcontent += '<td ' + tdclasstxt + ' colspan="2" id="pop' + slot + '">' + spouselink + '</td>';
            popupcontent += '<td ' + tdclasstxt + ' align="right">' + sppedlink + '</td></tr>';

            if (popupkids && children && children.length) {
                popupcontent += '<tr><td class="pboxpopup" align="right" id="popabbr' + slot + '">&nbsp;</td><td class="pboxpopup" colspan="3" id="pop' + slot + '"><B>' + textSnippet('children') + ':</B></td></tr>\n';
                for (var j = 0; j < children.length; j++) {
                    var spchild = children[j];

                    kidlink = '<a href="peopleShowPerson.php?personID=' + spchild.childID + '">' + spchild.name + '</a>';
                    if (popupchartlinks)
                        kidpedlink = pedIcon(spchild.childID);
                    else
                        kidpedlink = '';
                    popupcontent += '<tr><td ' + tdclasstxt + ' id="popabbr' + slot + '">&nbsp;</td>';
                    popupcontent += '<td ' + tdclasstxt + ' id="pop' + slot + '">' + pedbullet + '</td>';
                    popupcontent += '<td ' + tdclasstxt + ' id="pop' + slot + '">' + kidlink + '</td>';
                    popupcontent += '<td ' + tdclasstxt + ' align="right" id="pop' + slot + '">' + kidpedlink + '</td></tr>';
                }
            }
            popupcontent += '</table></div>\n';
        }
    }

    if (popupcontent)
        popupcontent = '<div><div class="popinner">' + popupcontent + '</div></div>\n';
    return popupcontent;
}

function editIcon(type, slot, personID, familyID, gender) {
    var iconlink;
    var editicon = '<img src="svg/pencil.svg" width="10" height="10">';

    if (type == "P")
        iconlink = ' <a href="#" onclick="return editPerson(\'' + personID + '\',' + slot + ',\'' + gender + '\');" title="' + textSnippet('editperson') + '">' + editicon + '</a>';
    else {
        var famc = personID ? people[personID].famc : familyID;
        iconlink = ' <a href="#" onclick="return editFamily(\'' + familyID + '\',' + slot + ',\'' + personID + '\',\'' + famc + '\');" title="' + textSnippet('editfam') + '">' + editicon + '</a>';
    }
    return iconlink;
}

function pedIcon(personID) {
    return ' <a href="pedigree.php?personID=' + personID + '&amp;display=' + display + '&amp;generations=' + generations + '" title="' + textSnippet('popupnote2') + '">' + chartlink + '</a>';
}