'use strict';

var Router = require("../../../js/location/Router");
var Route = require("../../../js/location/Route");
var TestController = require("../../../js/controller/TestController");

describe("Route", function() {

	var router = new Router();

	describe("when counting segments it", function() {
		it("can count one segment", function() {
			var route = new Route(router, "mypath", TestController);
			expect(route.getNumPathSegments()).toEqual(1);
		});

		it("can count one root segment", function() {
			var route = new Route(router, "/", TestController);
			expect(route.getNumPathSegments()).toEqual(1);
		});

		it("can count multipe dynamic segments", function() {
			var route = new Route(router, "files/:id/:name", TestController);
			expect(route.getNumPathSegments()).toEqual(3);
		});

		it("can count multipe dynamic segments with absolute", function() {
			var route = new Route(router, "/files/:id/:name", TestController);
			expect(route.getNumPathSegments()).toEqual(4);
		});
	});

	describe("matchesPath", function() {
		it("can match a single static segment", function() {
			var route = new Route(router, "mypath", TestController);
			expect(route.matchesPath("mypath")).toEqual({path: "mypath", params:{}, nextHopPath:""});
		});

		it("returns null when no simple match is found", function() {
			var route = new Route(router, "mypath", TestController);
			expect(route.matchesPath("nomatch")).toBe(null);
		});

		it("returns null if path does not match", function() {
			var route = new Route(router, "two/segs", TestController);
			expect(route.matchesPath("other/segs2")).toBe(null);
		});

		it("returns params when route matches", function() {
			var route = new Route(router, "obj/:objType/:oid", TestController);
			expect(route.matchesPath("obj/customer/1001")).toEqual({path:"obj/customer/1001", params:{objType:"customer", oid:"1001"}, nextHopPath:""});
		});

		it("returns params when route matches with nexthop", function() {
			var route = new Route(router, "obj/:objType/:oid", TestController);
			expect(route.matchesPath("obj/customer/1001/edit")).toEqual({path:"obj/customer/1001",params:{objType:"customer", oid:"1001"}, nextHopPath:"edit"});
		});

		it("returns params when route matches on creating new entity", function() {
			var route = new Route(router, "obj/new/:objType", TestController);
			expect(route.matchesPath("obj/new/customer")).toEqual({path:"obj/new/customer", params:{objType:"customer"}, nextHopPath:""});
		});

		it("returns params when route matches on creating new entity with a query string", function() {
			var route = new Route(router, "obj/new/:objType", TestController);
			expect(route.matchesPath("obj/new/customer?ref_id=1")).toEqual(
                {
				    path: "obj/new/customer?ref_id=1",
				    params: {objType:"customer", ref_id:"1"},
				    nextHopPath: ""
                }
            );
		});

		it("returns params when route matches on creating new entity with multiple query string", function() {
			var route = new Route(router, "obj/new/:objType", TestController);
			expect(route.matchesPath("obj/new/customer?ref_id=1&ref_value=test")).toEqual(
                {
                    path: "obj/new/customer?ref_id=1&ref_value=test",
                    params: {objType:"customer", ref_id:"1", ref_value:"test"},
                    nextHopPath: ""
                }
            );
		});
	});

	describe("getPathSegments", function() {
		it("fails gracefully if tested with a path too short", function() {
			var route = new Route(router, "my/deep/path", TestController);
			expect(route.getPathSegments("my/path", 3)).toBe(null);
		});

		it("will get target path segments from full path match", function() {
			var route = new Route(router, "my/path", TestController);
			expect(route.getPathSegments("my/path", 2)).toEqual({target:"my/path", remainder:""});
		});

		it("will set remainder for a partial path", function() {
			var route = new Route(router, "my/path", TestController);
			expect(route.getPathSegments("my/path/nexthop", 2)).toEqual({target:"my/path", remainder:"nexthop"});
		});

		it("will work with trailing slash", function() {
			var route = new Route(router, "my/path", TestController);
			expect(route.getPathSegments("my/path/nexthop/", 2)).toEqual({target:"my/path", remainder:"nexthop/"});
		});
	});

});
