'use strict';

var Definition = require("../../../src/entity/Definition");
var definitionLoader = require("../../../src/entity/definitionLoader");
var Entity = require("../../../src/entity/Entity");
var netric = require("../../../src/main");

/**
 * Test loading definitions asynchronously and make sure it gets cached for future requests
 */
describe("Get Definition Asynchronously", function() {
	var definition = null;

	beforeEach(function(done) {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base/";
		definitionLoader.get("customer", function(def){
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
		
		// Make sure views were loaded
		var views = definition.getViews();
		expect(views.length).toBeGreaterThan(0);

		// Get the fields by type
		var filteredFields = definition.getFieldsByType("number");
		expect(filteredFields.length).toBeGreaterThan(0);

		done();

	});

	it("Should have cached the definition object", function(done) {
		
		// Check the private _definitions property of the loader
		expect(definitionLoader._definitions["customer"]).not.toBeNull();
		expect(definitionLoader.getCached("customer")).not.toBeNull();
		done();

	});
});

/**
 * Test loading definitions Non Asynchronously and make sure it gets cached for future requests
 */
describe("Get Definition Non Async", function() {

	beforeEach(function() {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base/";
	});
	
	it("Can fallback to loading definition synchronously", function() {
		
		// Clear cache which forces the loader to get the definition through BackendRequest
		definitionLoader._definitions = new Array();
		var definition = definitionLoader.get("customer"); // No callback forces sync load
		expect(definition).not.toBeNull();
	});

	it("Should have loaded the right data", function() {
		
		// Clear cache which forces the loader to get the definition through BackendRequest
		definitionLoader._definitions = new Array();
		var definition = definitionLoader.get("customer"); // No callback forces sync load
		expect(definition.objType).toEqual("customer");
		expect(definition.title).toEqual("Customer");
	});

	it("Should have cached the definition object", function() {
		
		// Check the private _definitions property of the loader
		definitionLoader._definitions = new Array();
		var definition = definitionLoader.get("customer"); // No callback forces sync load
		expect(definitionLoader._definitions["customer"]).not.toBeNull();

	});
});

/**
 * Test loading all definitions asynchronously and make sure it gets cached for future requests
 */
describe("Get All Definitions Asynchronously", function () {
	var allDefinitions = null;

	beforeEach(function (done) {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base/";
		definitionLoader.getAll(function (result) {
			allDefinitions = result;
			done();
		});
	});

	it("Should have loaded the objects object", function (done) {
		expect(allDefinitions).not.toBeNull();
		done();
	});

	it("Should have cached the object", function (done) {
		// Check the private _AllDefinitions property of the loader
		expect(definitionLoader._flagAllDefinitionsLoaded).toBe(true);
		expect(definitionLoader._definitions).not.toBeNull();
		done();

	});

	it("Should have the objects specified in /svr/entity/getDefinitions", function (done) {
		expect(definitionLoader._definitions).not.toBeNull();
		expect(definitionLoader._definitions["customer"]).not.toBeNull();
		expect(definitionLoader._definitions["projects"]).not.toBeNull();
		expect(definitionLoader._definitions["task"]).not.toBeNull();
		done();

	});
});


/**
 * Test loading of objects Non Asynchronously and make sure it gets cached for future requests
 */
describe("Get All Definitions Non Async", function() {

	beforeEach(function() {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base/";
	});

	it("Can fallback to loading objects synchronously", function() {
		// Clear cache which forces the loader to get the objects through BackendRequest
		definitionLoader._definitions = new Array();
		definitionLoader._flagAllDefinitionsLoaded = false;
		var allDefinitions = definitionLoader.getAll(); // No callback forces sync load
		expect(allDefinitions).not.toBeNull();
	});

	it("Should have cached the objects", function() {

		// Check the private _objects property of the loader
		definitionLoader._definitions = new Array();
		definitionLoader._flagAllDefinitionsLoaded = false;
		var allDefinitions = definitionLoader.getAll(); // No callback forces sync load
		expect(definitionLoader._definitions).not.toBeNull();
		expect(definitionLoader._definitions["customer"]).not.toBeNull();
		expect(definitionLoader._definitions["projects"]).not.toBeNull();
		expect(definitionLoader._definitions["task"]).not.toBeNull();
	});
});