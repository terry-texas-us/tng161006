// global settings
// by default, the background around the image will match the body color defined in your
// style sheet.  If you want this to be different, just define the color here
var canvasBackground = 'transparent';

// global variables
var lastLeft, lastTop, lastX, lastY;
var magzoom = 3;  // default zoom is 50% (for +/- usage)
var haveZoomed = false;

// don't modify these unless you're changing images
var shadowPadding = 17;
var magnifierSize = 150;

function popUp(URL, type, height, width) {
    'use strict';
    var options;
    if (type === 'console') {
        options = "resizable,scrollbars,width=" + width;
    }
    if (type === 'fixed') {
        options = "status,width=" + width;
    }
    if (type === 'elastic') {
        options = "toolbar,menubar,scrollbars,resizable,location,width=" + width;
    }
    // open the image in a new window... we put it in _blank so that we don't keep writing new images into the same window
    window.open(URL, '_blank', options);
}

function determinePanning() {
    'use strict';
    // if the user has set the height of the window, let's see if we can pan
    if (this.bodyh > 0) {
        if (this.height > this.bodyh) {
            this.canPan = true;
            this.panV = true;
        } else {
            this.canPan = false;
            this.panV = false;
        }
    } else {
        this.canPan = false;
        this.panV = false;
    }

    if (this.width <= this.bodyw) {
        this.panH = false;
    } else {
        this.panH = true;
        this.canPan = true;
    }
}

function MagnifierPosition() {
    'use strict';
    var half = this.size / 2,
        canvas,
        halfcanv,
        theimage,
        halfimg,
        magnifierCenterX,
        magnifierCenterY;

    // determine position of the magnifier
    // -1s here to account for the border
    this.style.left = Math.round(this.xPosition - 1 - half) + 'px';
    this.style.top = Math.round(this.yPosition - 1 - half) + 'px';

    // determine position of the shadow
    this.shadow.style.left = Math.round(this.xPosition - half - shadowPadding) + 'px';
    this.shadow.style.top = Math.round(this.yPosition - half - shadowPadding) + 'px';

    // determine the coordinates that we want to magnify onto
    canvas = document.getElementById('imageviewer');
    halfcanv = Math.round(canvas.offsetWidth / 2);
    theimage = document.getElementById('theimage');
    halfimg = Math.round(theimage.offsetWidth / 2);
    magnifierCenterX = Math.round((this.xPosition - lastLeft - halfcanv + halfimg) * this.xMultiplier - half);
    magnifierCenterY = Math.round((this.yPosition - lastTop - 10) * this.yMultiplier - half);

    this.style.backgroundPosition = -magnifierCenterX + 'px ' + -magnifierCenterY + 'px';
}

// handles enabling/disabling zoom options depending on the current zoom level
function checkZoomLevel(level, controller) {
    'use strict';
    // we need to disable the zoom up button now
    if (magzoom === 9) {
        controller.magup.src = 'img/img_magupoff.gif';
        controller.magdown.src = 'img/img_magdown.gif';
    } else if (magzoom === 2) {
        controller.magdown.src = 'img/img_magdownoff.gif';
        controller.magup.src = 'img/img_magup.gif';
    } else {
        controller.magdown.src = 'img/img_magdown.gif';
        controller.magup.src = 'img/img_magup.gif';
    }

    if (magzoom > 4) {
        controller.buttonMag.src = 'img/img_magoff.gif';
        controller.buttonMag.enabled = false;
    } else {
        controller.buttonMag.src = 'img/img_mag.gif';
        controller.buttonMag.enabled = true;
    }
}

function scaleImageMap(canvas) {
    'use strict';
    // we only support the image map if you are in pan mode
    if (canvas.mode === "pan") {
        var map = document.getElementById('imgMapViewer'),
            zoom,
            i,
            area,
            coords,
            newCoords,
            j;
        if (canvas.zoom === 0 || canvas.zoom === -1) {
            zoom = canvas.image.height / canvas.image.fullHeight;
        } else {
            zoom = canvas.zoom;
        }
        for (i = 0; i < map.areas.length; i += 1) {
            area = map.areas[i];
            coords = [];
            coords = canvas.origmap[i].split(',');
            newCoords = [];
            for (j = 0; j < coords.length; j += 1) {
                newCoords.push(Math.floor(coords[j] * zoom));
            }
            area.coords = newCoords.join(',');
        }
    }
}

