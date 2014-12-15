<?php
require_once("init_application.php");

$controller = $_GET['controller'];
$path = "lib/Netric/Controller/".$controller."Controller.php";

if (!$_REQUEST['function'])
    die("Invalid function name");

// Load controller class
if (file_exists($path))
{
    include($path);
}
else
{
    die("Invalid controller");
}

$svr = new Netric\Mvc\Router($application);
$svr->setClass("Netric\\Controller\\" . $controller . "Controller");
$svr->run($_REQUEST['function']);