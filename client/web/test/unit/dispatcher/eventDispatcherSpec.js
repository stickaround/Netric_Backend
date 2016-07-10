'use strict';

var eventDispatcher = require("../../../src/dispatcher/eventDispatcher");

describe("Test global application events", function() {

    it("can fire events with payload", function() {

        var myPropertyToChange = false;
        var testPayload = "my test data";

        eventDispatcher.listen("my/test/event", function(payload){
            myPropertyToChange = payload;
        });

        // Trigger the event which should immediately update the test property
        eventDispatcher.triggerEvent("my/test/event", testPayload);

        // Make sure the property was changed by the listener
        expect(myPropertyToChange).toEqual(testPayload);

    });

});