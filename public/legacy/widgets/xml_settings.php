<?php
	require_once("../../../src/AntLegacy/AntConfig.php");
	require_once("../../../ant.php");
	require_once("../../../ant_user.php");
	require_once("../../../src/AntLegacy/aereus.lib.php/CChart.php");

	$dbh = $ANT->dbh;
	$USERNAME = $USER->name;
	$USERID =  $USER->id;
	$ACCOUNT = $USER->accountId;
	$FUNCTION = $_GET['function'];

	header("Content-type: text/xml");			// Returns XML document
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'; 

	switch ($FUNCTION)
	{
	case "get_themes":
		echo "<themes>";
		$result = $dbh->Query("select id, title from themes order by f_default DESC, title");
		$num = $dbh->GetNumberRows($result);
		for ($i = 0; $i < $num; $i++)
		{
			$row = $dbh->GetNextRow($result, $i);
			echo "<theme id='".$row['id']."'>".rawurlencode($row['title'])."</theme>";
		}
		$dbh->FreeResults($result);
		echo "</themes>";

		break;
	case "get_timezones":
		echo "<timezones>";
		$result = $dbh->Query("select id, name, code from user_timezones order by offs");
		$num = $dbh->GetNumberRows($result);
		for ($i = 0; $i < $num; $i++)
		{
			$row = $dbh->GetNextRow($result, $i);
			echo "<tz id='".$row['id']."' code='".$row['code']."' active=\"";
			echo (UserGetTimeZone($dbh, $USERID) == $row['code']) ? "1" : "0";
			echo "\">".rawurlencode($row['name'])."</tz>";
		}
		$dbh->FreeResults($result);
		echo "</timezones>";

		break;
	case "get_widgets":
		$dash = ($_GET['dash']) ? $_GET['dash'] : "home";
		echo "<widgets>";
		/*
		 *  where
			(
			type='system' and id not in 
			(select widget_id from user_dashboard_layout where user_id='$USERID' and dashboard='$dash')
			) or type != 'system' order by title
		 */
		$legacyWidgets = require(__DIR__ . "/app_widgets_data.php");
		foreach ($legacyWidgets as $widgetData)
		{
            $description = rawurlencode(stripslashes($widgetData['description']));
            if(empty($description))
                $description = "No description available.";
			echo "<widget id='{$widgetData['id']}' class_name='{$widgetData['class_name']}' description='{$description}'>".rawurlencode($widgetData['title'])."</widget>";
		}
		$dbh->FreeResults($result);
		echo "</widgets>";

		break;
	case "set_timezone":
		if ($_GET['tz'])
			$dbh->Query("update users set timezone_id='".$_GET['tz']."' where id='$USERID'");
		$retval = "1";
		break;
	case "add_widget":
		// Handled in the xml_actions file for each dasboard because it might be different
		break;
	}

	// Check for RPC
	if ($retval)
	{
		$res = "<retval>" . rawurlencode($retval) . "</retval>";
		$res .= "<cb_function>" . $_GET['cb_function'] . "</cb_function>";

		echo "<response>$res</response>";
	}
?>
