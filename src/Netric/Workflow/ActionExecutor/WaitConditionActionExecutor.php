<?php

declare(strict_types=1);

namespace Netric\Workflow\ActionExecutor;

use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\WorkerMan\WorkerService;
use Netric\WorkerMan\Worker\WorkflowWaitActionWorker;
use Netric\Workflow\WorkflowScheudleTimes;
use DateTime;
use InvalidArgumentException;

/**
 * Action used for delaying the execution of child actions
 *
 * Params that are set:
 *  when_unit       int REQUIRED A time unit from WorkFlowLegacy::TIME_UNIT_*
 *  when_interval   int REQUIRED An interval to use with the unit like 1 month or 1 day
 */
class WaitConditionActionExecutor extends AbstractActionExecutor implements ActionExecutorInterface
{
    /**
     * Scheudler serivice to queue jobs into the future
     *
     * @var WorkerService
     */
    private WorkerService $worker;

    /**
     * Constructor
     *
     * @param EntityLoader $entityLoader
     * @param WorkflowActionEntity $actionEntity
     * @param string $appliactionUrl
     */
    public function __construct(
        EntityLoader $entityLoader,
        WorkflowActionEntity $actionEntity,
        string $applicationUrl,
        WorkerService $workerService
    ) {
        $this->worker = $workerService;

        // Should always call the parent constructor for base dependencies
        parent::__construct($entityLoader, $actionEntity, $applicationUrl);
    }

    /**
     * Execute an action on an entity
     *
     * @param EntityInterface $actOnEntity The entity (any type) we are acting on
     * @param UserEntity $user The user who is initiating the action
     * @return bool true on success, false on failure
     */
    public function execute(EntityInterface $actOnEntity, UserEntity $user): bool
    {
        // Entity must be saved to meet conditions
        if (!$actOnEntity->getEntityId()) {
            return false;
        }

        // Get params - we pass $actOnEntity because a param might merge values
        // from the entity like <%name%> or <%owner_id%>
        $whenUnit = $this->getParam('when_unit', $actOnEntity);
        $whenInterval = $this->getParam('when_interval', $actOnEntity);

        // Execute now if no interval is set or it's been set to 'execute immediately'
        if (!$whenUnit || !$whenInterval) {
            return true;
        }

        /**
         * Set the payload
         */
        $payload = [
            'action_id' => $this->getActionEntityId(),
            'account_id' => $this->getActionAccountId(),
            'entity_id' => $actOnEntity->getEntityId(),
            'user_id' => $user->getEntityId(),
        ];

        /*
         * Determine the execute date from $whenUnit and $whenInterval.
         * This will eventually be a lot more complex where we can key off of
         * any field in $workFlowInstance->getEntityId() but right now we
         * just schedule everything in the future relative to 'now'
         */
        $executeTime = $this->getExecuteDate($whenUnit, $whenInterval);
        $today = new DateTime();
        $numSecondsBetween = $executeTime->getTimestamp() - $today->getTimestamp();

        /*
         * Schedule the action for later - to see how execution continues
         * please review the WorkflowWaitActionWorker. It essentially
         * resumes execution of the workflow through the WorkflowService
         * beginning at this action.
         */
        $this->worker->doWorkDelayed(
            WorkflowWaitActionWorker::class,
            $payload,
            $numSecondsBetween
        );

        /*
         * Return false to stop processing children (for now)
         * The WorfkowWaitActionWorker will continue processing any children
         * after the specified wait time
         */
        return false;
    }

    /**
     * Get the real date this workflow should execute based on params
     *
     * @param int $whenUnit A unit of time from Where::TIME_UNIT_*
     * @param int $whenInterval How many whenUnits to add
     * @return DateTime DateTime in the future to execute
     */
    private function getExecuteDate($whenUnit, $whenInterval): DateTime
    {
        $intervalUnit = $this->getDateIntervalUnit($whenUnit);

        /*
         * The unit will return lower case 'm' for minutes, since \DateInterval
         * stupidly uses a preceding 'T' before time intervals but the same character
         * 'M' to represent month as it does minutes. We just have getDateIntervalUnits return
         * a lower case 'm' for minutes, then prepend the 'T' below.
         */
        $prefix = ($intervalUnit === 'H' || $intervalUnit === 'm') ? $pre = "PT" : 'P';

        // Translate our 'm' (lowercase) for minute back to uppercase 'M' for \DateInterval (see above)
        if ($intervalUnit === 'm') {
            $intervalUnit = 'M';
        }

        $dateInterval = new \DateInterval($prefix . $whenInterval . $intervalUnit);
        $executeDate = new \DateTime();
        $executeDate->add($dateInterval);
        return $executeDate;
    }

    /**
     * Convert a WorkflowScheudleTimes::TIME_UNIT_* to a DateInterval textual unit
     *
     * @param int $unit A unit id from WorkFlowLegacy::TIME_UNIT_*
     * @return string Unit character used for PHP's DateInterval constructor
     * @throws InvalidArgumentException if we do not recognize the constant being passed
     */
    private function getDateIntervalUnit($unit): string
    {
        switch ($unit) {
            case WorkflowScheudleTimes::TIME_UNIT_YEAR:
                return 'Y';

            case WorkflowScheudleTimes::TIME_UNIT_MONTH:
                return 'M';

            case WorkflowScheudleTimes::TIME_UNIT_WEEK:
                return 'W';

            case WorkflowScheudleTimes::TIME_UNIT_DAY:
                return 'D';

            case WorkflowScheudleTimes::TIME_UNIT_HOUR:
                return 'H';

            case WorkflowScheudleTimes::TIME_UNIT_MINUTE:
                return 'm';

            default:
                // This should never happen, but if it does throw an exception
                throw new InvalidArgumentException("No DateTinerval conversion for unit $unit");
        }
    }
}
