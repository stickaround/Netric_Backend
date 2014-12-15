<?php
/** 
 * Root level application initialization
 * 
 * This is similar to a 'main' routine in that it MUST be included in all executed scripts
 * because it is responsible for setting up and initializing the netric application and account.
 * 
 *  @author Sky Stebnicki <sky.stebnicki@aereus.com>
 *  @copyright 2014 Aereus
 */

// Setup Zend Autoloader
// ------------------------------------------------
$zf2Path = false;

if (is_dir('lib/ZF2/library')) {
    $zf2Path = 'lib/ZF2/library';
} elseif (getenv('ZF2_PATH')) {      // Support for ZF2_PATH environment variable or git submodule
    $zf2Path = getenv('ZF2_PATH');
} elseif (get_cfg_var('zf2_path')) { // Support for zf2_path directive value
    $zf2Path = get_cfg_var('zf2_path');
}

if ($zf2Path) {
    require_once $zf2Path . '/Zend/Loader/StandardAutoloader.php';
    $autoLoader = new Zend\Loader\StandardAutoloader(array(
        /*
        'prefixes' => array(
            'MyVendor' => __DIR__ . '/MyVendor',
        ),
        */
        'namespaces' => array(
            'Netric' => __DIR__ . '/lib/Netric',
            'NetricPublic' => __DIR__ . '/public',
            //'Elastica' => __DIR__ . '/lib/Elastica',
            'Zend' => $zf2Path,
        ),
        'fallback_autoloader' => true,
    ));
    $autoLoader->register();
}

if (!class_exists('Zend\Loader\StandardAutoloader')) {
    throw new RuntimeException('Unable to load ZF2. Define a ZF2_PATH environment variable.');
}

// Initialize Netric Application and Account
// ------------------------------------------------
$config = new Netric\Config();

// Initialize application
$application = new Netric\Application($config);

// Initialize account
//$account = $application->getAccount();

// Initialize the current user (if set)
// if ($_SESSION['user'])
//      $user = new Netric\User($account);
