<?php

/**
 * @author Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright Copyright (c) 2015 Aereus Corporation (http://www.aereus.com)
 */

namespace Netric\Workflow\ActionExecutor;

use Netric\Entity\EntityLoader;
use Netric\EntityQuery\EntityQuery;
use Netric\Workflow\WorkFlowLegacyInstance;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Action to assign an entity to a user
 *
 * Params in the 'data' field:
 *
 *  field       string REQUIRED The name of the user field we are updating.
 *  team_id     int OPTIONAL if set, we will randomize users within this team
 *  group_id    int OPTIONAL if set, we randomize users that are a member of this group
 *  users       string OPTIONAL A comma separated list of user IDs
 *
 * One of the three optional params must be set to determine what users to assign
 */
class AssignActionExecutor extends AbstractActionExecutor implements ActionInterface
{
    /**
     * Loader for entity groupings
     *
     * @var GroupingLoader
     */
    private $groupingsLoader = null;

    /**
     * EnityQuery index for querying entities
     *
     * @var IndexInterface
     */
    private $queryIndex = null;

    /**
     * Set all dependencies
     *
     * @param EntityLoader $entityLoader
     * @param ActionExecutorFactory $actionFactory
     * @param GroupingLoader $groupingsLoader
     * @param IndexInterface $queryIndex
     */
    public function __construct(
        EntityLoader $entityLoader,
        ActionExecutorFactory $actionFactory,
        GroupingLoader $groupingsLoader,
        IndexInterface $queryIndex
    ) {
        $this->groupingsLoader = $groupingsLoader;
        $this->queryIndex = $queryIndex;
        parent::__construct($entityLoader, $actionFactory);
    }

    /**
     * Execute this action
     *
     * @param WorkFlowLegacyInstance $workflowInstance The workflow instance we are executing in
     * @return bool true on success, false on failure
     */
    public function execute(WorkFlowLegacyInstance $workflowInstance)
    {
        $entity = $workflowInstance->getEntity();

        // Get merged params
        $params = $this->getParams($entity);

        /*
         * We used to utilize a round-robin approach to this, but now
         * we are using randomization because it is bad/odd design to
         * have the action update itself. We may end up building some sort
         * of generic queuing system later, but for now random should
         * serve to accomplish what people are looking for.
         */
        if ($params['field']) {
            $userGuid = null;
            if (isset($params['team_id'])) {
                $userGuid = $this->getNextUserFromTeam($params['team_id']);
            } elseif (isset($params['group_id'])) {
                $userGuid = $this->getNextUserFromGroup($params['group_id']);
            } elseif (isset($params['users'])) {
                $userGuid = $this->getNextUserFromList($params['users']);
            }

            if ($userGuid !== null) {
                $entity->setValue($params['field'], $userGuid);
                $this->entityLoader->save($entity);
                return true;
            }
        }

        // Could not assign it
        return false;
    }


    /**
     * Get the next user that is a member of a team
     *
     * @param int $teamId
     * @return int
     */
    private function getNextUserFromTeam($teamId)
    {
        $query = new EntityQuery(ObjectTypes::USER);
        $query->where("team_id")->equals($teamId);
        $result = $this->queryIndex->executeQuery($query);
        $num = $result->getTotalNum();
        $getIndex = mt_rand(0, ($num - 1));
        $user = $result->getEntity($getIndex);
        return $user->getEntityId();
    }

    /**
     * Get the next user that is a member of a user group
     *
     * @param int $groupId
     * @return int
     */
    private function getNextUserFromGroup($groupId)
    {
        $query = new EntityQuery(ObjectTypes::USER);
        $query->where("groups")->equals($groupId);
        $result = $this->queryIndex->executeQuery($query);
        $num = $result->getTotalNum();
        $getIndex = mt_rand(0, ($num - 1));
        $user = $result->getEntity($getIndex);
        return $user->getEntityId();
    }

    /**
     * Get the next user from a comma separated list
     *
     * @param string $listOfUsers
     * @return int
     */
    private function getNextUserFromList($listOfUsers)
    {
        $users = explode(',', $listOfUsers);

        if (!count($users)) {
            return null;
        }

        return $users[mt_rand(0, (count($users) - 1))];
    }
}
