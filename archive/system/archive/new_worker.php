<?php
	require_once("src/AntLegacy/AntConfig.php");
	require_once("src/AntLegacy/Ant.php");
	require_once("src/AntLegacy/ant_error_handler.php");
	require_once("src/AntLegacy/CAntFs.awp");
	require_once("src/AntLegacy/AntUser.php");
	require_once("src/AntLegacy/CWorker.php");
	require_once("src/AntLegacy/CAntObject.php");
	require_once("src/AntLegacy/CAntObjectList.php");
	require_once("src/AntLegacy/CAntObject.php");
	require_once("src/AntLegacy/Email.php");
	require_once("src/AntLegacy/customer/CCustomer.php");
	require_once("src/AntLegacy/email/email_functions.php");

	ini_set("max_execution_time", "0");	
	ini_set("max_input_time", "0");	
	ini_set('memory_limit', -1);

	$worker = new CWorker();
	if ($settings_version)
		$worker->setAntVersion($settings_version);
	while ($worker->work()) 
	{
		if (WORKER_SUCCESS != $worker->returnCode()) 
		{
			echo "Worker failed: " . $worker->error() . "\n";
		}
	}
?>
