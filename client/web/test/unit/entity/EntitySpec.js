'use strict';

var Definition = require("../../../js/entity/Definition");
var Entity = require("../../../js/entity/Entity");

/**
 * Test loading definitions asynchronously and make sure it gets cached for future requests
 */
describe("Get and Set Entity Values", function() {
	var entity = null;

	// Setup test entity
	beforeEach(function() {
		var definition = new Definition({
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
		entity = new Entity(definition);
	});

	it("should be able to get and set string values", function() {
		var testStr = "My Test Value";
		entity.setValue("name", testStr);
		expect(entity.getValue("name")).toEqual(testStr);
	});

	it("should be able to get and set fkey values with valueNames", function() {
		var statusName = "My Test Value";
		var statusId = 2; 
		entity.setValue("status", statusId, statusName);
		expect(entity.getValue("status")).toEqual(statusId);
		expect(entity.getValueName("status")).toEqual(statusName);
	});

	it("should be able to get and add fkey_multi values with valueNames", function() {
		var catName = "My Test Value";
		var catId = 2; 
		entity.addMultiValue("categories", catId, catName);
		expect(entity.getValue("categories")).toEqual([catId]);
		expect(entity.getValueName("categories")).toEqual([{key: catId, value:catName}]);
		expect(entity.getValueName("categories", catId)).toEqual(catName);
	});

	it("should be able to remove fkey_multi fields", function() {
		var catName = "My Third Test Value";
		var catId = 3; 
		entity.addMultiValue("categories", catId, catName);
		expect(entity.getValue("categories")).toContain(catId);
		expect(entity.remMultiValue("categories", catId)).toBeTruthy();
		expect(entity.getValue("categories")).not.toContain(catId);
	});

	it("should loadData for *_multi fields", function() {
		var catName = "My Test Value";
		var catId = 2; 

		var data = {
			id: 1,
			obj_type: "customer",
			"categories": [catId],
			"categories_fval": {"2":catName}
		};

		entity.loadData(data);
		expect(entity.getValue("categories")).toEqual([catId]);
		expect(entity.getValueName("categories")).toEqual([{key: catId, value:catName}]);
		expect(entity.getValueName("categories", catId)).toEqual(catName);
	});

	it("should loadData for string fields", function() {
		var data = {
			id: 1,
			obj_type: "customer",
			"name": "test"
		};

		entity.loadData(data);
		expect(entity.getValue("name")).toEqual(data.name);
	});

	it("should getData for string fields", function() {
		entity.setValue('name', 'test');
		var data = entity.getData();
		expect(data.name).toEqual('test');
	});

	it("should getData for *_multi fields", function() {
		var catName = "My Test Value";
		var catId = 2; 
		entity.addMultiValue("categories", catId, catName);

		var data = entity.getData();
		expect(data.categories).toEqual([catId]);
		var expRet = {};
		expRet[catId] = catName;
		expect(data.categories_fval).toEqual(expRet);
	});
});