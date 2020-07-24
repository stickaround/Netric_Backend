<?php
// Add includes for z-push because they don't use namespaces (sad)
ini_set(
    'include_path',
    ini_get('include_path') . PATH_SEPARATOR .
    __DIR__ . '/../../../../src/ZPush'
);

require_once __DIR__ . '/../../../../src/ZPush/vendor/autoload.php';

define('ZPUSH_CONFIG', __DIR__ . '/../../../../config/zpush.config.php');
