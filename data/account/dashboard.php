<?php
/**
 * System-wide dashboard that should exist in every account
 *
 * Refer to data/modules/home.php to get the route used for dashboard
 */
return array(
    // Home Activity System Dashboard
    'home.activity' => array(
        "uname" => "activity",
        "Name" => "System Wide Dashboard",
        "app_dash" => "home.activity",
        "description" => "Summary of activity displayed on the home page to all users",
        "scope" => "system",
        "num_columns" => 2
    )
);
