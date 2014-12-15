'use strict';

/**
 * Check to make sure expected public varibles are set
 */
describe("Public Variables:", function() {
  it("Has a local version variable", function() {
    expect(netric.version.length).toBeGreaterThan(0);
  });
});

/**
 * Test netric namespaced public functions
 */
describe("Public Base Functions:", function() {
	/*
	 * Base URI is used to dynamically construct links
	 */
	it("Can get the local base URI", function() {
		var baseUri = netric.getBaseUri();
		expect(baseUri.length).toBeGreaterThan(0);
	});

	/**
	 * Check to make sure that getApplication calls made
	 * without first setting the application will result in
	 * an error exception.
	 */
	it ("Should throw an exception if getApplication is called early", function() {
		var error = "An instance of netric.Application has not yet been loaded.";
		expect( function(){ netric.getApplication(); } ).toThrow(new Error(error));
	});
});