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
            sessionId;

        // disable drag/drop (force page reload to re-enable)
        // redips.enableDrag(false, '#redips-drag');
        // disable drag/drop (force page reload to re-enable)
        if (redips.disableDrag === true) {
            window.alert('Drag disabled due to earlier error.  Changes are not being saved: Re-load page');
            // redips.drag.enableDrag(false, td);
            return false;
        }

        // print message only if target and source table cell differ
        if (rd.td.target !== rd.td.source) {
            sessionIdElement    = document.getElementById('s');
            sessionId           = sessionIdElement.getAttribute("data-sessionid");
            params              = 'sessionId=' + sessionId + '&cell2Data=' + rd.td.source.id + '&cell1Data=' + rd.td.target.id;

            // Swap the cell ref identifiers (data = <rowType_gameId_refId>
            var fromElements = rd.td.source.id.split('_');
            var toElements   = rd.td.target.id.split('_');

            // Swap the refId's or assign the refId to the new cell if cloning
            // Cloning has a 0 for the gameId: id = <rowType>_<gameId>_<refId>
            var newFromId = "";
            var newToId   = "";
            if (fromElements.length >= 3 && fromElements[1] != "0") {
                newFromId = fromElements[0] + '_' + fromElements[1] + '_' + toElements[2];
                newToId   = toElements[0] + '_' + toElements[1] + '_' + fromElements[2];

                if (fromElements.length >= 4) {
                    // Append "squad" element
                    newFromId = newFromId + '_' + toElements[3];
                    newToId   = newToId + '_' + fromElements[3];
                }
            } else {
                newFromId = rd.td.source.id;
                newToId   = toElements[0] + '_' + toElements[1] + '_' + fromElements[2];

                if (fromElements.length >= 4) {
                    // Append "squad" element
                    newToId   = newToId + '_' + fromElements[3];
                }
            }

            var sourceButtonElement = document.getElementById(rd.td.source.id);
            var targetButtonElement = document.getElementById(rd.td.target.id);

            sourceButtonElement.id = newFromId;
            targetButtonElement.id = newToId;

            // Swap the cell background colors and titles
            // tempTitle                          = rd.td.source.title;
            // rd.td.source.title                 = rd.td.target.title;
            // rd.td.target.title                 = tempTitle;

            // Swap the lock button displays (for when moving to an empty cell - noop otherwise)
            // var sourceButtonElement = document.getElementById('button' + rd.td.source.id);
            // var targetButtonElement = document.getElementById('button' + rd.td.target.id);
            // var tempDisplay         = sourceButtonElement.style.display;
            // var tempGameId          = sourceButtonElement.dataset.gameid;

            // sourceButtonElement.style.display   = targetButtonElement.style.display;
            // sourceButtonElement.dataset.gameid  = targetButtonElement.dataset.gameid;
            // targetButtonElement.style.display   = tempDisplay;
            // targetButtonElement.dataset.gameid  = tempGameId;

            REDIPS.drag.ajaxCall(redips.ajaxSave +  "/api/refAssign", redips.handler, {method: 'POST', data: params});
        }
    };

    rd.event.deleted = function () {
        var	params,
            sessionIdElement,
            sessionId;

        sessionIdElement    = document.getElementById('s');
        sessionId           = sessionIdElement.getAttribute("data-sessionid");
        params              = 'sessionId=' + sessionId + '&cell1Data=' + rd.td.source.id;

        REDIPS.drag.ajaxCall(redips.ajaxSave +  "/api/refDelete", redips.handler, {method: 'POST', data: params});
    };
};

// AJAX callback function
redips.handler = function (xhr) {
    var result = xhr.responseText.trim().substr(0, 7);
    if (result !== 'SUCCESS') {
        redips.printMessage(xhr.responseText, "red");
        window.alert(xhr.responseText);
        redips.disableDrag = true;
    } else {
        redips.printMessage(xhr.responseText, "green");
    }
};

// print message
redips.printMessage = function (message, color) {
    var messageElement = document.getElementById('message');

    messageElement.style.backgroundColor    = color;
    messageElement.innerHTML                = message;
};

// add onload event listener
if (window.addEventListener) {
    window.addEventListener('load', redips.init, false);
}
else if (window.attachEvent) {
    window.attachEvent('onload', redips.init);
}