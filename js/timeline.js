$(document).ready(init);
$(window).resize(onResize);

var tl;
function init() {
    timeline_init();
}

function timeline_init() {
    var eventSource = new Timeline.DefaultEventSource();
    if (monthpct !== "0%") {
        var bandInfos = [
            Timeline.createBandInfo({
                eventSource: eventSource,
                date: tlstartdate,
                width: monthpct,
                intervalUnit: Timeline.DateTime.MONTH,
                intervalPixels: monthpixels
            }),
            Timeline.createBandInfo({
                eventSource: eventSource,
                date: tlstartdate,
                width: yearpct,
                intervalUnit: Timeline.DateTime.YEAR,
                multiple: yearmultiple,
                intervalPixels: yearpixels
            })
        ];
        bandInfos[1].syncWith = 0;
        bandInfos[1].highlight = true;
    } else {
        var bandInfos = [
            Timeline.createBandInfo({
                eventSource: eventSource,
                date: tlstartdate,
                width: yearpct,
                intervalUnit: Timeline.DateTime.YEAR,
                multiple: yearmultiple,
                intervalPixels: yearpixels
            })
        ];
    }

    tl = Timeline.create(document.getElementById("tngtimeline"), bandInfos);
    Timeline.loadXML(xmlfile, function (xml, url) {
        eventSource.loadXML(xml, url);
    });
}

var resizeTimerID = null;
function onResize() {
    if (resizeTimerID === null) {
        resizeTimerID = window.setTimeout(function () {
            resizeTimerID = null;
            tl.layout();
        }, 500);
    }
}