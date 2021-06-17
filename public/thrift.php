<?php

error_reporting(E_ALL);

// Setup autoloader
include(__DIR__ . "/../vendor/autoload.php");

// use Thrift\Factory\TTransportFactory;
// use Thrift\Factory\TBinaryProtocolFactory;
// use Thrift\Transport\TBufferedTransport;
// use Thrift\Protocol\TBinaryProtocol;
// use Thrift\Transport\TPhpStream;
// use NetricApi\TestProcessor;
// use NetricApi\AuthenticationProcessor;
// use Netric\Handler\TestHandler;
// use Netric\Handler\AuthenticationHandler;

// /**
//  * For now we just have a static list of handlers
//  * 
//  * Later we'll make a router with a loader
//  */
// $handler = null;
// $processor = null;
// switch ($_REQUEST['handler']) {
//     case 'test':
//         $handler = new TestHandler();
//         $processor = new TestProcessor($handler);
//         break;
//     case 'authentication':
//         $handler = new AuthenticationHandler();
//         $processor = new AuthenticationProcessor($handler);
//         break;
//     default:
//         die("No hanlder specified");
// }

// $transportFactory = new TTransportFactory();
// $protocolFactory = new TBinaryProtocolFactory(true, true);


// // Run as cli mode, listen port, official implementation
// // $transport = new TServerSocket('localhost', 9090);
// // $server = new TSimpleServer($processor, $transport, $transportFactory, $transportFactory, $protocolFactory, $protocolFactory);
// // $server->serve();

// $transport = new TBufferedTransport(new TPhpStream(TPhpStream::MODE_R | TPhpStream::MODE_W));
// $protocol = new TBinaryProtocol($transport, true, true);

// header('Content-Type', 'application/x-thrift');
// try {
//     $transport->open();
//     $processor->process($protocol, $protocol);
//     $transport->close();
// } catch (Exception $ex) {
//     error_log(var_export($ex, true));
// }

use Netric\Application\Application;
use Aereus\Config\ConfigLoader;

$configLoader = new ConfigLoader();
$applicationEnvironment = (getenv('APPLICATION_ENV')) ? getenv('APPLICATION_ENV') : "production";

// Setup the new config
$config = $configLoader->fromFolder(__DIR__ . "/../config", $applicationEnvironment);

// Run the application
Application::init($config)->runThrift();