// zooms in on the image, based on the zoom value passed in.
function zoomImage(zoom, canvas) {
    'use strict';
    var numericZoom = parseFloat(zoom),
        img = canvas.image,
        tmpzoom = 0,
        mag;

    if (numericZoom === 0) { // this is the fit width option
        img.width = img.fitWidth;
        img.height = img.fitHeight;
        tmpzoom = img.bodyw / img.fullWidth;
        if (tmpzoom > 1) {
            tmpzoom = 1;
        }
    } else if (numericZoom === -1) { // this is the fit height option
        tmpzoom = img.bodyh / img.fullHeight;
        if (tmpzoom > 1) {
            tmpzoom = 1;
        }
        img.width = img.fullWidth * tmpzoom;
        img.height = img.fullHeight * tmpzoom;
    } else { // otherwise we already know the zoom level
        img.width = img.fullWidth * numericZoom;
        img.height = img.fullHeight * numericZoom;
    }
    // update our magzoom level
    if (tmpzoom > 0) {
        if (tmpzoom < 0.25) {
            magzoom = 1;
        } else if (tmpzoom < 0.50) {
            magzoom = 2;
        } else if (tmpzoom < 0.75) {
            magzoom = 3;
        } else if (tmpzoom < 1) {
            magzoom = 4;
        } else {
            magzoom = 5;
        }
    }

    img.findPan();
    img.style.left = img.style.top = '0px';
    lastLeft = lastTop = 0;

    mag = canvas.magnifier;
    mag.xMultiplier = img.fullWidth / img.width;
    mag.yMultiplier = img.fullHeight / img.height;

    // need to modify the image map
    canvas.zoom = zoom;
    scaleImageMap(canvas);
}

// handles pushing the zoom level up button
function ControllerMagUp(e) {
    'use strict';
    var sel = this.parentNode.zoomsel;

    magzoom += 1;
    if (magzoom === 10) {
        magzoom = 9;
    }
    checkZoomLevel(magzoom, this.parentNode);
    sel.selectedIndex = magzoom;
    zoomImage(sel.options[sel.selectedIndex].value, this.parentNode.canvas);
    haveZoomed = true;

    // if the zoom is 100% or greater, we need to force pan mode
    if (magzoom >= 5) {
        this.parentNode.buttonPan.onclick();
    }
}

// handles pushing the zoom level down button
function ControllerMagDown(e) {
    'use strict';
    var sel = this.parentNode.zoomsel;
    if (sel.selectedIndex === 0 || sel.selectedIndex === 1) {
        magzoom = this.parentNode.canvas.origZoom;
    }
    if (haveZoomed) {
        magzoom -= 1;
    }
    if (magzoom <= 1) {
        magzoom = 2;
    }
    checkZoomLevel(magzoom, this.parentNode);
    sel.selectedIndex = magzoom;
    zoomImage(sel.options[sel.selectedIndex].value, this.parentNode.canvas);
    haveZoomed = true;
}

// handles hitting the 'pan mode' button
function ControllerPanMode(e) {
    'use strict';
    var img = this.parentNode.canvas.image;

    img.style.cursor = 'all-scroll';
    this.parentNode.canvas.mode = 'pan';

    // enable/disable the buttons as appropriate
    this.parentNode.buttonMag.className = 'controllerMag';
    this.className = 'controllerPanSelected';
    this.parentNode.msg.innerHTML = textSnippet('pan');

    scaleImageMap(this.parentNode.canvas);
}

function clearImageMap() {
    'use strict';
    var map = document.getElementById('imgMapViewer'),
        i,
        j,
        area,
        coords,
        newCoords;
    for (i = 0; i < map.areas.length; i += 1) {
        area = map.areas[i];
        coords = area.coords.split(',');
        newCoords = [];
        for (j = 0; j < coords.length; j += 1) {
            newCoords.push(0);
        }
        area.coords = newCoords.join(',');
    }
}

// handles hitting the magnifier button
function ControllerMagMode(e) {
    'use strict';
    if (this.enabled === true) {
        var img = this.parentNode.canvas.image;
        img.style.cursor = 'zoom-in';
        this.parentNode.canvas.mode = 'mag';

        this.parentNode.buttonPan.className = 'controllerPan';
        this.className = 'controllerMagSelected';
        this.parentNode.msg.innerHTML = textSnippet('magnifyreg');

        clearImageMap();
    }
}

