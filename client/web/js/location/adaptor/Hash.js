/**
* @fileOverview Location adaptor that uses hash
*
* @author:  Sky Stebnicki, sky.stebnicki@aereus.com; 
*       Copyright (c) 2015 Aereus Corporation. All rights reserved.
*/

netric.declare("netric.location.adaptor.Hash");

netric.require("netric");
netric.require("netric.adaptor");

/**
 * Create global namespaces
 */
netric.location = netric.location || {};
netric.location.adaptor = netric.location.adaptor || {};

/**
 * Get the window has adaptor
 *
 * @constructor
 */
netric.location.adaptor.Hash = function() {

  /**
   * Type of action last performed
   * 
   * @type {netric.location.actions}
   */
  this.actionType_ = null;

  // Begin listening for hash changes
  alib.events.listen(window, "hashchange", function(evt) {
    // Check to see if we changes
    if (this.ensureSlash_()){
      // If we don't have an actionType_ then all we know is the hash
      // changed. It was probably caused by the user clicking the Back
      // button, but may have also been the Forward button or manual
      // manipulation. So just guess 'pop'.
      this.notifyChange_(this.actionType_ || netric.location.actions.POP);
      this.actionType_ = null;  
    }
  }.bind(this));
  
}

/**
 * A new location has been pushed onto the stack
 *
 * @param {string} path The path to push onto the history statck
 */
netric.location.adaptor.Hash.prototype.push = function (path) {
  this.actionType_ = netric.location.actions.PUSH;
  window.location.hash = netric.location.path.encode(path);
}

/**
 * The current location should be replaced
 *
 * @param {string} path The path to push onto the history statck
 */
netric.location.adaptor.Hash.prototype.replace = function (path) {
  this.actionType_ = netric.location.actions.REPLACE;
  window.location.replace(window.location.pathname + window.location.search + '#' + netric.location.path.encode(path));
}

/**
 * The most recent path should be removed from the history stack
 */
netric.location.adaptor.Hash.prototype.pop = function () {
    this.actionType_ = netric.location.actions.POP;
    History.back();
}

/**
 * Get the current path from the 'hash' including query string
 */
netric.location.adaptor.Hash.prototype.getCurrentPath = function () {
  return netric.location.path.decode(
    // We can't use window.location.hash here because it's not
    // consistent across browsers - Firefox will pre-decode it!
    window.location.href.split('#')[1] || ''
  );
}

/**
 * Assure that the path begins with a slash '/'
 */
netric.location.adaptor.Hash.prototype.ensureSlash_ = function() {
  var path = this.getCurrentPath();

  if (path.charAt(0) === '/')
    return true;

  this.replace('/' + path);

  return false;
}

/**
 * Notify the listeners that the location has changed
 */
netric.location.adaptor.Hash.prototype.notifyChange_ = function(type) {
  if (type === netric.location.actions.PUSH)
    History.length += 1;

  var data = {
    path: this.getCurrentPath(),
    type: type
  };

  alib.events.triggerEvent(this, "pathchange", data);
}
