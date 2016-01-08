'use strict';

var EntityCollection = require("../../../js/entity/Collection");
var netric = require("../../../js/main");

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
        var collection = new EntityCollection("customer");
        collection.where("first_name").equalTo("test");
        expect(collection.getConditions().length).toEqual(1);
    });

    it("can take andWhere conditions", function() {
        var collection = new EntityCollection("customer");
        collection.where("first_name").equalTo("test");
        collection.orWhere("last_name").equalTo("test");
        var conditions = collection.getConditions();
        expect(conditions.length).toEqual(2);
        expect(conditions[0].fieldName).toEqual("first_name");
        expect(conditions[0].operator).toEqual("is_equal");
        expect(conditions[0].bLogic).toEqual("and");
        expect(conditions[0].value).toEqual("test");
    });

    it("sets the limit", function() {
        var collection = new EntityCollection("customer");
        collection.setLimit(50);
        expect(collection.getLimit()).toEqual(50);
    });

    it("sets the offset", function() {
        var collection = new EntityCollection("customer");
        collection.setOffset(50);
        expect(collection.getOffset()).toEqual(50);
    });

    it("sets order by", function() {
        var collection = new EntityCollection("customer");
        collection.setOrderBy("first_name", EntityCollection.orderByDir.DESC);
        var orderBy = collection.getOrderBy();
        expect(orderBy[0].field).toEqual("first_name");
        expect(orderBy[0].direction).toEqual(EntityCollection.orderByDir.DESC);
    });

});

/**
 * Test loading collections asynchronously
 */
describe("Entity Collection Loading Asynchronously", function() {

    it("Should have loaded entities object", function(done) {
        var collection = new EntityCollection("customer");
        netric.server.host = "base/";
        collection.load(function(collection){
            expect(collection.getTotalNum()).toEqual(3);
            expect(collection.getEntities().length).toEqual(3);
        });
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