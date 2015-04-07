/**
 * @fileoverview This is a test controller used primarily for unit tests
 */
'use strict';

var netric = require("../base");
var AbstractController = require("./AbstractController");

/**
 * Test controller
 */
var TestController = function() { /* Should have details */ }

/**
 * Extend base controller class
 */
netric.inherits(TestController, AbstractController);

/**
 * Function called when controller is first loaded
 */
TestController.prototype.onload = function() { }

/**
 * Render the contoller into the dom
 */
TestController.prototype.onload = function() { }

module.exports = TestController;
