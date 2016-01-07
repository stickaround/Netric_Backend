/**
 * Handle lisenting custom events and bubbling them up
 *
 * @jsx React.DOM
 */
'use strict';

/**
 * Handle listening custom events
 */
var CustomEventListen = {

    /**
     * Listen a custom event
     *
     * @param {string} type The name of the event type
     * @param {Object} opt_func Optional function that will be executed if the event is triggered
     */
    listenCustomEvent: function(type, opt_func) {
        var evtFunc = opt_func || {};
        if (this.props.eventsObj) {
            alib.events.listen(this.props.eventsObj, type, evtFunc);
        } else {
            throw 'An eventsObj has not been passed by the parent of this componenet.';
        }
    }

};

module.exports = CustomEventListen;