function ControllerNewWin(e) {
    'use strict';
    var canvas = document.getElementById('imageviewer'),
        width = canvas.image.fitWidth > 600 ? canvas.image.fitWidth : 600;
    popUp("img_newwin.php?mediaID=" + this.mediaID + "&medialinkID=" + this.medialinkID + "&title=" + this.newwintitle, 'console', 0, width);
}

// handles the drop down box to select a zoom level
function ControllerZoomLevel(e) {
    'use strict';
    var sel = this.parentNode.zoomsel;
    magzoom = sel.selectedIndex;
    checkZoomLevel(magzoom, this.parentNode);
    zoomImage(sel.options[sel.selectedIndex].value, this.parentNode.canvas);
}

// this is called after the iframe has been filled out to allow for the proper 
// width and height of the image to be done
// we pass in the specified height of the frame as we need to know that information
// and it isn't available to us yet since the iframe isn't done being sized
// if frameHeight comes in as '1', the height of the iframe should be the height
// of the image
function sizeController(frameHeight) {
    'use strict';
    var canvas = document.getElementById('imageviewer'),
        img = canvas.image,
        controllerContainer = document.getElementById('ctrlcontainer'),
        enableContainer = document.getElementById('encontainer'),
        subOffset = true,
        zoom,
        showController,
        mag,
        magimg;

    // determine the height and width of our iframe first
    img.bodyw = document.body.offsetWidth;

    if (frameHeight > 1) { // frame height is specified, we can go ahead and set the height
        img.bodyh = frameHeight;
    }

    // now lets do one final resize of the image now that we know the actual iframe width
    zoom = img.bodyw / img.fullWidth;
    //zoom = (img.bodyh - canvas.offsetTop) / img.fullHeight;
    if (zoom > 1) {
        zoom = 1;
    }
    canvas.zoom = zoom;
    img.origZoom = zoom;
    img.width = Math.round(img.fullWidth * zoom);
    img.height = Math.round(img.fullHeight * zoom);
    img.fitWidth = img.width;
    img.fitHeight = img.height;

    if (frameHeight === 1) {
        subOffset = false;
        img.bodyh = img.height;
    }
    canvas.style.height = img.bodyh;

    // determine if we want to show the controller
    showController = true;

    if (!showController) {
        controllerContainer.style.display = "none";
        enableContainer.style.display = '';
    } else {
        enableContainer.style.display = "none";
        controllerContainer.style.display = '';
    }
    if (subOffset === true) {
        img.bodyh -= canvas.offsetTop;
    }

    // configure magnifier
    mag = canvas.magnifier;
    mag.xMultiplier = img.fullWidth / img.width;
    mag.yMultiplier = img.fullHeight / img.height;

    // if we are zoomed in at 100%, let's say so
    if (zoom === 1) {
        document.getElementById('zoomsel').selectedIndex = 5;
        magzoom = 5;
        haveZoomed = true;
    } else { // set our magzoom to the right value
        if (zoom < 0.25) {
            magzoom = 1;
        } else if (zoom < 0.50) {
            magzoom = 2;
        } else if (zoom < 0.75) {
            magzoom = 3;
        } else if (zoom < 1) {
            magzoom = 4;
        }
    }
    canvas.origZoom = magzoom;

    magimg = document.getElementById('buttonMag');
    if (zoom === 1) {
        magimg.enabled = false;
        magimg.src = 'img/img_magoff.gif';
    } else {
        magimg.enabled = true;
        magimg.src = 'img/img_mag.gif';
    }
    img.findPan();
    scaleImageMap(canvas);
}

