'use strict';

var groupingLoader = require("../../../src/entity/groupingLoader");

describe("Get Groupings Asynchronously", function() {
    var groupings = null;

    beforeEach(function(done) {
        groupingLoader.get("customer", "groups", function(grps){
            groupings = grps;
            done();
        });
    });

    it("Should have loaded the groupings object", function(done) {
        expect(groupings).not.toBeNull();
        done();
    });

    it("Should have returned the object type and field", function(done) {
        expect(groupings.objType).toEqual("customer");
        expect(groupings.fieldName).toEqual("groups");
        done();
    });
});
