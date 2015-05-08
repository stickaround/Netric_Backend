<?php
/**
 * Router handles loading a controller from a URL route
 */
require_once("init_application.php");

$controller = $_GET['controller'];

// Normalize the controller name to map to a class name
$controller = str_replace("_", " ", $controller);
$controller = str_replace("-", " ", $controller);
$controller = ucwords($controller);
$controller = str_replace(" ", "", $controller);

$path = "lib/Netric/Controller/".$controller."Controller.php";

// Normalize the function name to change get-data to getData
$functionName = $_REQUEST['function'];
if (!$functionName)
    die("Invalid function name");
$functionName = str_replace("-", " ", $functionName);
$functionName = ucwords($functionName);
$functionName = str_replace(" ", "", $functionName);
$functionName = lcfirst($functionName);

// Load controller class
if (file_exists($path))
{
    include($path);
}
else
{
    die("Invalid controller");
}

// Set headers to allow CORS since we are using /svr resources in multiple clients
// @see http://www.html5rocks.com/en/tutorials/cors/#toc-adding-cors-support-to-the-server
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Authentication");

// Lod up the router and run the $functionName
$svr = new Netric\Mvc\Router($application);
$svr->setClass("Netric\\Controller\\" . $controller . "Controller");
$svr->run($functionName);