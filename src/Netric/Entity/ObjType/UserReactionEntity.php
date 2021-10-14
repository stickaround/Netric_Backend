<?php

/**
 * Provide user extensions to base Entity class
 *
 * @author Marl Tumulak <marl@aereus.com>
 * @copyright 2021 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Authentication\AuthenticationService;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoader;
use Netric\Account\AccountContainerInterface;

/**
 * Description of User Reaction
 *
 * @author Marl Tumulak
 */
class UserReactionEntity extends Entity implements EntityInterface
{
    /**
     * The loader for a specific entity
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Grouping loader used to get user groups
     *
     * @var GroupingLoader
     */
    private $groupingLoader = null;

    /**
     * Container used to load accounts
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     * @param EntityLoader $entityLoader The loader for a specific entity
     * @param GroupingLoader $groupingLoader Handles the loading and saving of groupings
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     */
    public function __construct(
        EntityDefinition $def,
        EntityLoader $entityLoader,
        GroupingLoader $groupingLoader,
        AccountContainerInterface $accountContainer
    ) {
        $this->entityLoader = $entityLoader;
        $this->groupingLoader = $groupingLoader;
        $this->accountContainer = $accountContainer;

        parent::__construct($def);
    }
}
