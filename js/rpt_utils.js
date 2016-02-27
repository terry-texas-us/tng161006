function toggleSection(section, img, display) {
    if (display == 'on') {
        jQuery('#' + img).attr('src', 'tng_collapse.gif');
        jQuery('#' + section).fadeIn(300);
    } else if (display == 'off') {
        jQuery('#' + img).attr('src', 'tng_expand.gif');
        jQuery('#' + section).fadeOut(300);
    } else {
        jQuery('#' + img).attr('src', jQuery('#' + img).attr('src').indexOf('collapse') > 0 ? 'img/tng_expand.gif' : 'img/tng_collapse.gif');
        jQuery('#' + section).toggle(300);
    }
    return false;
}