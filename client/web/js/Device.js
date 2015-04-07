/**
 * @fileoverview Device information class
 * 
 * @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
 * 			Copyright (c) 2014 Aereus Corporation. All rights reserved.
 */
'use strict';

// Information about the current device
var Device = function() {
	// Try to determine the current devices size
	this.getDeviceSize_();

	/**
	 * Attempt to determine our platform
	 *
	 * @type {Device.platforms}
	 */
	this.platform = this.getDevicePlatform_();
}

/**
 * Static device sizes
 * 
 * @const
 * @public
 */
Device.sizes = {
	// Phones and small devices
	small : 1,
	// Tablets
	medium : 3,
	// Desktops
	large : 5
};

/**
 * Static device platforms
 * 
 * @const
 * @public
 */
Device.platforms = {
	// Google Android
	android : 1,
	// Apple iOS
	ios : 3,
	// Microsoft Windows
	windows : 5
};

/**
 * The size of the current device once loaded
 *
 * @type {Device.sizes}
 */
Device.prototype.size = Device.sizes.large;

/**
 * Detect the size of the current device and set this.size
 *
 * @private
 */
 Device.prototype.getDeviceSize_ = function() {
 	
 	var width = alib.dom.getClientWidth();
 	
 	if (width <= 768) {
 		this.size = Device.sizes.small;
 	} else if (width > 768 && width < 1200) {
 		this.size = Device.sizes.medium;
 	} else if (width >= 1200) {
 		this.size = Device.sizes.large;
 	}
 }

 /**
  * Detect the size of the current device and set this.size
  *
  * @private
  */
 Device.prototype.getDevicePlatform_ = function() {
 	// TODO: Detect platform here
 }

 module.exports = Device;
 