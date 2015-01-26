'use strict';

describe("netric.location.Route", function() {

	var router = new netric.location.Router();

	describe("when counting segments it", function() {
		it("can count one segment", function() {
			var route = new netric.location.Route(router, "mypath", netric.controller.TestController);
			expect(route.getNumPathSegments()).toEqual(1);
		});

		it("can count one root segment", function() {
			var route = new netric.location.Route(router, "/", netric.controller.TestController);
			expect(route.getNumPathSegments()).toEqual(1);
		});

		it("can count multipe dynamic segments", function() {
			var route = new netric.location.Route(router, "files/:id/:name", netric.controller.TestController);
			expect(route.getNumPathSegments()).toEqual(3);
		});

		it("can count multipe dynamic segments with absolute", function() {
			var route = new netric.location.Route(router, "/files/:id/:name", netric.controller.TestController);
			expect(route.getNumPathSegments()).toEqual(4);
		});
	});

	describe("matchesPath", function() {
		it("can match a single static segment", function() {
			var route = new netric.location.Route(router, "mypath", netric.controller.TestController);
			expect(route.matchesPath("mypath")).toEqual({path: "mypath", params:{}, nextHopPath:""});
		});

		it("returns null when no simple match is found", function() {
			var route = new netric.location.Route(router, "mypath", netric.controller.TestController);
			expect(route.matchesPath("nomatch")).toBe(null);
		});

		it("returns null if path does not match", function() {
			var route = new netric.location.Route(router, "two/segs", netric.controller.TestController);
			expect(route.matchesPath("other/segs2")).toBe(null);
		});

		it("returns params when route matches", function() {
			var route = new netric.location.Route(router, "obj/:objType/:oid", netric.controller.TestController);
			expect(route.matchesPath("obj/customer/1001")).toEqual({path:"obj/customer/1001", params:{objType:"customer", oid:"1001"}, nextHopPath:""});
		});

		it("returns params when route matches with nexthop", function() {
			var route = new netric.location.Route(router, "obj/:objType/:oid", netric.controller.TestController);
			expect(route.matchesPath("obj/customer/1001/edit")).toEqual({path:"obj/customer/1001",params:{objType:"customer", oid:"1001"}, nextHopPath:"edit"});
		});
	});

	describe("getPathSegments", function() {
		it("fails gracefully if tested with a path too short", function() {
			var route = new netric.location.Route(router, "my/deep/path", netric.controller.TestController);
			expect(route.getPathSegments("my/path", 3)).toBe(null);
		});

		it("will get target path segments from full path match", function() {
			var route = new netric.location.Route(router, "my/path", netric.controller.TestController);
			expect(route.getPathSegments("my/path", 2)).toEqual({target:"my/path", remainder:""});
		});

		it("will set remainder for a partial path", function() {
			var route = new netric.location.Route(router, "my/path", netric.controller.TestController);
			expect(route.getPathSegments("my/path/nexthop", 2)).toEqual({target:"my/path", remainder:"nexthop"});
		});

		it("will work with trailing slash", function() {
			var route = new netric.location.Route(router, "my/path", netric.controller.TestController);
			expect(route.getPathSegments("my/path/nexthop/", 2)).toEqual({target:"my/path", remainder:"nexthop/"});
		});
	});
	
});
