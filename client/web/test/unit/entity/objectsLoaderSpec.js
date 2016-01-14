'use strict';

var objectsLoader = require("../../../js/entity/objectsLoader");
var netric = require("../../../js/main");

/**
 * Test loading objects asynchronously and make sure it gets cached for future requests
 */
describe("Get Objects Asynchronously", function () {
    var objects = null;

    beforeEach(function (done) {
        // Set test base where karma unit tests are hosted
        netric.server.host = "base/";
        objectsLoader.get(function (result) {
            objects = result;
            done();
        });
    });

    it("Should have loaded the objects object", function (done) {
        expect(objects).not.toBeNull();
        done();
    });

    it("Should have cached the object", function (done) {
        // Check the private _objects property of the loader
        expect(objectsLoader._objects).not.toBeNull();
        done();

    });
});


/**
 * Test loading of objects Non Asynchronously and make sure it gets cached for future requests
 */
describe("Get objects Non Async", function() {

    beforeEach(function() {
        // Set test base where karma unit tests are hosted
        netric.server.host = "base/";
    });

    it("Can fallback to loading objects synchronously", function() {

        // Clear cache which forces the loader to get the objects through BackendRequest
        objectsLoader._objects = null;
        var objects = objectsLoader.get(); // No callback forces sync load
        expect(objects).not.toBeNull();
    });

    it("Should have cached the objects", function() {

        // Check the private _objects property of the loader
        objectsLoader._objects = "";
        var objects = objectsLoader.get(); // No callback forces sync load
        expect(objectsLoader._objects).not.toBeNull();

    });

});