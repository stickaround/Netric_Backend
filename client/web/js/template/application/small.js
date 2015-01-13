/** 
 * @fileoverview View templates for the application in full desktop mode
 */
 alib.declare("netric.template.application.small");

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
	var vt = new netric.mvc.ViewTemplate();

	var header = alib.dom.createElement("div", null, null, {id:"app-header-small"});
	header.innerHTML = "Mobile Header";
	vt.addElement(header);
	vt.header = header; // Add for later reference

	vt.bodyCon = alib.dom.createElement("p");
	vt.bodyCon.innerHTML = "Put the app body here!";
	vt.addElement(vt.bodyCon);

	return vt;
}