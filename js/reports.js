function finishValidation() {
    var displayfields = document.form1.displayfields;
    var criteriafields = document.form1.finalcriteria;
    var orderbyfields = document.form1.finalsort;
    var displayfield = document.form1.display;
    var criteriafield = document.form1.criteria;
    var orderbyfield = document.form1.orderby;

    for (var i = 0; i < displayfields.options.length; i++) {
        displayfield.value = displayfield.value + displayfields.options[i].value + "\r\n";
    }

    for (var j = 0; j < criteriafields.options.length; j++) {
        if (criteriafields.options[j].text.substr(0, 1) == "\"")
            criteriafields.options[j].value = "\"" + criteriafields.options[j].value + "\"";
        criteriafield.value = criteriafield.value + criteriafields.options[j].value + "\r\n";
    }

    for (var k = 0; k < orderbyfields.options.length; k++) {
        orderbyfield.value = orderbyfield.value + orderbyfields.options[k].value + "\r\n";
    }
}

function AddConstant(source, dest, flag) {
    if (flag || source.value.length) {
        if (flag)
            var newval = "\"" + source.value + "\"";
        else
            var newval = source.value;
        if (navigator.appName == "Netscape") {
            dest.options[dest.options.length] = new Option(newval, source.value, false, false)
        } else if (navigator.appName == "Microsoft Internet Explorer") {
            var newElem = document.createElement("OPTION");
            newElem.text = newval;
            newElem.value = source.value;
            dest.options.add(newElem);
        }
    }
}