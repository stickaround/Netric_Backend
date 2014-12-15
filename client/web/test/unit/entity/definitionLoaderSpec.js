'use strict';

/**
 * Test loading definitions asynchronously and make sure it gets cached for future requests
 */
describe("Get Definition Asynchronously", function() {
	var definition = null;

	beforeEach(function(done) {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base/";
		netric.entity.definitionLoader.get("customer", function(def){
			definition = def;
			done(); 
		});
	});
	
	it("Should have loaded the definition object", function(done) {
		
		expect(definition).not.toBeNull();
		done();

	});

	it("Should have loaded the right data", function(done) {
		
		expect(definition.id).toEqual(1);
		expect(definition.objType).toEqual("customer");
		expect(definition.title).toEqual("Customer");
		expect(definition.revision).toEqual(10);
		expect(definition.isPrivate).toEqual(true);
		// TODO: recur rules
		expect(definition.parentField).toEqual("fake_field");
		expect(definition.listTitle).toEqual("Customers");
		expect(definition.icon).toEqual("customIcon");
		expect(definition.browserMode).toEqual("table");
		expect(definition.browserBlankContent).toEqual("No items found");
		expect(definition.system).toEqual(true);

		// Test fields
		var idField = definition.getField("id");
		expect(idField.type).toEqual("number");
		
		// TODO: views

		done();

	});

	it("Should have cached the definition object", function(done) {
		
		// Check the private definitions_ property of the loader
		expect(netric.entity.definitionLoader.definitions_["customer"]).not.toBeNull();
		done();

	});

});

/**
 * Check to make sure expected public varibles are set
 */
describe("Get Definition Non Async", function() {

	beforeEach(function() {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base/";
	});
	
	it("Can fallback to loading definition synchronously", function() {
		
		// Clear cache which forces the loader to get the definition through BackendRequest
		netric.entity.definitionLoader.definitions_ = new Array();
		var definition = netric.entity.definitionLoader.get("customer"); // No callback forces sync load
		expect(definition).not.toBeNull();
	});

	it("Should have loaded the right data", function() {
		
		// Clear cache which forces the loader to get the definition through BackendRequest
		netric.entity.definitionLoader.definitions_ = new Array();
		var definition = netric.entity.definitionLoader.get("customer"); // No callback forces sync load
		expect(definition.objType).toEqual("customer");
		expect(definition.title).toEqual("Customer");
	});

	it("Should have cached the definition object", function() {
		
		// Check the private definitions_ property of the loader
		netric.entity.definitionLoader.definitions_ = new Array();
		var definition = netric.entity.definitionLoader.get("customer"); // No callback forces sync load
		expect(netric.entity.definitionLoader.definitions_["customer"]).not.toBeNull();

	});

});