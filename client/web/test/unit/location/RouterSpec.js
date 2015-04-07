'use strict';

var Router = require("../../../js/location/Router");
var TestController = require("../../../js/controller/TestController");

describe("Router", function() {

	describe("Adding Routes to Router", function() {

		it("Can add a segment", function() {
			var router = new Router();
			router.addRoute("test", TestController);
			var route = router.getRoute("test");
			expect(route.getName()).toEqual("test");
		});

		it ("Can get the router for next hops", function() {
			var router = new Router();
			var route = router.addRoute("test", TestController);
			var nextRouter = route.getChildRouter();
			expect(nextRouter instanceof Router).toEqual(true);
		});

		it ("Sets the parent router of the child route::router", function() {
			var router = new Router();
			var route = router.addRoute("test", TestController);
			var nextRouter = route.getChildRouter();
			expect(nextRouter.getParentRouter()).toEqual(router);
		});

	});

	/**
	 * Test all 'go' type scenarios
	 */
	describe("When going to routes", function() {

		it("Should be able to load the special root path", function(){
			var router = new Router();
			router.addRoute("/", TestController);
			router.go("/");
			expect(router.getActivePath()).toEqual("/");
		});

		it("Should make the next hop to a child route", function(){
			var router = new Router();
			var route = router.addRoute("/", TestController);
			route.getChildRouter().addRoute("testsub", TestController);
			router.go("/testsub");
			expect(route.getChildRouter().getActivePath()).toEqual("/testsub");
		});

		it("Should instantiate the controller class", function(){
			var router = new Router();
			router.addRoute("/", TestController);
			router.go("/");
			expect(router.getActiveRoute().getController() instanceof TestController).toEqual(true);
		});

		it("subroutes should be exited when navigated back to parent", function(){
			var router = new Router();
			var route = router.addRoute("/", TestController);
			route.getChildRouter().addRoute("testsub", TestController);
			// To to the subroute
			router.go("/testsub");
			// Now go back to the root
			router.go("/");
			// Make sure the subroute is now exited
			expect(route.getChildRouter().getActiveRoute()).toBe(null);
		});

	});

});