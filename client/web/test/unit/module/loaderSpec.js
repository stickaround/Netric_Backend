'use strict';

var netric = require("../../../src/main");
var moduleLoader = require("../../../src/module/loader");

/**
 * Test loading the account asynchronously and make sure it gets cached for future requests
 */
describe("Get Module Asynchronously", function() {
	var module = null;

	beforeEach(function(done) {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base/";
		moduleLoader.get("messages", function(mdl){
			module = mdl;
			done(); 
		});
	});
	
	it("Should have loaded the module object", function(done) {
		
		expect(module).not.toBeNull();
		done();

	});

	it("Should have loaded the right data", function(done) {
		
		expect(module.name).toEqual("messages");
		expect(module.title).toEqual("Messages");
		done();

	});

	it("Should have cached the module object", function(done) {
		
		// Check the private loadedModules_ property of the loader
		expect(moduleLoader.loadedModules_["messages"]).not.toBeNull();
		done();

	});

});

/**
 * Check to make sure expected public varibles are set
 */
describe("Get Module Non Async", function() {

	beforeEach(function() {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base/";
	});
	
	it("Can can fallback to loading module synchronously", function() {
		
		// Clear cache which forces the loader to get the account through BackendRequest
		moduleLoader.accountCache_ = null;
		var module = moduleLoader.get("messages"); // No callback forces sync load
		expect(module).not.toBeNull();
	});

	it("Should have loaded the right data", function() {
		
		// Clear cache which forces the loader to get the account through BackendRequest
		moduleLoader.accountCache_ = null;
		var module = moduleLoader.get("messages"); // No callback forces sync load
		expect(module.name).toEqual("messages");
		expect(module.title).toEqual("Messages");
	});

	it("Should have cached the account object", function() {
		
		// Check the private loadedModules_ property of the loader
		moduleLoader.loadedModules_ = new Array();
		var module = moduleLoader.get("messages"); // No callback forces sync load
		expect(moduleLoader.loadedModules_["messages"]).not.toBeNull();

	});

});

describe("Test preloading module data", function() {

	beforeEach(function() {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base/";
	});
	
	it("Should be able to preload modules", function() {
		
		// Clear cache which forces the loader to get the account through BackendRequest
		moduleLoader.accountCache_ = null;
		var data = [{name:"messages"}];
		moduleLoader.preloadFromData(data);
		expect(moduleLoader.loadedModules_["messages"]).not.toBeNull();
	});
});
