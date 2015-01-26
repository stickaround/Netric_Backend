/**
 * @fileoverview This is a test controller used primarily for unit tests
 */
netric.declare("netric.controller.TestController");

netric.require("netric.controller");
netric.require("netric.controller.AbstractController");

/**
 * Test controller
 */
netric.controller.TestController = function() {}

/**
 * Extend base controller class
 */
netric.inherits(netric.controller.TestController, netric.controller.AbstractController);


/**
 * Function called when controller is first loaded
 */
netric.controller.TestController.prototype.onload = function() { }

/**
 * Render the contoller into the dom
 */
netric.controller.TestController.prototype.onload = function() { }