function handleMouseDown(e) {
    'use strict';
    e = e || event;

    var img = this.image,
        mag,
        shadow,
        shadowSize,
        shadowImageSrc;

    if (this.mode === 'pan') { // pan mode
        if (img.canPan) {
            this.beingDragged = true;
            lastLeft = parseInt(img.style.left, 10);
            lastTop = parseInt(img.style.top, 10);
            lastX = e.clientX;
            lastY = e.clientY;
        }
        img.style.cursor = 'grabbing';
    } else { // magnify mode
        this.beingDragged = true;
        mag = this.magnifier;
        mag.startX = e.clientX;
        mag.startY = e.clientY;

        // the starting position of the magnifier... this places our cursor right in the middle of the magnifier
        mag.xPosition = mag.startX;
        mag.yPosition = mag.startY - this.offsetTop;

        shadow = mag.shadow;
        shadowSize = mag.size + 2 * shadowPadding;

        //// MSIE 5.x/6.x must be treated specially in order to make them use the PNG alpha channel
        shadowImageSrc = 'img/img_shadow.png';
        if (shadow.runtimeStyle) {
            shadow.runtimeStyle.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + shadowImageSrc + "', sizingMethod='scale')";
        } else {
            shadow.style.backgroundImage = 'url(' + shadowImageSrc + ')';
        }
        shadow.style.width = shadowSize + 'px';
        shadow.style.height = shadowSize + 'px';
        shadow.style.display = 'block';

        // msie counts the border as being part of the width
        if (mag.runtimeStyle) {
            mag.size += 2;
        }

        mag.style.width = mag.size + 'px';
        mag.style.height = mag.size + 'px';
        mag.style.display = 'block';
        mag.position();

    }
    return false;
}

function handleMouseMove(e) {
    'use strict';
    e = e || event;
    var img,
        top,
        left,
        magnifier;
    if (this.beingDragged) {
        if (this.mode === 'pan') {
            img = this.image;
            if (img.canPan) {

                // compute top and left coordinates
                top = lastTop + (e.clientY - lastY);
                if (top > 0) {
                    top = 0;
                }
                left = lastLeft + (e.clientX - lastX);
                if (left > 0) {
                    left = 0;
                }

                if (img.panH === true) {
                    if (img.width + left < img.bodyw) {
                        left = img.bodyw - img.width;
                    }
                }

                if (img.panV === true) {
                    if (img.height + top < img.bodyh) {
                        top = img.bodyh - img.height;
                    }
                }

                // pan the image
                if (img.panV) {
                    img.style.top = top + 'px';
                }
                if (img.panH) {
                    img.style.left = left + 'px';
                }
            }
        } else {
            magnifier = this.magnifier;
            magnifier.xPosition += e.clientX - magnifier.startX;
            magnifier.yPosition += e.clientY - magnifier.startY;

            magnifier.startX = e.clientX;
            magnifier.startY = e.clientY;

            magnifier.position();
        }
    }
    return false;
}

// the following three functions are the mouse handlers for panning around the image
function handleMouseUp(e) {
    'use strict';
    var img,
        mag;
    this.beingDragged = false;
    if (this.mode === 'pan') {
        // save these so we don't need to compute it later
        img = this.image;
        lastLeft = parseInt(img.style.left, 10);
        lastTop = parseInt(img.style.top, 10);

        img.style.cursor = 'all-scroll';
    } else {
        mag = this.magnifier;
        mag.shadow.style.display = 'none';
        mag.style.display = 'none';
    }
}

