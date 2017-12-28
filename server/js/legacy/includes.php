<?php
	if (!defined("APPLICATION_PATH"))
		require_once("../AntConfig.php");

	// Either make a random cache breaker, or use the parent /inc_jslibs.php version
	$verHash = (AntConfig::getInstance()->debug) ? rand() : $ver;

	$libs = array(
		"/js/legacy/Ant.js", 

		"/js/legacy/global.js",
		"/js/legacy/CReport.js", 
		"/js/legacy/Report.js", 
		"/customer/CCustActivity.js", 
		"/customer/CCustomerBrowser.js", 
		"/js/legacy/CWidgetBox.js", 
		"/js/legacy/CFlashObj.js", 
		"/js/legacy/CVideoPlayer.js", 
		"/users/CUserBrowser.js",
		"/objects/CActivity.js", 
		"/contacts/contact_functions.js", 
		"/project/CProjectStart.js",
		"/customer/customer_functions.js", 
		"/calendar/calendar_functions.js", 
		"/project/project_functions.js", 
		"/email/email_functions.js",  
		"/js/legacy/CRecurrencePattern.js", 

		// Base
		"/js/legacy/AntUpdateStream.js", 
		"/js/legacy/NewObjectTool.js", 
		"/js/legacy/DaclEdit.js", 
		"/js/legacy/Emailer.js", 

		// WorkFlow
		"/js/legacy/WorkFlow.js", 
		"/js/legacy/WorkFlow/Selector/User.js", 
		"/js/legacy/WorkFlow/Selector/MergeField.js", 
		"/js/legacy/WorkFlow/Action.js", 
        "/js/legacy/WorkFlow/Action/Child.js", 
		"/js/legacy/WorkFlow/Action/Invoice.js", 
		"/js/legacy/WorkFlow/Action/Task.js", 
		"/js/legacy/WorkFlow/Action/Notification.js", 
		"/js/legacy/WorkFlow/Action/Email.js", 
        "/js/legacy/WorkFlow/Action/Approval.js", 
        "/js/legacy/WorkFlow/Action/CallPage.js", 
        "/js/legacy/WorkFlow/Action/AssignRR.js", 
		"/js/legacy/WorkFlow/Action/Update.js",
		"/js/legacy/WorkFlow/Action/WaitCondition.js",
		"/js/legacy/WorkFlow/Action/CheckCondition.js",
		"/js/legacy/WorkFlow/ActionsGrid.js", 

		// AntView(s) and routers
		"/js/legacy/AntViewsRouter.js",
		"/js/legacy/AntViewManager.js",
		"/js/legacy/AntView.js",

		// AntObject
		"/js/legacy/AntObjectForms.js", 
		"/js/legacy/CAntObject.js", 
		"/js/legacy/CAntObjects.js", 
		"/js/legacy/CAntObjectView.js", 
		"/js/legacy/AntObjectInfobox.js", 
		"/js/legacy/AntObjectGroupingSel.js", 
		"/js/legacy/AntObjectTypeSel.js", 
		"/js/legacy/CAntObjectCond.js",
		//"/js/legacy/CAntObjectBrowser.js", 
		"/js/legacy/EntityDefinitionEdit.js",
		"/js/legacy/CAntObjectMergeWizard.js", 
		//"/js/legacy/CAntObjectImpWizard.js",   // Replaced with EntityImport AntWizard
        //"/js/legacy/CAntObjectFrmEditor.js", // Depricated
		"/js/legacy/AntObjectFormEditor.js", 

		// Entity - Replacing AntObject due to reserved namespace of Object
		"/js/legacy/EntityDefinition.js",
		"/js/legacy/EntityDefinition/Field.js",
		"/js/legacy/EntityDefinitionLoader.js",

		// AntObjectTemp
		"/js/legacy/AntObjectTemp.js", 
		"/js/legacy/AntObjectTempLoader.js", 

		// New browser and list
		"/js/legacy/AntObjectList.js", 
		"/js/legacy/AntObjectBrowser.js", 
		"/js/legacy/ObjectBrowser/Item.js", 
		"/js/legacy/ObjectBrowser/Item/Activity.js", 
		"/js/legacy/ObjectBrowser/Item/Notification.js", 
		"/js/legacy/ObjectBrowser/Item/StatusUpdate.js", 
		"/js/legacy/ObjectBrowser/Item/Comment.js", 
		"/js/legacy/ObjectBrowser/Toolbar.js", 
		"/js/legacy/ObjectBrowser/Toolbar/EmailThread.js", 
        "/js/legacy/AntObjectBrowserView.js", 
        "/js/legacy/AntObjectViewEditor.js", 
		"/js/legacy/AntCalendarBrowse.js", 

		// AntFs
		"/js/legacy/AntFsBrowser.js", 
		"/js/legacy/AntFsOpen.js", 
		"/js/legacy/AntFsUpload.js", 
		"/js/legacy/SWFUpload/swfupload.js",
		"/js/legacy/SWFUpload/plugins/swfupload.queue.js",

		// Olap
		"/js/legacy/COlapCube.js", 
		"/js/legacy/OlapCube.js", 
        "/js/legacy/OlapCube/Query.js", 
		"/js/legacy/OlapCube/Graph.js", 
		"/js/legacy/OlapCube/Table/Tabular.js", 
		"/js/legacy/OlapCube/Table/Summary.js", 
		"/js/legacy/OlapCube/Table/Matrix.js", 
        
        // Olap Report        
        "/js/legacy/OlapReport/Dialog.js", 
        "/js/legacy/OlapReport/Tabular.js", 
        "/js/legacy/OlapReport/Summary.js", 
        "/js/legacy/OlapReport/PivotMatrix.js", 

        // Class Objects
        "/js/legacy/Object/User.js", 
        
		// ANT Application
		"/js/legacy/AntApp.js", 
		"/js/legacy/AntAppSettings.js", 
		"/js/legacy/AntAppDash.js", 

		// Notifications
		"/js/legacy/NotificationMan.js", 

		// Searcher
		"/js/legacy/Searcher.js", 

		// ANT Chat
		"/js/legacy/AntChatMessenger.js", 
		"/js/legacy/AntChatClient.js",

		// Help notifications and tours
		"/js/legacy/HelpTour.js", 

		// "/email/CEmailThreadViewer.js",
		// "/js/legacy/CAntObjectLoader.js",
		// "/js/legacy/CAntObjectForm.js",
		// "/js/legacy/CAntObjectFormMem.js",
		// "/js/legacy/CEmailViewer.js",

		// Object loaders
		"/js/legacy/AntObjectLoader.js", // Base class
		"/js/legacy/ObjectLoader/Form.js", // UIML default class
        "/js/legacy/ObjectLoader/Plugin/global/Mem.js", // Global members plugin for forms
        "/js/legacy/ObjectLoader/Plugin/global/Attachments.js", // Global attachment plugin for forms
		"/js/legacy/ObjectLoader/Plugin/global/Uname.js", // Global Uname plugin for forms
		"/js/legacy/ObjectLoader/Plugin/global/StatusUpdate.js", // Status update plugin - used for projects and big items
		"/js/legacy/ObjectLoader/Plugin/global/Reminders.js", // Status update plugin - used for projects and big items
		"/objects/loaders/EmailThread.js",
		"/objects/loaders/EmailMessage.js",
        "/objects/loaders/EmailMessageCmp.js",
        //"/objects/loaders/User.js",
        "/objects/loaders/Report.js",
        "/objects/loaders/Dashboard.js",
		//"/objects/loaders/Calendar.js",

		// Object fileds fields - used mostly for forms
        "/js/legacy/Object/FieldInput.js", 
        "/js/legacy/Object/FieldInput/Alias.js", 
        "/js/legacy/Object/FieldInput/Bool.js", 
        "/js/legacy/Object/FieldInput/Date.js", 
        "/js/legacy/Object/FieldInput/Grouping.js", 
        "/js/legacy/Object/FieldInput/Number.js", 
        "/js/legacy/Object/FieldInput/Object.js", 
        "/js/legacy/Object/FieldInput/OptionalValues.js", 
        "/js/legacy/Object/FieldInput/Text.js", 
        "/js/legacy/Object/FieldInput/Timestamp.js", 

		// Validators
        "/js/legacy/Object/FieldValidator.js",
        
        // Email Includes
        "/js/legacy/spell/spellcheck.js",
        "/email/CVideoWizard.js",

		// Wizards
		"/js/legacy/AntWizard.js", 
		"/js/legacy/wizards/WorkflowWizard.js", 
	);
                  
	$dashboardWidgets = array(
							"/widgets/CWidWelcome.js", 
							"/widgets/CWidWeather.js", 
							"/widgets/CWidStocks.js", 
							"/widgets/CWidTasks.js", 
							"/widgets/CWidFriends.js",
							"/widgets/CWidSettings.js", 
							"/widgets/CWidBookmarks.js", 
							"/widgets/CWidRssManager.js", 
							"/widgets/CWidWebsearch.js",
							"/widgets/CWidCalendar.js", 
							"/widgets/CWidRss.js", 
							"/widgets/CWidReport.js", 
							"/widgets/CWidgetBrowser.js",
							"/widgets/CWidWebpage.js", 
							"/widgets/CWidActivity.js");    

	// Combine all scripts into a single file
	foreach ($libs as $lib)
	{
		if (isset($_SERVER['argv']) && $_SERVER['argv'][1] == "build")
		{
			include(APPLICATION_PATH . $lib);
			echo "\n";
		}
		else
		{
			echo '<script type="text/javascript" src="'.$lib.'?v=' . $verHash . '"></script>'."\n";
		}
	}
        
	// Widgets are handled differently because we will eventually be loading these dynamically
    foreach ($dashboardWidgets as $widget)
	{
		if (isset($_SERVER['argv']) && $_SERVER['argv'][1] == "build")
		{
			include(APPLICATION_PATH . $widget);
			echo "\n";
		}
		else
		{
			echo '<script type="text/javascript" src="'.$widget.'"></script>'."\n";
		}
	}
?>
