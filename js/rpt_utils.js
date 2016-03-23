function toggleSection(section, img, display) {
    if (display == 'on') {
        $('#' + img).attr('src', 'tng_collapse.gif');
        $('#' + section).fadeIn(300);
    } else if (display == 'off') {
        $('#' + img).attr('src', 'tng_expand.gif');
        $('#' + section).fadeOut(300);
    } else {
        $('#' + img).attr('src', $('#' + img).attr('src').indexOf('collapse') > 0 ? 'img/tng_expand.gif' : 'img/tng_collapse.gif');
        $('#' + section).toggle(300);
    }
    return false;
}