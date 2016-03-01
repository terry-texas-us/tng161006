// [ts] global functions and variables for jsLint
/*global textSnippet */

$('#suggest').submit(function (event) {
    'use strict';
    var emailControl = $(this).data('emailControl');
    var confirmEmailControl = $(this).data('confirmEmailControl');
    var email = $(emailControl).val();

    var reg = /^([A-Za-z0-9_\-\.])+@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,6})$/;
    if (reg.test(email) === false) {
        alert(textSnippet('enteremail'));
        event.preventDefault();
    } else if (email !== $(confirmEmailControl).val()) {
        alert(textSnippet('emailsmatch'));
        event.preventDefault();
    }
});

