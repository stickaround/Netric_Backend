'use strict';

describe("netric.location.Router", function() {

	describe("Adding Routes to Router", function() {

		it("Can add a segment", function() {
			var router = new netric.location.Router();
			router.addRoute("test", netric.controller.TestController);
			var route = router.getRoute("test");
			expect(route.getName()).toEqual("test");
		});

		it ("Can get the router for next hops", function() {
			var router = new netric.location.Router();
			var route = router.addRoute("test", netric.controller.TestController);
			var nextRouter = route.getChildRouter();
			expect(nextRouter instanceof netric.location.Router).toEqual(true);
		});

		it ("Sets the parent router of the child route::router", function() {
			var router = new netric.location.Router();
			var route = router.addRoute("test", netric.controller.TestController);
			var nextRouter = route.getChildRouter();
			expect(nextRouter.getParentRouter()).toEqual(router);
		});

	});

	/**
	 * Test all 'go' type scenarios
	 */
	describe("When going to routes", function() {

		it("Should be able to load the special root path", function(){
			var router = new netric.location.Router();
			router.addRoute("/", netric.controller.TestController);
			router.go("/");
			expect(router.getActivePath()).toEqual("/");
		});

		it("Should make the next hop to a child route", function(){
			var router = new netric.location.Router();
			var route = router.addRoute("/", netric.controller.TestController);
			route.getChildRouter().addRoute("testsub", netric.controller.TestController);
			router.go("/testsub");
			expect(route.getChildRouter().getActivePath()).toEqual("/testsub");
		});

		it("Should instantiate the controller class", function(){
			var router = new netric.location.Router();
			router.addRoute("/", netric.controller.TestController);
			router.go("/");
			expect(router.getActiveRoute().getController() instanceof netric.controller.TestController).toEqual(true);
		});

		it("subroutes should be exited when navigated back to parent", function(){
			var router = new netric.location.Router();
			var route = router.addRoute("/", netric.controller.TestController);
			route.getChildRouter().addRoute("testsub", netric.controller.TestController);
			// To to the subroute
			router.go("/testsub");
			// Now go back to the root
			router.go("/");
			// Make sure the subroute is now exited
			expect(route.getChildRouter().getActiveRoute()).toBe(null);
		});

	});

});