'use strict';

var accountLoader = require("../../../src/account/loader");
var moduleLoader = require("../../../src/module/loader");
var netric = require("../../../src/main");

/**
 * Test loading the account asynchronously and make sure it gets cached for future requests
 */
describe("Get Account Asynchronously", function() {
	var account = null;

	beforeEach(function(done) {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base/";
		accountLoader.get(function(acct){
			account = acct;
			done(); 
		});
	});
	
	it("Should have loaded the account object", function(done) {
		
		expect(account).not.toBeNull();
		expect(account.id).toEqual(1);
		done();

	});

	it("Should have loaded the right data", function(done) {
		
		expect(account.id).toEqual(1);
		expect(account.name).toEqual("test");
		expect(account.orgName).toEqual("Test Company LLC");
		done();

	});

	it("Should have a user object", function(done) {
		
		expect(account.user).not.toBeNull();
		expect(account.user.id).toEqual(1002);
		expect(account.user.name).toEqual("test.user");
		expect(account.user.fullName).toEqual("Test User");
		done();

	});

	it("Should have cached the account object", function(done) {
		
		// Check the private accountCache_ property of the loader
		expect(accountLoader.accountCache_).not.toBeNull();
		done();

	});
	
	it("Should preload modules", function(done) {
		// accountLoader will call moduleLoader.preloadFormData
		expect(moduleLoader.loadedModules_["messages"]).not.toBeNull();
		expect(moduleLoader.loadedModules_["home"]).not.toBeNull();
		done();
	});

});

/**
 * Check to make sure expected public varibles are set
 */
describe("Get Account Non Async", function() {

	beforeEach(function() {
		// Set test base where karma unit tests are hosted
		netric.server.host = "base/";
	});
	
	it("Can can fallback to loading account synchronously", function() {
		
		// Clear cache which forces the loader to get the account through BackendRequest
		accountLoader.accountCache_ = null;
		var account = accountLoader.get(); // No callback forces sync load
		expect(account).not.toBeNull();
	});

	it("Should have loaded the right data", function() {
		
		// Clear cache which forces the loader to get the account through BackendRequest
		accountLoader.accountCache_ = null;
		var account = accountLoader.get(); // No callback forces sync load
		expect(account.id).toEqual(1);
	});

	it("Should have cached the account object", function() {
		
		// Check the private accountCache_ property of the loader
		accountLoader.accountCache_ = null;
		var account = accountLoader.get(); // No callback forces sync load
		expect(accountLoader.accountCache_).not.toBeNull();

	});

});