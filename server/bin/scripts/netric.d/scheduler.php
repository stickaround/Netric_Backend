#!/usr/bin/env php
<?php
/**
 * This is a worker that will listen for things to be done from the WorkerMan
 */

/*
 * A tick is an event that occurs for every N low-level tickable
 * statements executed by the parser within the declare block.
 * We are basically telling the pcntl_signal to check after every
 * single operation to see if we should exit.
 */
declare(ticks = 1);

/*
 * Handle signals coming in from the outside
 */
function sig_handler_stop($signo) {
    echo "\tStopping scheduler gracefully with " . $signo . "\n";
    exit();
}
// setup signal handlers to actually catch and direct the signals
pcntl_signal(SIGTERM, "sig_handler_stop");
pcntl_signal(SIGHUP,  "sig_handler_stop");
pcntl_signal(SIGINT, "sig_handler_stop");

while (true) {
    echo "\tScheduling tasks...\n";
    sleep(3);
}

exit();