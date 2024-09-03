/*jslint white: true, browser: true, undef: true, nomen: true, eqeqeq: true, plusplus: false, bitwise: true, regexp: true, strict: true, newcap: true, immed: true, maxerr: 14 */
/*global window: false, REDIPS: true */

/* enable strict mode */
"use strict";

// create redips container
var redips = {};

redips.disableDrag = false;

// configuration
redips.configuration = function () {
    redips.ajaxSave     = "https://" + window.location.host;
    redips.request      = null;
};


// redips initialization
redips.init = function () {
	// reference to the REDIPS.drag object
	var	rd = REDIPS.drag;

    // set script configuration
    redips.configuration();

    // initialization
	rd.init();

	// REDIPS.drag settings
	rd.dropMode 		= 'switch';		// dragged elements source and target cell are switched
	rd.hover.colorTd 	= '#ffff00'; 	// set hover color

    // set error handler for AJAX call
    rd.error.ajax = function (xhr) {
        // display error message
        window.alert('AJAX error: [' + xhr.status + '] ' + xhr.statusText);

        // Disable drag and force a page re-load
        redips.disableDrag = true;

        // return false to stop calling callback function
        return false;
    };

    // element is dropped
	rd.event.dropped = function () {
		var	params = '',    // Ajax params
            sessionIdElement,
            sessionId,
            tempTitle;

        // disable drag/drop (force page reload to re-enable)
        // redips.enableDrag(false, '#redips-drag');
        // disable drag/drop (force page reload to re-enable)
        if (redips.disableDrag === true) {
            window.alert('Drag disabled due to earlier error.  Changes are not being saved: Re-load page');
            // redips.drag.enableDrag(false, td);
            return false;
        }

        redips.printMessage("hello world");

        // print message only if target and source table cell differ
		if (rd.td.target !== rd.td.source) {
            sessionIdElement    = document.getElementById('s');
            sessionId           = sessionIdElement.getAttribute("data-sessionid");
            params              = 'gameTimeId1=' + rd.td.target.id + '&gameTimeId2=' + rd.td.source.id + '&sessionId=' + sessionId;

            // Swap the cell background colors and titles
            tempTitle                          = rd.td.source.title;
            rd.td.source.title                 = rd.td.target.title;
            rd.td.target.title                 = tempTitle;

            // Swap the lock button displays (for when moving to an empty cell - noop otherwise)
            var sourceButtonElement = document.getElementById('button' + rd.td.source.id);
            var targetButtonElement = document.getElementById('button' + rd.td.target.id);
            var tempDisplay         = sourceButtonElement.style.display;
            var tempGameId          = sourceButtonElement.dataset.gameid;

            sourceButtonElement.style.display   = targetButtonElement.style.display;
            sourceButtonElement.dataset.gameid  = targetButtonElement.dataset.gameid;
            targetButtonElement.style.display   = tempDisplay;
            targetButtonElement.dataset.gameid  = tempGameId;

            REDIPS.drag.ajaxCall(redips.ajaxSave +  "/api/swap", redips.handler, {method: 'POST', data: params});
        }
    };
};

// method parses form elements and submits to the server
redips.toggleGameLock = function (gameTimeId) {
    var buttonElement       = document.getElementById('button' + gameTimeId);
    var buttonImageElement  = document.getElementById('lockButton' + gameTimeId);
    var unlockBgColor       = buttonElement.getAttribute("data-bgcolor");
    var sessionId           = buttonElement.getAttribute("data-sessionid");
    var gameId              = buttonElement.getAttribute("data-gameid");
    var params              = "gameId=" + gameId + "&sessionId=" + sessionId;
    var divElement          = document.getElementById("div" + gameId);

    if (divElement.style.backgroundColor === 'orange') {
        divElement.style.backgroundColor   = unlockBgColor;
        buttonImageElement.src              = '/images/unlock.jpeg';
    } else {
        divElement.style.backgroundColor   = 'orange';
        buttonImageElement.src              = '/images/lock.jpeg';
    }

    // make AJAX call and set redips.handler as callback function
    REDIPS.drag.ajaxCall(redips.ajaxSave  + "/api/toggle", redips.handler, {method: 'POST', data: params});
};

// method parses form elements and submits to the server
redips.toggleGameTimeLock = function (gameTimeId) {
    var buttonElement       = document.getElementById('button' + gameTimeId);
    var buttonImageElement  = document.getElementById('lockButton' + gameTimeId);
    var unlockBgColor       = buttonElement.getAttribute("data-bgcolor");
    var sessionId           = buttonElement.getAttribute("data-sessionid");
    var params              = "gameTimeId=" + gameTimeId + "&sessionId=" + sessionId;
    var cellElement         = document.getElementById(gameTimeId);

    if (cellElement.style.backgroundColor === 'red') {
        cellElement.style.backgroundColor   = unlockBgColor;
        buttonImageElement.src              = '/images/unlock.jpeg';
    } else {
        cellElement.style.backgroundColor   = 'red';
        buttonImageElement.src              = '/images/lock.jpeg';
    }

    // make AJAX call and set redips.handler as callback function
    REDIPS.drag.ajaxCall(redips.ajaxSave  + "/api/toggleGameTime", redips.handler, {method: 'POST', data: params});
};

// AJAX callback function
redips.handler = function (xhr) {
    var result = xhr.responseText.trim().substr(0, 7);
    if (result !== 'SUCCESS') {
        redips.disableDrag = true;
    }
    window.alert(xhr.responseText);
};

// print message
redips.printMessage = function (message) {
	document.getElementById('message').innerHTML = message;
};

// method displays or hides "Save" button
redips.button = function () {
    // set reference to the "Save" button
    var button = document.getElementById('save-button');
    // if OL element contains LI (one or more)
    if (redips.ol.children.length > 0) {
        button.style.display = 'block';
    }
    // LI is empty - hide "Save button"
    else {
        button.style.display = 'none';
    }
};

// add onload event listener
if (window.addEventListener) {
	window.addEventListener('load', redips.init, false);
}
else if (window.attachEvent) {
	window.attachEvent('onload', redips.init);
}