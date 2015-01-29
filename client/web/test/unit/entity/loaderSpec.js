'use strict';

/**
 * Test loading definitions asynchronously and make sure it gets cached for future requests
 */
describe("Get Entity Asynchronously", function() {
	var entity = null;

	beforeEach(function(done) {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base";
		netric.entity.loader.get("customer", "1", function(ent){
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
		expect(netric.entity.definitionLoader.getCached("customer")).not.toBeNull();
		done();

	});

	it("should have loaded the right data", function(done) {
		
		expect(entity.id).toEqual("1");
		expect(entity.objType).toEqual("customer");
		
		// TODO: views

		done();

	});

	it("should have cached the entity object", function(done) {
		
		expect(netric.entity.loader.getCached("customer", "1")).not.toBeNull();
		done();

	});

});

/**
 * Check to make sure expected public varibles are set
 */
describe("Get Entity Non Async", function() {

	beforeEach(function() {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base";
	});
	
	it("can fallback to loading entity synchronously", function() {
		
		// Clear cache which forces the loader to get the entity through BackendRequest
		netric.entity.loader.entities_ = new Object();
		var entity = netric.entity.loader.get("customer", "1"); // No callback forces sync load
		expect(entity).not.toBeNull();
	});

	it("should have loaded the right data", function() {
		
		// Clear cache which forces the loader to get the entity through BackendRequest
		netric.entity.loader.entities_ = new Object();
		var entity = netric.entity.loader.get("customer", "1"); // No callback forces sync load
		expect(entity.objType).toEqual("customer");
		expect(entity.id).toEqual("1");
	});

	it("should have cached the entity object", function() {
		
		// Check the private definitions_ property of the loader
		netric.entity.loader.entities_ = new Object();
		var entity = netric.entity.loader.get("customer", "1"); // No callback forces sync load
		expect(netric.entity.loader.getCached("customer", "1")).not.toBeNull();

	});

});