<?php
// Add includes for z-push because they don't use namespaces (sad)
ini_set(
    'include_path',
    ini_get('include_path') . PATH_SEPARATOR .
    dirname(__FILE__) . '/../../../../lib/ZPush'
);

require_once dirname(__FILE__) . '/../../../../lib/ZPush/vendor/autoload.php';

define('ZPUSH_CONFIG', dirname(__FILE__) . '/../../../../config/zpush.config.php');