// Creates the actual imageViewer object in the iframe
// Parameters:
//   fullWidth - original width of the image
//   fullHeight - original height of the image
//   standalone - running in its own window?
//   mediaID, medialinkID, title - link info for the image
function imageViewer(baseID, imgURL, fullWidth, fullHeight, standalone, mediaID, medialinkID, title) {
    'use strict';
    var base = document.getElementById(baseID),
        canvas = document.createElement('div'), // create the image viewer itself
        img,
        map,
        i,
        area,
        magnifier,
        controller,
        pan,
        separator = [],
        magdown,
        magup,
        sel,
        zoomOption = [],
        magimg,
        newbtn,
        msg,
        shadow,
        controllerContainer,
        enableContainer,
        onoffbtn,
        closeimg;

    canvas.zoom = 1;
    canvas.id = 'imageviewer';
    canvas.className = 'canvas';
    canvas.style.position = 'absolute';
    canvas.style.overflow = 'hidden';
    canvas.style.width = '99.8%';
    canvas.beingDragged = false;
    canvas.mode = 'pan';
    lastLeft = lastTop = 0;

    img = document.createElement('img'); // define the image
    img.id = 'theimage';
    img.src = imgURL;
    img.useMap = '#imgMapViewer';
    img.style.position = 'relative';
    img.style.border = '0';
    img.style.left = img.style.top = '0px';

    img.style.cursor = 'all-scroll';

  // these are just dummy values... we'll set the correctly later
    img.width = 1;
    img.height = 1;
    img.fitWidth = 1;
    img.fitHeight = 1;
    img.fullWidth = fullWidth;
    img.fullHeight = fullHeight;
    img.findPan = determinePanning;
    img.origZoom = 1;

    canvas.image = img;

    // get the original coordinates for any image map
    map = document.getElementById('imgMapViewer');
    canvas.origmap = [];
    for (i = 0; i < map.areas.length; i += 1) {
        area = map.areas[i];
        canvas.origmap[i] = area.coords;
        if (standalone === true) {
            area.target = '_blank';
        } else {
            area.target = '_parent';
        }
    }

    magnifier = document.createElement('div');
    magnifier.id = 'Magnifier';
    magnifier.className = 'magnifier';
    // dummy values to be setup later
    magnifier.xMultiplier = 1;
    magnifier.yMultiplier = 1;
    magnifier.size = magnifierSize;
    magnifier.style.backgroundImage = 'url(' + imgURL + ')';
    magnifier.position = MagnifierPosition;
    magnifier.style.display = "none";
    canvas.magnifier = magnifier;

    controller = document.createElement('span');
    controller.id = 'imgViewerController';
    controller.className = 'controller';
    controller.style.width = '100%';

    pan = document.createElement('img'); // controller - pan button
    pan.id = 'buttonPan';
    pan.className = 'controllerPanSelected';
    pan.src = 'img/img_select.gif';
    pan.title = textSnippet('panmode');
    pan.alt = textSnippet('panmode');
    pan.onclick = ControllerPanMode;
    controller.appendChild(pan);
    controller.buttonPan = pan;

    separator[0] = document.createElement('img');
    separator[0].className = 'breakLine';
    separator[0].src = 'img/img_break.png';
    controller.appendChild(separator[0]);

    // controller - magnify down button
    magdown = document.createElement('img');
    magdown.id = 'magdown';
    magdown.className = 'controllerImage';
    magdown.src = 'img/img_magdown.gif';
    magdown.style.cursor = 'pointer';
    magdown.onclick = ControllerMagDown;
    magdown.title = textSnippet('zoomout');
    magdown.alt = textSnippet('zoomout');
    controller.appendChild(magdown);
    controller.magdown = magdown;

    // controller - magnify up button
    magup = document.createElement('img');
    magup.id = 'magup';
    magup.className = 'controllerImage';
    magup.src = 'img/img_magup.gif';
    magup.style.cursor = 'pointer';
    magup.onclick = ControllerMagUp;
    magup.title = textSnippet('zoomin');
    magup.alt = textSnippet('zoomin');
    controller.appendChild(magup);
    controller.magup = magup;

    // controller - zoom levels
    sel = document.createElement('select');
    sel.id = 'zoomsel';
    sel.className = 'zoomSelector custom-select';
    zoomOption[1] = document.createElement('option');
    zoomOption[1].text = textSnippet('fitwidth');
    zoomOption[1].value = 0;
    zoomOption[2] = document.createElement('option');
    zoomOption[2].text = textSnippet('fitheight');
    zoomOption[2].value = -1;
    zoomOption[3] = document.createElement('option');
    zoomOption[3].text = '25%';
    zoomOption[3].value = 0.25;
    zoomOption[4] = document.createElement('option');
    zoomOption[4].text = '50%';
    zoomOption[4].value = 0.50;
    zoomOption[5] = document.createElement('option');
    zoomOption[5].text = '75%';
    zoomOption[5].value = 0.75;
    zoomOption[6] = document.createElement('option');
    zoomOption[6].text = '100%';
    zoomOption[6].value = 1;
    zoomOption[7] = document.createElement('option');
    zoomOption[7].text = '125%';
    zoomOption[7].value = 1.25;
    zoomOption[8] = document.createElement('option');
    zoomOption[8].text = '150%';
    zoomOption[8].value = 1.5;
    zoomOption[9] = document.createElement('option');
    zoomOption[9].text = '175%';
    zoomOption[9].value = 1.75;
    zoomOption[10] = document.createElement('option');
    zoomOption[10].text = '200%';
    zoomOption[10].value = 2;

    if (document.all) {
        sel.add(zoomOption[1]);
        sel.add(zoomOption[2]);
        sel.add(zoomOption[3]);
        sel.add(zoomOption[4]);
        sel.add(zoomOption[5]);
        sel.add(zoomOption[6]);
        sel.add(zoomOption[7]);
        sel.add(zoomOption[8]);
        sel.add(zoomOption[9]);
        sel.add(zoomOption[10]);
    } else {
        sel.add(zoomOption[1], null);
        sel.add(zoomOption[2], null);
        sel.add(zoomOption[3], null);
        sel.add(zoomOption[4], null);
        sel.add(zoomOption[5], null);
        sel.add(zoomOption[6], null);
        sel.add(zoomOption[7], null);
        sel.add(zoomOption[8], null);
        sel.add(zoomOption[9], null);
        sel.add(zoomOption[10], null);
    }
    sel.onchange = ControllerZoomLevel;

    controller.appendChild(sel);
    controller.zoomsel = sel;

    // controller - magnifier button
    magimg = document.createElement('img');
    magimg.id = 'buttonMag';
    magimg.className = 'controllerMag';
    magimg.onclick = ControllerMagMode;
    magimg.title = textSnippet('magmode');
    magimg.alt = textSnippet('magmode');
    controller.appendChild(magimg);
    controller.buttonMag = magimg;

    if (standalone === false) {
        // controller - second break line
        separator[1] = document.createElement('img');
        separator[1].className = 'breakLine';
        separator[1].src = 'img/img_break.png';
        controller.appendChild(separator[1]);

        // controller - new window button
        newbtn = document.createElement('button');
        newbtn.className = 'controllerButton';
        newbtn.innerHTML = textSnippet('newwin');
        newbtn.imgURL = encodeURI(imgURL);
        newbtn.mediaID = mediaID;
        newbtn.medialinkID = medialinkID;
        newbtn.newwintitle = title;
        newbtn.title = textSnippet('opennw');
        newbtn.onclick = ControllerNewWin;
        controller.appendChild(newbtn);
    }
    // provide some status info
    // controller - third? break line
    separator[2] = document.createElement('img');
    separator[2].className = 'breakLine';
    separator[2].src = 'img/img_break.png';
    controller.appendChild(separator[2]);
    msg = document.createElement('span');
    msg.innerHTML = textSnippet('pan');
    msg.className = 'controllerText';
    msg.id = "msg";
    controller.appendChild(msg);
    controller.msg = msg;

    // shadow
    shadow = document.createElement('div');
    shadow.id = 'MagnifierShadow';
    shadow.className = 'magnifierShadow';
    shadow.style.display = 'none';
    magnifier.shadow = shadow;

    // point objects at each other
    canvas.controller = controller;
    controller.canvas = canvas;

    // Controller container... gives us some control over the visual of this area
    controllerContainer = document.createElement('div');
    controllerContainer.id = 'ctrlcontainer';
    controllerContainer.className = 'controllerContainer';
    controllerContainer.style.display = "none";
    controllerContainer.style.height = "36px";

    enableContainer = document.createElement('div');
    enableContainer.id = 'encontainer';
    enableContainer.style.backgroundColor = canvasBackground;
    enableContainer.style.height = "36px";

    // put a button at the bottom to turn on/off the controller
    onoffbtn = document.createElement('button');
    onoffbtn.className = 'controllerButton';
    onoffbtn.innerHTML = textSnippet('imgctrls');
    onoffbtn.title = textSnippet('vwrctrls');
    onoffbtn.onclick = function () {
        enableContainer.style.display = "none";
        controllerContainer.style.display = '';
    };
    enableContainer.appendChild(onoffbtn);

    // controller - close button
    closeimg = document.createElement('button');
    closeimg.id = 'buttonClose';
    closeimg.className = 'close';
    closeimg.title = textSnippet('vwrclose');
    closeimg.innerHTML = "<span aria-hidden='true'>&times;</span>";
    closeimg.onclick = function () {
        enableContainer.style.display = '';
        controllerContainer.style.display = "none";

        // need to shrink (or expand) the image to fit height (which will automatically fit width if they are running with height=1)
        zoomImage(0, canvas);
        controller.zoomsel.selectedIndex = 1;
    };
    controllerContainer.appendChild(closeimg);
    controllerContainer.appendChild(controller);

    canvas.appendChild(img);
    canvas.appendChild(shadow);
    canvas.appendChild(magnifier);

    base.appendChild(enableContainer);
    base.appendChild(controllerContainer);
    base.appendChild(canvas);

    base.resize = sizeController;

    // install our mouse handlers
    canvas.onmousedown = handleMouseDown;
    canvas.onmousemove = handleMouseMove;
    canvas.onmouseup = handleMouseUp;

    return this;
}
