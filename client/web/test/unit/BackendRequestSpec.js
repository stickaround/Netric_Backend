'use strict';

var netric = require("../../js/main.js");
var BackendRequest = require("../../js/BackendRequest");

/**
 * Check to make sure expected public varibles are set
 */
describe("Send Requests", function() {
	var request = new BackendRequest();

	beforeEach(function(done) {

		alib.events.listen(request, "load", function(evt) { 	
			done(); 
		});

		netric.server.host = "base/";
		request.send("svr/test.json");
	});
	

	/**
	 * Check if send and receive works
	 */
	it("Can send and receive data", function(done) {
		
		var data = request.getResponse();
		expect(data.test).toEqual("test");
		done();

	});

});

/**
 * Check to make sure expected public varibles are set
 */
describe("Static Send Requests", function() {
	var request = null;

	beforeEach(function(done) {
		netric.server.host = "base/";
		request = BackendRequest.send("svr/test.json", function(evt){
			done(); 
		});
	});
	
	/**
	 * Check if send and receive works
	 */
	it("Can send and receive data with static closure", function(done) {
		
		var data = request.getResponse();
		expect(data.test).toEqual("test");
		done();

	});

});