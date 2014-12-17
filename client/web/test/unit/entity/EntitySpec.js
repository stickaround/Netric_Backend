'use strict';

/**
 * Test loading definitions asynchronously and make sure it gets cached for future requests
 */
describe("Get and Set Entity Values", function() {
	var entity = null;

	// Setup test entity
	beforeEach(function() {
		var definition = new netric.entity.Definition({
			obj_type: "test",
			title: "Test Object",
			id: "1",
			fields: [
				{
					"id" : 2,
					"name" : "id",
					"title" : "Id",
					"type" : "number",
					"subtype" : "integer",
					"default_val" : {
						"on" : "null",
						"value" : "3"
					},
					"mask" : "xxx,xxx",
					"required" : true,
					"system" : true,
					"readonly" : true,
					"unique" : true,
					"use_when" : "",
					"optional_values" : ""
				},
				{
					"id" : 3,
					"name" : "name",
					"title" : "Name",
					"type" : "string",
					"subtype" : "",
					"default_val" : null,
				},
				{
					"id" : 5,
					"name" : "status",
					"title" : "Status",
					"type" : "fkey",
					"subtype" : "",
					"default_val" : null,
				},
				{
					"id" : 6,
					"name" : "categories",
					"title" : "Cateogires",
					"type" : "fkey_multi",
					"subtype" : "",
					"default_val" : null,
				},
				{
					"id" : 6,
					"name" : "person",
					"title" : "Person",
					"type" : "object",
					"subtype" : "customer",
					"default_val" : null,
				},
				{
					"id" : 7,
					"name" : "people",
					"title" : "People",
					"type" : "object_multi",
					"subtype" : "customer",
					"default_val" : null,
				},
				{
					"id" : 8,
					"name" : "reference",
					"title" : "Reference",
					"type" : "object",
					"subtype" : "",
					"default_val" : null,
				}
			]
		});
		entity = new netric.entity.Entity(definition);
	});

	it("should be able to get and set string values", function() {
		var testStr = "My Test Value";
		entity.setValue("name", testStr);
		expect(entity.getValue("name")).toEqual(testStr);
	});

	it("should be able to get and set fkey values with valueNames", function() {
		var testStr = "My Test Value";
		entity.setValue("name", testStr);
		expect(entity.getValue("name")).toEqual(testStr);
	});
});