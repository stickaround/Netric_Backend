'use strict';

var Definition = require("../../../js/entity/Definition");
var Entity = require("../../../js/entity/Entity");
var Account = require("../../../js/account/Account");
var Application = require("../../../js/Application");
var netric = require("../../../js/main");

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

/**
 * Test setting of default values
 */
describe("Set default values for new entities", function() {
	var entity = null;

	var defaultUser = "Current User";
	var defaultUserId = -3;

	var creator = "testCreator";
	var creatorId = 1;

	var accountData = {
		id: defaultUserId,
		name: defaultUser,
	}

	// Setup application user account
	var account = new Account(accountData);
	var application = new Application();
	application.setAccount(account);
	netric.setApplication(application);

	// Setup test entity
	beforeEach(function() {
		var definition = new Definition({
			obj_type: "test",
			title: "Test Object",
			id: "", // Id is blank to specify that we are creating a new entity
			fields: [
				{
					"id" : 1,
					"name" : "owner_id",
					"title" : "Owner",
					"type" : "object",
					"subtype" : "user",
					"default_val" : null,
				},
				{
					"id" : 2,
					"name" : "creator_id",
					"title" : "Creator",
					"type" : "object",
					"subtype" : "user",
					"default_val" : null,
				},
				{
					"id" : 3,
					"name" : "project_id",
					"title" : "Project",
					"type" : "object",
					"subtype" : "project",
					"default_val" : null,
				}
			]
		});
		entity = new Entity(definition);
	});

	it("should be able to set default values for user subtypes", function() {

		// Save value for creator so we can check later if the creator value is overwritten when setting default values
		entity.setValue("creator_id", creatorId, creator);

		// Set default values for the entity
		entity.setDefaultValues(null);

		// owner_id should be set as default user
		expect(entity.getValue("owner_id")).toEqual(defaultUserId);
		expect(entity.getValueName("owner_id")).toEqual(defaultUser);
		expect(entity.getValueName("owner_id", defaultUserId)).toEqual(defaultUser);

		// Should not be over written
		expect(entity.getValue("creator_id")).toEqual(creatorId);
		expect(entity.getValueName("creator_id")).toEqual(creator);
		expect(entity.getValueName("creator_id", creatorId)).toEqual(creator);
	});

	it("should be able to set default values from source", function() {

		var project = "Test Project";
		var projectId = 1;

		var sourceProps = {
			project_id: projectId,
			project_id_val: project,
		}

		// Save value for creator so we can check later if the creator value is overwritten when setting default values
		entity.setValue("creator_id", creatorId, creator);

		// Set default values for the entity from source
		entity.setDefaultValues(sourceProps);

		// project_id should be updated
		expect(entity.getValue("project_id")).toEqual(projectId);
		expect(entity.getValueName("project_id")).toEqual(project);
		expect(entity.getValueName("project_id", projectId)).toEqual(project);

		// owner_id will be set as default user
		expect(entity.getValue("owner_id")).toEqual(defaultUserId);
		expect(entity.getValueName("owner_id")).toEqual(defaultUser);
		expect(entity.getValueName("owner_id", defaultUserId)).toEqual(defaultUser);

		// Should not be over written
		expect(entity.getValue("creator_id")).toEqual(creatorId);
		expect(entity.getValueName("creator_id")).toEqual(creator);
		expect(entity.getValueName("creator_id", creatorId)).toEqual(creator);
	});

	it("should NOT be able to set default values since entity will now have an id", function() {

		// Set an id for the entity
		entity.setValue("id", 1);

		// Try to set default values for the entity (but it should NOT update any fields since we have an entity id)
		entity.setDefaultValues();

		// project_id should NOT be updated
		expect(entity.getValue("project_id")).toBe(null);
		expect(entity.getValueName("project_id")).toBe('');
	});
});
