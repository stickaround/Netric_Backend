/**
* @fileOverview Load instance of netric application
*
* @author:	Sky Stebnicki, sky.stebnicki@aereus.com; 
* 			Copyright (c) 2011 Aereus Corporation. All rights reserved.
*/
alib.declare("netric.mvc.ViewTemplate");

alib.require("netric.mvc");

/**
 * Application instance
 *
 * @param {netric.Account} account The current account netric is running under
 */
netric.mvc.ViewTemplate = function(opt_html) {
	/**
	 * Raw HTML for this template
	 * 
	 * 
	 * @private
	 * @type {string}
	 */
	this.html_ = opt_html || "";

	/**
	 * Dom elements for template
	 * 
	 * @type {DOMElement[]}
	 */
	this.domElements_ = null;

	/**
	 * Associateve array of dom elements that can be referenced later by name
	 *
	 * @private
	 * @type {Array()}
	 */
	this.domExports_ = null;
}

/**
 * Get the dom element for this template
 * 
 * @public
 * @return {DOMElement}
 */
netric.mvc.ViewTemplate.prototype.getDom = function() {
	if (this.domElements_ != null) {
		return this.domElements_;
	}

	// TODO: render this.html_ to dom and return
}

/**
 * Add a dom element to the template
 * 
 * @param {DOMElement} domEl The element to add to this template
 * @param {string} opt_exportName Optional name to export for reference by this.<name>
 */
netric.mvc.ViewTemplate.prototype.addElement = function(domEl, opt_exportName) {

	if (this.domElements_ == null) {
		this.domElements_ = new Array();
	}

	// Add the element to the template
	this.domElements_.push(domEl);

	if (opt_exportName) {
		this[opt_exportName] = domEl;
	}
}

/**
 * Render the template into a dom element
 * 
 * @param {DOMElement}
 */
netric.mvc.ViewTemplate.prototype.render = function(domCon) {
	if (this.domElements_ != null) {
		for (var i in this.domElements_) {
			domCon.appendChild(this.domElements_[i]);
		}

	} else {
		domCon.innerHTML = this.html_;
	}
}