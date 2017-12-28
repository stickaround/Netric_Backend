<?php
/**
 * Cleanup email system
 *
 * @category	Ant
 * @package		Email
 * @subpackage	Queue_Process
 * @copyright	Copyright (c) 2003-2011 Aereus Corporation (http://www.aereus.com)
 */
require_once("../../lib/AntConfig.php");
require_once("src/AntLegacy/Ant.php");
require_once("src/AntLegacy/AntService.php");
require_once("services/EmailCleanup.php");

ini_set("memory_limit", "-1");	

$svc = new EmailCleanup();
$svc->run();
echo "Finished!\n";
