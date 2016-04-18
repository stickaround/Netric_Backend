#!/usr/bin/env php
<?php
/**
 * This is a worker that will listen for things to be done from the WorkerMan
 */

echo "\tStarting background worker process: " . getmypid() . "\n";
sleep(10);
echo "\tExiting background worker process: " . getmypid() . "\n";
exit();