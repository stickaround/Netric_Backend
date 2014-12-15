/** 
 * @fileoverview View templates for the application in full desktop mode
 */
 alib.declare("netric.template.application.small");

 /**
  * Make sure tha namespace exists for template
  */

 /**
 * Make sure module namespace is initialized
 */
netric.template = netric.template || {};
netric.template.appication = netric.template.application || {};


/**
 * Large and medium views will use this same template
 *
 * @param {Object} data Used for rendering the template
 */
netric.template.application.small = function(data) {
	return "<div id='main'><div id='loading'>Loading...</div></div><div id='footerTabs'></div>";
}