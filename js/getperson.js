function turnOn(subpart) {
    'use strict';
    $('#' + subpart).show();
}

function turnOff(subpart) {
    'use strict';
    $('#' + subpart).hide();
}

function innerToggle(part, subpart) {
    'use strict';
    if (part === subpart) {
        turnOn(subpart);
    } else {
        turnOff(subpart);
    }
}