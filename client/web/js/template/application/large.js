/** 
 * @fileoverview View templates for the application in full desktop mode
 */
 alib.declare("netric.template.application.large");

 /**
  * Make sure tha namespace exists for template
  */

 /**
 * Make sure module namespace is initialized
 */
netric.template = netric.template || {};
netric.template.application = netric.template.application || {};

/**
 * Large and medium templates will use this same template
 *
 * @param {Object} data Used for rendering the template
 * @return {string|netric.mvc.ViewTemplate} Either returns a string or a ViewTemplate object
 */
netric.template.application.large = function(data) {
	
	/*
	<!-- application header -->
	<div id='appheader' class='header'>
		<!-- right actions -->
		<div id='headerActions'>
			<table border='0' cellpadding="0" cellspacing="0">
			<tr valign="middle">			
				<!-- notifications -->
				<td style='padding-right:10px'><div id='divAntNotifications'></div></td>

				<!-- chat -->
				<td style='padding-right:10px'><div id='divAntChat'></div></td>

				<!-- new object dropdown -->
				<td style='padding-right:10px'><div id='divNewObject'></div></td>

				<!-- settings -->
				<td style='padding-right:10px'>
					<a href="javascript:void(0);" class="headerLink" 
						onclick="document.location.hash = 'settings';" 
						title='Click to view system settings'>
							<img src='/images/icons/main_settings_24.png' />
					</a>
				</td>

				<!-- help -->
				<td style='padding-right:10px' id='mainHelpLink'>
					<a href='javascript:void(0);' title='Click to get help'><img src='/images/icons/help_24_gs.png' /></a>
				 </td>
				<td id='mainProfileLink'>
					<a href='javascript:void(0);' title='Logged in as <?php echo $USER->fullName; ?>'><img src="/files/userimages/current/0/24" style='height:24px;' /></a>
				</td>
			</tr>
			</table>
		</div>

		<!-- logo -->
		<div class='headerLogo'>
		<?php
			$header_image = $ANT->settingsGet("general/header_image");
			if ($header_image)
			{
				echo "<img src='/antfs/images/$header_image' />";
			}
			else
			{
				echo "<img src='/images/netric-logo-32.png' />";

			}
		?>
		</div> 
		<!-- end: logo -->
		
		<!-- middle search -->
		<div id='headerSearch'><div id='divAntSearch'></div></div>

		<div style="clear:both;"></div>
	</div>
	<!-- end: application header -->

	<!-- application tabs -->
	<div id='appnav'>
		<div class='topNavbarHr'></div>
		<div class='topNavbarBG' id='apptabs'></div>
		<div class='topNavbarShadow'></div>
	</div>
	<!-- end: application tabs -->

	<!-- application body - where the applications load -->
	<div id='appbody'>
	</div>
	<!-- end: application body -->

	<!-- welcome dialog -->
	<div id='tour-welcome' style='display:none;'>
		<div data-tour='apps/netric' data-tour-type='dialog'></div>
	</div>
	<!-- end: welcome dialog -->
	*/

	var vt = new netric.mvc.ViewTemplate();

	// Add header
	// ------------------------------------------
	vt.header = alib.dom.createElement("div", null, null, {id:"app-header"});
	alib.dom.styleAddClass(vt.header, "app-header app-header-large");
	vt.addElement(vt.header);

	// Add logo
	var headerLogo = alib.dom.createElement("div", vt.header);
	alib.dom.styleAddClass(headerLogo, "app-header-logo-con");
	headerLogo.innerHTML = "<i class=\"fa fa-bars fa-lg\"></i> <img src=\"" + data.logoSrc + "\" id='app-header-logo' />";

	// Add search box
	vt.headerSearchCon = alib.dom.createElement("div", vt.header, null, {id:"app-header-search"});
	alib.dom.styleAddClass(vt.headerSearchCon, "app-header-search-con");
	vt.headerSearchCon.innerHTML = "Search goes here";

	// Add profile area
	vt.headerProfileCon = alib.dom.createElement("div", vt.header, null, {id:"app-header-search"});
	alib.dom.styleAddClass(vt.headerProfileCon, "app-header-profile-con");
	vt.headerProfileCon.innerHTML = "<i class=\"fa fa-camera-retro fa-lg\"></i>";

	// Add module body
	// ------------------------------------------
	vt.bodyCon = alib.dom.createElement("div", null, null, {id:"app-body"});
	alib.dom.styleAddClass(vt.bodyCon, "app-body");
	alib.dom.styleAddClass(vt.bodyCon, "app-body-large");
	vt.bodyCon.innerHTML = "Put the app body here!";
	vt.addElement(vt.bodyCon);

	return vt;
}
