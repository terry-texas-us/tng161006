/*
 url-loading object and a request queue built on top of it
 */

/* namespacing object */
var net = new Object();

net.READY_STATE_UNINITIALIZED = 0;
net.READY_STATE_LOADING = 1;
net.READY_STATE_LOADED = 2;
net.READY_STATE_INTERACTIVE = 3;
net.READY_STATE_COMPLETE = 4;


/*--- content loader object for cross-browser requests ---*/
net.ContentLoader = function (url, onload, onerror, method, params, contentType) {
    this.req = null;
    this.onload = onload;
    this.onerror = (onerror) ? onerror : this.defaultError;
    this.loadXMLDoc(url, method, params, contentType);
};

net.ContentLoader.prototype.loadXMLDoc = function (url, method, params, contentType) {
    if (!method) {
        method = "GET";
    }
    if (!contentType && method === "POST") {
        contentType = 'application/x-www-form-urlencoded';
    }
    if (window.XMLHttpRequest) {
        this.req = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        this.req = new ActiveXObject("Microsoft.XMLHTTP");
    }
    if (this.req) {
        try {
            var loader = this;
            this.req.onreadystatechange = function () {
                net.ContentLoader.onReadyState.call(loader);
            };
            this.req.open(method, url, true);
            if (contentType) {
                this.req.setRequestHeader('Content-Type', contentType);
            }
            this.req.send(params);
        } catch (err) {
            this.onerror.call(this);
        }
    }
};


net.ContentLoader.onReadyState = function () {
    var req = this.req;
    var ready = req.readyState;
    if (ready === net.READY_STATE_COMPLETE) {
        var httpStatus = req.status;
        if (httpStatus === 200 || httpStatus === 0) {
            this.onload.call(this);
        } else {
            this.onerror.call(this);
        }
    }
};

net.ContentLoader.prototype.defaultError = function () {
    alert("There was a problem returning data from the server. This may be temporary, so please try again later. Here is some information about the status of this request:"
      + "\n\nreadyState:" + this.req.readyState
      + "\nstatus: " + this.req.status
      + "\nheaders: " + this.req.getAllResponseHeaders());
};

function showPreview(mediaID, path, entitystr) {
    if ($('#prev' + entitystr).html() === '')
        $('#prev' + entitystr).html('<div id="ld' + entitystr + '"><img src="img/spinner.gif" style="border:0"> ' + textSnippet('loading') + '</div><img src="' + 'ajx_smallimage.php?' + 'mediaID=' + mediaID + '&path=' + encodeURIComponent(path) + '" style="display:none" onload="$(\'#ld\'+\'' + entitystr + '\').hide(); this.style.display=\'\';">');
    $('#prev' + entitystr).fadeIn(100);
}

function closePreview(entitystr) {
    $('#prev' + entitystr).fadeOut(100);
}

function openLogin(url) {
    tnglitbox = new ModalDialog(url);
    return false;
}

function setFocus(field) {
    if ($('#' + field).length)
        $('#' + field).focus();
}

function checkEmail(email) {
    var domains = ",AC,AD,AE,AERO,AF,AG,AI,AL,AM,AN,AO,AQ,AR,ARPA,AS,AT,AU,AW,AX,AZ,BA,BB,BD,BE,BF,BG,BH,BI,BIZ,BJ,BM,BN,BO,BR,BS,BT,BV,BW,BY,BZ,CA,CAT,CC,CD,CF,CG,CH,CI,CK,CL,CM,CN,CO,COM,COOP,CR,CU,CV,CX,CY,CZ,DE,DJ,DK,DM,DO,DZ,EC,EDU,EE,EG,ER,ES,ET,EU,FI,FJ,FK,FM,FO,FR,GA,GB,GD,GE,GF,GG,GH,GI,GL,GM,GN,GOV,GP,GQ,GR,GS,GT,GU,GW,GY,HK,HM,HN,HR,HT,HU,ID,IE,IL,IM,IN,INFO,INT,IO,IQ,IR,IS,IT,JE,JM,JO,JOBS,JP,KE,KG,KH,KI,KM,KN,KR,KW,KY,KZ,LA,LB,LC,LI,LK,LR,LS,LT,LU,LV,LY,MA,MC,MD,MG,MH,MIL,MK,ML,MM,MN,MO,MOBI,MP,MQ,MR,MS,MT,MU,MUSEUM,MV,MW,MX,MY,MZ,NA,NAME,NC,NE,NET,NF,NG,NI,NL,NO,NP,NR,NU,NZ,OM,ORG,PA,PE,PF,PG,PH,PK,PL,PM,PN,PR,PRO,PS,PT,PW,PY,QA,RE,RO,RU,RW,SA,SB,SC,SD,SE,SG,SH,SI,SJ,SK,SL,SM,SN,SO,SR,ST,SU,SV,SY,SZ,TC,TD,TEL,TF,TG,TH,TJ,TK,TL,TM,TN,TO,TP,TR,TRAVEL,TT,TV,TW,TZ,UA,UG,UK,UM,US,UY,UZ,VA,VC,VE,VG,VI,VN,VU,WF,WS,YE,YT,YU,ZA,ZM,ZW,";
    var rval = /^\w+([\.\+-]*\w+)*@\w+([\.\+-]*\w+)*(\.\w{2,6})+$/.test(email);
    if (rval) {
        var thisdomain = email.substr(email.lastIndexOf(".") + 1);
        rval = domains.indexOf(',' + thisdomain.toUpperCase() + ',') >= 0 ? true : false;
    }
    return rval;
}

if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function (obj, start) {
        for (var i = (start || 0), j = this.length; i < j; i++) {
            if (this[i] === obj) {
                return i;
            }
        }
        return -1;
    }
}

$(document).ready(function () {
    $('.toggleicon').click(function (e) {
        var target = $(e.target);
        var targetId = target.attr('id');
        var affectedRows = $('.' + targetId);
        if (target.attr('src').indexOf('desc') > 0) {
            target.attr('src', "img/tng_sort_asc.gif");
            target.attr('title', textSnippet('collapse'));
            $('.l' + targetId).attr('rowspan', affectedRows.length + 1);
        } else {
            target.attr('src', "img/tng_sort_desc.gif");
            target.attr('title', textSnippet('expand'));
            $('.l' + targetId).removeAttr('rowspan');
        }
        if (targetId.substring(0, 1) === "m") {
            $('#dr' + targetId).toggle();
            $('#ss' + targetId).toggle();
        }
        affectedRows.toggle();
    });
});
