
LITBox = function (url, options) {
    'use strict';
    this.url = url;
    this.options = {
        width: 700,
        height: 500,
        type: 'window',
        func: null,
        onremove: null,
        draggable: true,
        resizable: true,
        overlay: true,
        opacity: 1,
        left: false,
        top: false,
        doneLoading: null
    };
    var winwidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    $.extend(this.options, options || {});
    if (winwidth < this.options.width) {
        this.options.width = winwidth - 35;
    }

    this.setup();
};

LITBox.prototype = {
    setup: function () {
        'use strict';
        var self = this;
        $(document).ready(function () {
            self._setup();
        });
    },
    _setup: function () {
        'use strict';
        var self = this;
        if ($('#searchdrop').length) {
            $('#searchdrop').hide();
        }
        this.rn = (Math.floor(Math.random() * 100000000 + 1));
        this.getWindow();

        switch (this.options.type) {
        case 'window':
            var tempvar = this.getAjax(this.url);
            this.d4.innerHTML = tempvar;
            break;
        case 'alert':
            this.d4.innerHTML = this.url;
            break;
        case 'confirm':
            this.d4.innerHTML = '<p>' + this.url + '</p>';
            this.button_y = document.createElement('input');
            this.button_y.type = 'button';
            this.button_y.value = 'Yes';
            this.d4.appendChild(this.button_y);
            this.button_y.d = this.d;
            this.button_y.d2 = this.d2;
            this.button_y.temp = this.options.func;
            this.button_y.onclick = this.remove;
            this.button_n = document.createElement('input');
            this.button_n.type = 'button';
            this.button_n.value = 'No';
            this.d4.appendChild(this.button_n);
            this.button_n.d = this.d;
            this.button_n.d2 = this.d2;
            this.button_n.onclick = this.remove;
            break;
        }
        this.display();
    },
    getWindow: function () {
        'use strict';
        this.over = null;
        if (this.options.overlay === true) {
            this.d = document.createElement('div');
            document.body.appendChild(this.d);
            this.d.className = 'LB_overlay';
            this.d.id = 'LB_overlay';
            this.d.style.display = 'block';
        }
        this.d2 = document.createElement('div');
        document.body.appendChild(this.d2);
        this.d2.className = 'LB_window';
        this.d2.id = 'LB_window';
        this.d2.style.height = parseInt(this.options.height, 10) + 'px';
        this.d2.style.zIndex = '101';

        this.d3 = document.createElement('div');
        this.d2.appendChild(this.d3);
        this.d3.className = 'LB_closeAjaxWindow';
        this.d3.d2 = this.d2;
        this.d3.over = this.over;
        this.d3.options = this.options;
        if (this.options.draggable) {
            $(this.d2).draggable({handle: '.LB_closeAjaxWindow'}).resizable();
        }
        this.close = document.createElement('a');
        this.d3.appendChild(this.close);
        this.d3.style.width = parseInt(this.options.width, 10) + 'px';
        this.close.d = this.d;
        this.close.d2 = this.d2;
        this.close.href = '#';
        this.close.onclick = this.remove;
//        this.close.innerHTML = '<img src="' + closeimg + '" border="0">';
        this.close.id = 'LB_close';
        this.close.onremove = this.options.onremove;
        this.d3text = document.createElement('div');
        this.d3text.id = 'LB_titletext';
        this.d3text.className = 'fieldname';
        if (this.options.title) {
            this.d3text.innerHTML = '<strong>' + this.options.title + '</strong>';
        }
        this.d3.appendChild(this.d3text);
        this.d4 = document.createElement('div');
        this.d4.className = 'LB_content';
        this.d4.style.height = parseInt(this.options.height, 10) + 'px';
        this.d4.style.width = parseInt(this.options.width, 10) + 'px';
        this.d2.appendChild(this.d4);
        this.clear = document.createElement('div');
        this.d2.appendChild(this.clear);
        this.clear.style.clear = 'both';
        if (this.options.overlay === true) {
            this.d.d = this.d;
            this.d.d2 = this.d2;
        }
    },
    display: function () {
        'use strict';
        $(this.d2).css('opacity', 0);
        this.position();
        $(this.d2).animate({opacity: this.options.opacity}, 200, this.options.doneLoading);
    },
    position: function () {
        'use strict';
        var de = document.documentElement;
        var w = self.innerWidth || (de && de.clientWidth) || document.body.clientWidth;

        var yScroll = 0;
        if (window.innerHeight) {
            yScroll = window.innerHeight;
            if (window.scrollMaxY) {
                yScroll += window.scrollMaxY;
            }
        }
        if (document.body.scrollHeight >= document.body.offsetHeight && document.body.scrollHeight > yScroll) { // all but Explorer Mac
            yScroll = document.body.scrollHeight + 30;
        }
        if (document.documentElement.clientHeight && document.documentElement.clientHeight > yScroll) {
            yScroll = document.documentElement.clientHeight;
        }
        if (!yScroll) { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
            yScroll = document.body.offsetHeight;
        }
        this.d2.style.width = this.options.width + 'px';
        this.d2.style.display = 'block';
        if (!this.options.left || this.options.left < 0) {
            this.d2.style.left = ((w - this.options.width) / 2) + "px";
        } else {
            this.d2.style.left = parseInt(this.options.left, 10) + 'px';
        }

        var pagesize = this.getPageSize();
        var arrayPageScroll = this.getPageScrollTop();

        if (!this.options.top || this.options.top < 0) {
            var newtop = arrayPageScroll[1] + ((pagesize[1] - this.d2.offsetHeight) / 2);
            this.d2.style.top = newtop > 0 ? newtop + "px" : "0px";
        } else {
            this.d2.style.top = parseInt(this.options.top, 10) + 'px';
        }
        if (this.d) {
            this.d.style.height = yScroll + "px";
        }
    },
    remove: function () {
        'use strict';
        if (this.temp) {
            this.temp();
        }
        var d2 = $(this.d2);
        var onremove = this.onremove;

        d2.animate({opacity: 0}, 200, function () {
            d2.remove();
        });
        if (this.d) {
            var d = $(this.d);
            d.animate({opacity: 0}, 200, function () {
                d.remove();
                if (onremove) {
                    onremove();
                }
            });
        }
        return false;
    },
    parseQuery: function (query) {
        'use strict';
        var Params = new Object();
        if (!query) {
            return Params; // return empty object
        }
        var Pairs = query.split(/[;&]/);
        var i;
        var KeyVal, key, val;
        for (i = 0; i < Pairs.length; i += 1) {
            KeyVal = Pairs[i].split('=');
            if (!KeyVal || KeyVal.length !== 2) {
                continue;
            }
            key = unescape(KeyVal[0]);
            val = unescape(KeyVal[1]);
            val = val.replace(/\+/g, ' ');
            Params[key] = val;
        }
        return Params;
    },
    getPageScrollTop: function () {
        'use strict';
        var yScrolltop;
        if (self.pageYOffset) {
            yScrolltop = self.pageYOffset;
        } else if (document.documentElement && document.documentElement.scrollTop) {    // Explorer 6 Strict
            yScrolltop = document.documentElement.scrollTop;
        } else if (document.body) {// all other Explorers
            yScrolltop = document.body.scrollTop;
        }
        arrayPageScroll = new Array('', yScrolltop);
        return arrayPageScroll;
    },
    getPageSize: function () {
        'use strict';
        var de = document.documentElement;
        var w = self.innerWidth || (de && de.clientWidth) || document.body.clientWidth;
        var h = self.innerHeight || (de && de.clientHeight) || document.body.clientHeight;

        arrayPageSize = new Array(w, h);
        return arrayPageSize;
    },
    getAjax: function (url) {
        'use strict';
        var xmlhttp = false;

        if (!xmlhttp && typeof XMLHttpRequest !== 'undefined') {
            xmlhttp = new XMLHttpRequest();
        }
        if (xmlhttp.overrideMimeType) {
            xmlhttp.overrideMimeType('text/xml');
        }
        if (url !== "") {
            xmlhttp.open("GET", url, false);
            xmlhttp.send(null);
            return xmlhttp.responseText;
        }
    }
};

function openFind(form, findscript) {
    'use strict';
    var params = $.param(form);
    if ($('#findspin').length) {
        $('#findspin').show();
    }
    $.ajax({
        url: findscript,
        data: params,
        dataType: 'html',
        success: function (req) {
            $('#findresults').html(req);
            if ($('#findspin').length) {
                $('#findspin').hide();
            }
            $('#finddiv').toggle(200, function () {
                $('#findresults').toggle(200);
            });
        }
    });
    return false;
}

function clearForm(form) {
    'use strict';
    $(form).children(':input').each(function (index, element) {
        if (element.type === 'text') {
            element.value = '';
        }
    });
}

function reopenFindForm() {
    'use strict';
    $('#findresults').toggle(200, function () {
        clearForm(document.findform1);
        $('#finddiv').toggle(200);
    });
}

function openHelp(filename) {
    'use strict';
    var newwindow = window.open(filename, 'newwindow', 'height=600,width=700,resizable=yes,scrollbars=yes');
    newwindow.focus();

    return false;
}
