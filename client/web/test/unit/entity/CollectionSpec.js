'use strict';

/**
 * Test loading definitions asynchronously and make sure it gets cached for future requests
 */
describe("Entity Collection", function() {

    /*
    // Setup test entity
    beforeEach(function() {

    });
    */

    it("can take andWhere conditions", function() {
        var collection = new netric.entity.Collection("customer");
        collection.where("first_name").equalTo("test");
        expect(collection.getConditions().length).toEqual(1);
    });

    it("can take andWhere conditions", function() {
        var collection = new netric.entity.Collection("customer");
        collection.where("first_name").equalTo("test");
        collection.orWhere("last_name").equalTo("test");
        expect(collection.getConditions().length).toEqual(2);
    });

    it("sets the limit", function() {
        var collection = new netric.entity.Collection("customer");
        collection.setLimit(50);
        expect(collection.getLimit()).toEqual(50);
    });

    it("sets the offset", function() {
        var collection = new netric.entity.Collection("customer");
        collection.setOffset(50);
        expect(collection.getOffset()).toEqual(50);
    });

    it("sets order by", function() {
        var collection = new netric.entity.Collection("customer");
        collection.setOrderBy("first_name", netric.entity.Collection.orderByDir.DESC);
        var orderBy = collection.getOrderBy();
        expect(orderBy[0].field).toEqual("first_name");
        expect(orderBy[0].direction).toEqual(netric.entity.Collection.orderByDir.DESC);
    });

});

/**
 * Test loading collections asynchronously
 */
describe("Entity Collection Loading Asynchronously", function() {
    var collection = new netric.entity.Collection("customer");

    beforeEach(function(done) {
        // Set test base where karma unit tests are hosted
        netric.server.host = "base/";
        collection.load(function(collection){
            done();
        });
    });

    it("Should have loaded entities object", function(done) {

        expect(collection.getTotalNum()).toEqual(3);
        done();

    });

    it("Should have loaded entities object", function(done) {

        expect(collection.getEntities().length).toEqual(3);
        done();

    });

    /*
    it("Should have cached the definition object", function(done) {

        // Check the private definitions_ property of the loader
        expect(netric.entity.definitionLoader.definitions_["customer"]).not.toBeNull();
        expect(netric.entity.definitionLoader.getCached("customer")).not.toBeNull();
        done();

    });
    */

});