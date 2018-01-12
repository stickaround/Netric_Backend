<?php
    // Built files are hashed for cache breaking so use the manifest to get the absolute name and path
$manifest = ['netric.js' => '/mobile/js/netric.js', 'netric.css' => '/mobile/css/netric.css'];

if (file_exists("webpack-manifest.json")) {
    $manifest = json_decode(file_get_contents("webpack-manifest.json"), true);
} else {
    throw new Exception("Netric webapp not installed");
}

    // Setup autoloader
include(__DIR__ . "/../../init_autoloader.php");

    // Get netric config
$configLoader = new Netric\Config\ConfigLoader();
$applicationEnvironment = (getenv('APPLICATION_ENV')) ? getenv('APPLICATION_ENV') : "production";
$config = $configLoader->fromFolder(__DIR__ . "/../../config", $applicationEnvironment);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline' data: gap: https://ssl.gstatic.com 'unsafe-eval'; style-src 'self' 'unsafe-inline'; connect-src 'self' http://*.netric.com https://*.netric.com http://netric.myaereus.com; media-src *; font-src *">
        <meta name="format-detection" content="telephone=no">
        <meta name="msapplication-tap-highlight" content="no">
        <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width">
        <link rel="stylesheet" id='netric-css-base' href="<?php print($manifest['netric.css']); ?>" />
        <title>Netric</title>
        <script type="text/javascript" src="<?php print($manifest['netric.js']); ?>"></script>
		<script>
            function startApplication() {
                netric.Application.load(function(app){
                    app.run(document.getElementById("netric-app"));
                }, "<?php echo $config->loginserver; ?>", "/mobile");
            }
        </script>
    </head>
    <body onload="startApplication();">
        <div id='netric-app'></div>
    </body>
</html>
