<?php
	require_once('src/AntLegacy/Worker.php'); 
	require_once('src/AntLegacy/pdf/class.ezpdf.php'); 

	if (is_array($g_workerFunctions))
	{
		$g_workerFunctions["email/inject"] = "email_inject";
	}

	function email_inject($job)
	{
		$data = $job->workload();

		return strrev($data);
	}
?>
