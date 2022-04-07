<?php

declare(strict_types=1);

namespace Netric\Workflow\ActionExecutor;

use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityGroupings\GroupingLoader;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Action to assign an entity to a user
 *
 * Params:
 *
 *  field       string REQUIRED The name of the user field we are updating.
 *  team_id     int OPTIONAL if set, we will randomize users within this team
 *  group_id    int OPTIONAL if set, we randomize users that are a member of this group
 *  users       string OPTIONAL A comma separated list of user IDs
 *
 * One of the three optional params must be set to determine what users to assign
 */
class AssignActionExecutor extends AbstractActionExecutor implements ActionExecutorInterface
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
        IndexInterface $entityIndex
    ) {
        $this->queryIndex = $entityIndex;

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
        // Get params
        $field = $this->getParam('field', $actOnEntity);
        $assignToTeam = $this->getParam('team_id', $actOnEntity);
        $assignToGroup = $this->getParam('group_id', $actOnEntity);
        $assignUsersList = $this->getParam('users', $actOnEntity);

        /*
         * We used to utilize a round-robin approach to this, but now
         * we are using randomization because it is bad/odd design to
         * have the action update itself. We may end up building some sort
         * of generic queuing system later, but for now random should
         * serve to accomplish what people are looking for.
         */
        if ($field) {
            $userId = null;
            if (!empty($assignToTeam)) {
                $userId = $this->getNextUserFromTeam($assignToTeam);
            } elseif (!empty($assignToGroup)) {
                $userId = $this->getNextUserFromGroup($assignToGroup);
            } elseif (!empty($assignUsersList)) {
                $userId = $this->getNextUserFromList($assignUsersList);
            }

            if ($userId !== null) {
                $actOnEntity->setValue($field, $userId);
                $this->entityLoader->save($actOnEntity);
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
        $query = new EntityQuery(ObjectTypes::USER, $this->getActionAccountId());
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
        $query = new EntityQuery(ObjectTypes::USER, $this->getActionAccountId());
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
