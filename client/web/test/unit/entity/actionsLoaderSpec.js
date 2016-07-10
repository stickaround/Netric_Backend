'use strict';

var actionsLoader = require("../../../src/entity/actionsLoader");

describe("Get Default Actions", function() {
	it("Should have loaded the actions object", function(done) {
		var actions = actionsLoader.get("note");
		expect(actions).not.toBeNull();
		done();
	});
});

describe("Get Actions Asynchronously", function() {
	var actions = null;

	beforeEach(function(done) {
		actionsLoader.get("customer", function(act){
			actions = act;
			done(); 
		});
	});
	
	it("Should have loaded the actions object", function(done) {
		expect(actions).not.toBeNull();
		done();
	});
});

