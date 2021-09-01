<?php

declare(strict_types=1);

namespace Netric\Workflow;

/**
 * Constants used for scheudling workflows
 */
class WorkflowScheudleTimes
{
    /**
     * Units of time for relative times
     *
     * @var const
     */
    const TIME_UNIT_MINUTE = 1;
    const TIME_UNIT_HOUR = 2;
    const TIME_UNIT_DAY = 3;
    const TIME_UNIT_WEEK = 4;
    const TIME_UNIT_MONTH = 5;
    const TIME_UNIT_YEAR = 6;
}
