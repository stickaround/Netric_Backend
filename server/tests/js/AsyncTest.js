/**
 * @fileoverview This file demonstrates asynchronous testing
 *
 * More information can be found here:
 * http://code.google.com/p/js-test-driver/wiki/AsyncTestCase
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 */
var AsynchronousTest = AsyncTestCase('AsynchronousTest');

AsynchronousTest.prototype.testSomethingComplicated = function(queue) 
{
	var state = 0;

	// Increment the variable async using a callback and a timeout
	queue.call('Step 1: schedule the window to increment our variable 1 second from now.', function(callbacks) {
		var myCallback = callbacks.add(function() { ++state; });
		window.setTimeout(myCallback, 1000);
	});

	queue.call('Step 2: then assert our state variable changed', function() {
		assertEquals(1, state);
	});
};
