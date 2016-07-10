'use strict';

var log = require("../../src/log.js");

/**
 * Check to make sure expected public varibles are set
 */
describe("Logging class:", function() {
    it("Can pass through to console in test mode", function() {
        log.info("Did something");
    });

    it("Can pass through to console with multiple args", function() {
        log.info("Did", "something", {prop:"val"});
    });
});