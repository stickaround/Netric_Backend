/**
 * @fileOverview Events is just a wrapper to alib.events
 *
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com;
 * 			Copyright (c) 2013 Aereus Corporation. All rights reserved.
 */

/**
 * Create events namespace
 *
 * @object
 */
var events = {};

/**
 * Add event listener to an element
 *
 * @var {mixed} obj Either a DOM element or a custom Object to attache event to
 * @var {string} eventName The name of the event to listen for
 * @var {function|Object} callback Can be a function or an object with {context:(object reference), method:(string name)}
 * @var {Object} data Optional data to pass to event
 */
events.listen = function(obj, eventName, callBack, data)
{
    alib.events.listen(obj, eventName, callBack, data);
}

/**
 * Removes an event from the object
 */
events.unlisten = function(obj, eventName, callBack)
{
    alib.events.unlisten(obj, eventName, callBack);
}

/**
 * Stop an event from bubbling up the event DOM
 */
events.stop = function(evt)
{
}

/**
 * Manually trigger an event by name if event type is not an included DOM event (like a custom event)
 *
 * This will not be needed for DOM dispatched events so only use it when defining custom events.
 *
 * @var {mixed} obj The context of the event being fired
 * @var {string} eventName The name of the event being fired
 * @var {Object} data Optional data to be passed to the callback in event.data
 */
events.triggerEvent = function(obj, eventName, data)
{
    alib.events.triggerEvent(obj, eventName, data);
}

module.exports = events;