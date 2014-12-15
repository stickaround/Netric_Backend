<?php
	require_once("../lib/AntConfig.php");
	require_once("ant.php");
	require_once("ant_user.php");
	require_once("lib/content_table.awp");
	require_once("lib/CToolTabs.awp");
	require_once("lib/WindowFrame.awp");
	require_once("lib/date_time_functions.php");
	require_once("lib/CPopup.awp");
	require_once("lib/Button.awp");
	require_once("lib/CDropdownMenu.awp");
	require_once("lib/CToolTable.awp");
	require_once("contacts/contact_functions.awp");
	require_once("customer/customer_functions.awp");
	require_once("lib/CAutoComplete.awp");
	require_once("lib/aereus.lib.php/CPageCache.php");
	
	$FID = $_GET['fid'];
									  
	$dbh = $ANT->dbh;
	$USERNAME = $USER->name;
	$USERID =  $USER->id;
	$ACCOUNT = $USER->accountId;
	$THEME = $USER->themeName;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>ANT Tutorials</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="STYLESHEET" type="text/css" href="/css/<?php echo UserGetTheme($dbh, $USERID, 'css'); ?>">
<script language="javascript" type="text/javascript" src="/calendar/calendar_functions.js"></script>
<script language="javascript" type="text/javascript" src="/customer/customer_functions.js"></script>
<?php
	include("../lib/aereus.lib.js/js_lib.php");
?>
<script language="javascript" type="text/javascript">

</script>
<style type="text/css">
body
{
	overflow: auto;
}
</style>
</head>

<body class='popup'>
<div id='bdy' class='popup_body'>
<?php
	$tbl = new CToolTable;
	// Create table headers
	$tbl->StartHeaders();
	$tbl->AddHeader("");
	$tbl->AddHeader("Revision", 'center', '50px');
	$tbl->AddHeader("Date &amp; Time");
	$tbl->EndHeaders();

	$result = $dbh->Query("select file_title, revision, category_id, to_char(time_updated, 'MM/DD/YYYY HH12:MI:SS AM') as ts_updated from user_files where id='$FID'");
	if ($dbh->GetNumberRows($result))
	{
		$row = $dbh->GetRow($result, $i);
		$CAT = $row["category_id"];
		$fname = $row["file_title"];

		$tbl->StartRow();
		$tbl->AddCell("<a href='/files/$FID'>$fname</a>", true);
		$tbl->AddCell($row['revision'], false, 'center');
		$tbl->AddCell($row['ts_updated']);
		$tbl->EndRow();

		$result = $dbh->Query("select id, revision, to_char(time_updated, 'MM/DD/YYYY HH12:MI:SS AM') as ts_updated  
								from user_files where category_id='$CAT' and file_title='".$dbh->Escape($fname)."' and id!='$FID' order by revision DESC");
		$num = $dbh->GetNumberRows($result);
		for ($i = 0; $i < $num; $i++)
		{
			$row = $dbh->GetRow($result, $i);

			$tbl->StartRow();
			$tbl->AddCell("<a href='/files/".$row['id']."'>$fname</a>", true);
			$tbl->AddCell($row['revision'], false, 'center');
			$tbl->AddCell($row['ts_updated']);
			$tbl->EndRow();
		}
	}

	$tbl->PrintTable();
?>
</div>
</body>
</html>
