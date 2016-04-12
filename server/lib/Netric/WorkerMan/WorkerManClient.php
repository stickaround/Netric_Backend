<?php
/**
 * Example from PHP
 *
 * TODO: Implement generic client to send jobs
 */
$client= new GearmanClient();

$client->addServer(‘127.0.0.1’);

$client->setCreatedCallback("create_change");

$client->setDataCallback("data_change");

$client->setStatusCallback("status_change");

$client->setCompleteCallback("complete_change");

$client->setFailCallback("fail_change");

$data_array =array('mydata'=>’task’);

$task= $client->addTask("reverse", "mydata", $data_array);

$task2= $client->addTaskLow("reverse", "task", NULL);

echo "DONE\n";

function create_change($task)
{
    echo "CREATED: " . $task->jobHandle() . "\n";
}

function status_change($task)
{
    echo "STATUS: " . $task->jobHandle() . " - " . $task->taskNumerator() .
        "/" . $task->taskDenominator() . "\n";
}

function complete_change($task)
{
    echo "COMPLETE: " . $task->jobHandle() . ", " . $task->data() . "\n";
}

function fail_change($task)
{
    echo "FAILED: " . $task->jobHandle() . "\n";
}

function data_change($task)
{
    echo "DATA: " . $task->data() . "\n";
}
Function Client_error()
{
    if (! $client->runTasks())
        return $client->error() ;
}

?>