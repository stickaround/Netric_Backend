'use strict';

var localData = require("../../src/localData");

describe("Set db item string", function() {

	var value, error;

	beforeEach(function(done) {
		localData.dbSetItem("test/val", "string_value", function(err, val){
			error = err;
			value = val;
			done(); 
		});
	});
	

	/**
	 * Check if send and receive works
	 */
	it("set a string value", function(done) {
		expect(value).toEqual("string_value");
		done();
	});

	describe("Get db item string", function() {

		var value, error;

		beforeEach(function(done) {
			localData.dbGetItem("test/val", function(err, val){
				error = err;
				value = val;
				done(); 
			});
		});
		

		it("set a string value", function(done) {
			expect(value).toEqual("string_value");
			done();
		});

	});

});

describe("Set db item object", function() {

	var value, error;

	beforeEach(function(done) {
		localData.dbSetItem("test/val", { "mycustprop": 1 }, function(err, val){
			error = err;
			value = val;
			done(); 
		});
	});
	

	/**
	 * Check if send and receive works
	 */
	it("can get a property", function(done) {
		expect(value.mycustprop).toEqual(1);
		done();
	});

	describe("Get db item string", function() {

		var value, error;

		beforeEach(function(done) {
			localData.dbGetItem("test/val", function(err, val){
				error = err;
				value = val;
				done(); 
			});
		});
		

		it("set a string value", function(done) {
			expect(value.mycustprop).toEqual(1);
			done();
		});

	});

});

