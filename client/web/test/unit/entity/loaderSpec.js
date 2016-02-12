'use strict';

var definitionLoader = require("../../../js/entity/definitionLoader");
var entityLoader = require("../../../js/entity/loader");
var netric = require("../../../js/main");

/**
 * Test loading definitions asynchronously and make sure it gets cached for future requests
 */
describe("Get Entity Asynchronously", function() {
	var entity = null;

	beforeEach(function(done) {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base/";
		entityLoader.get("customer", "1", function(ent){
			entity = ent;
			done();
		});
	});

	it("should have loaded the entity object", function(done) {

		expect(entity).not.toBeNull();
		done();

	});

	it("should have loaded and cached the entity definition", function(done) {

		expect(entity.def).not.toBeNull();
		expect(definitionLoader.getCached("customer")).not.toBeNull();
		done();

	});

	it("should have loaded the right data", function(done) {

		expect(parseInt(entity.id)).toEqual(1);
		expect(entity.objType).toEqual("customer");

		// TODO: views

		done();

	});

	it("should have cached the entity object", function(done) {

		expect(entityLoader.getCached("customer", "1")).not.toBeNull();
		done();

	});

});

/**
 * Check to make sure expected public varibles are set
 */
describe("Get Entity Non Async", function() {

	beforeEach(function() {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base/";
	});

	it("can fallback to loading entity synchronously", function() {

		// Clear cache which forces the loader to get the entity through BackendRequest
		entityLoader.entities_ = new Object();
		var entity = entityLoader.get("customer", "1"); // No callback forces sync load
		expect(entity).not.toBeNull();
	});

	it("should have loaded the right data", function() {

		// Clear cache which forces the loader to get the entity through BackendRequest
		entityLoader.entities_ = new Object();
		var entity = entityLoader.get("customer", "1"); // No callback forces sync load
		expect(entity.objType).toEqual("customer");
		expect(parseInt(entity.id)).toEqual(1);
	});

	it("should have cached the entity object", function() {

		// Check the private definitions_ property of the loader
		entityLoader.entities_ = new Object();
		var entity = entityLoader.get("customer", "1"); // No callback forces sync load
		expect(entityLoader.getCached("customer", "1")).not.toBeNull();

	});

});
