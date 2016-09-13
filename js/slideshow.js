function Slideshow(options) {
    var slidetime = "3.0";
    $('#loadingdiv').css('top', '240px');
    $('#loadingdiv').css('left', '290px');

    var slidemsg = document.createElement('span');
    slidemsg.id = "slidemsg";
    slidemsg.className = "small";
    slidemsg.innerHTML = '&nbsp;&nbsp; <a href="#" onclick="return stopshow();" id="slidetoggle">' + '&gt; ' + textSnippet('slidestop') + '</a> &nbsp;&nbsp; ';
    $('.modal-header').append(slidemsg);

    var sscontrols = document.createElement('span');
    sscontrols.id = "sscontrols";
    sscontrols.className = "small";
    var dims = 'width="9" height="9"';
    var controls;
    controls = textSnippet('slidesecs') + '\n<a href="#" title="' + textSnippet('minussecs') + '" onclick="return changeSlideTime(-500)"><img src="img/tng_minus.gif" ' + dims + ' alt="' + textSnippet('minussecs') + '" name="minus"></a>\n';
    controls += '<span id="sssecs">' + slidetime + '</span>\n';
    controls += '<a href="#" title="' + textSnippet('plussecs') + '" onclick="return changeSlideTime(500)"><img src="img/tng_plus.gif" ' + dims + ' alt="' + textSnippet('plussecs') + '" name="plus"></a>\n';
    sscontrols.innerHTML = controls;
    $('.modal-header').append(sscontrols);

    this.slides = [];
    this.slides.push($('#div0'));
    this.slides.push($('#div1'));

    this.timeout = options.timeout;
    this.front = 1;
    this.back = 0;
    this.ready = 0;
    this.paused = false;

    this.startingID = options.startingID;
    this.previousID = options.mediaID;
    this.mediaID = options.mediaID;
    this.medialinkID = options.medialinkID;
    this.albumlinkID = options.albumlinkID;
    this.cemeteryID = options.cemeteryID;

    this.slides[this.front].css('z-index', 1);
    this.slides[this.back].css('z-index', 0);

    this.next();
}
Slideshow.prototype = {
    next: function () {
        if (this.slides[this.back].html()) {
            this.slides[this.back].show();

            if ($('#div0').length) {
                var slideheight = $('#div' + this.back).height();
                var ssheight = $('#slideshow').height();
                if (!ssheight || ssheight < slideheight) {
                    $('#div' + this.front).height(slideheight);
                    $('#slideshow').height(slideheight);
                }
                var slidewidth = $('#div' + this.back).width();
                var sswidth = $('#slideshow').width();
                if (!sswidth || sswidth < slidewidth) {
                    $('#div' + this.front).width(slidewidth);
                    $('#slideshow').width(slidewidth);
                }

                this.slides[this.front].css('z-index', 2);
                this.slides[this.back].css('z-index', 1);

                $('#loadingdiv').hide();
                var frontSlide = this.slides[this.front];
                frontSlide.fadeOut(300, function () {
                    frontSlide.css('z-index', 0);
                    frontSlide.css('opacity', 1);
                    if (!myslides.paused)
                        adjustTimeout(myslides);
                    myslides.front = (myslides.front + 1) % 2;
                    myslides.back = (myslides.back + 1) % 2;
                    myslides.ready = 0;
                    myslides.loadslide();
                });
            }
        } else {
            adjustTimeout(this);
            this.loadslide();
        }
    },
    loadslide: function () {
        var strParams = 'mediaID=' + this.mediaID + '&medialinkID=' + this.medialinkID + '&albumlinkID=' + this.albumlinkID + '&cemeteryID=' + this.cemeteryID;
        var params = {mediaID: this.mediaID, medialinkID: this.medialinkID, albumlinkID: this.albumlinkID, cemeteryID: this.cemeteryID};
        //alert(strParams);
        $.ajax({
            url: 'ajx_showmediaxml.php?',
            data: params,
            dataType: 'html',
            type: 'POST',
            success: getNextSlide
        });
    }
}

function adjustTimeout(slide) {
    clearTimeout(timeoutID);
    timeoutID = setTimeout((function () {
        slide.next();
    }).bind(slide), slide.timeout + 500);
}

function getNextSlide(req) {
    var pair;
    var mediaID = '';
    var medialinkID = '';
    var albumlinkID = '';

    var contentstart = req.indexOf('<');
    var arglist = req.substr(0, contentstart);
    var content = req.substr(contentstart);

    arglist.replace('&amp;', '&');
    var args = arglist.split("&");
    for (i = 0; i < args.length; i++) {
        pair = args[i].split('=');
        if (pair[0] == "mediaID")
            mediaID = pair[1];
        else if (pair[0] == "medialinkID")
            medialinkID = pair[1];
        else if (pair[0] == "albumlinkID")
            albumlinkID = pair[1];
    }

    if (!repeat && myslides.previousID == myslides.startingID) {
        stopshow('&gt; ' + textSnippet('slidestart'));
    }

    myslides.previousID = myslides.mediaID;
    myslides.mediaID = mediaID;
    myslides.medialinkID = medialinkID;
    myslides.albumlinkID = albumlinkID;

    if ($('#div0').length) {
        $('#div' + myslides.back).hide();
        $('#div' + myslides.back).html(content);
    }
    myslides.ready = 1;
}

function stopshow(msg) {
    if (myslides.paused) {
        $('#slidetoggle').html('&gt; ' + textSnippet('slidestop'));
        myslides.paused = false;
        $('#sscontrols').fadeIn(300);
        timeoutID = setTimeout((function () {
            myslides.next();
        }).bind(myslides), myslides.timeout + 500);
    } else {
        clearTimeout(timeoutID);
        timeoutID = false;
        myslides.paused = true;
        if (!msg)
            msg = '&gt; ' + textSnippet('slideresume');
        $('#slidetoggle').html(msg);
        $('#sscontrols').fadeOut(300);
    }
    return false;
}

function jump(mediaID, medialinkID, albumlinkID) {
    if (timeoutID || tnglitbox) {
        clearTimeout(timeoutID);
        timeoutID = false;
        $('#div' + myslides.back).html('');
        $('#div' + myslides.front).animate({opacity: 0.4}, 200, function () {
            $('#loadingdiv').css('display', 'block');
        });

        myslides.previousID = myslides.mediaID;
        myslides.mediaID = mediaID;
        myslides.medialinkID = medialinkID;
        myslides.albumlinkID = albumlinkID;

        adjustTimeout(myslides);
        myslides.loadslide();
        return false;
    } else
        return true;
}

function jumpnext(mediaID, medialinkID, albumlinkID) {
    if (timeoutID || tnglitbox) {
        if (myslides.ready) {
            clearTimeout(timeoutID);
            timeoutID = false;
            myslides.next();
            return false;
        } else
            return jump(mediaID, medialinkID, albumlinkID);
    } else
        return true;
}

function changeSlideTime(delta) {
    if ((myslides.timeout > 1000 && delta < 0) || (myslides.timeout < 10000 && delta > 0))
        myslides.timeout += delta;
    var secs = myslides.timeout / 1000;
    $('#sssecs').html(secs.toPrecision(2));
    return false;
}