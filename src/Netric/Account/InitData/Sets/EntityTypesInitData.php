<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Account\Account;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Account\InitData\InitDataInterface;
use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperInterface;
use Netric\EntityDefinition\EntityDefinitionLoader;

/**
 * Return array of data initializers to run for an account
 */
class EntityTypesInitData implements InitDataInterface
{
    /**
     * Entity type data to initialize
     */
    private array $typesData;

    /**
     * Entity definition loader
     */
    private EntityDefinitionLoader $defLoader;

    /**
     * Entity definition data mapper
     */
    private EntityDefinitionDataMapperInterface $defDatamapper;

    /**
     * Constructor
     *
     * @param array $typesData Array of data to use for initializing types
     * @param EntityDefinitionDataMapperInterface $defDatamapper
     * @param EntityDefinitionLoader $defLoader
     */
    public function __construct(
        array $typesData,
        EntityDefinitionDataMapperInterface $defDatamapper,
        EntityDefinitionLoader $defLoader
    ) {
        $this->typesData = $typesData;
        $this->defLoader = $defLoader;
        $this->defDatamapper = $defDatamapper;
    }

    /**
     * Insert or update initial data for account
     *
     * @param Account $account
     * @return bool
     */
    public function setInitialData(Account $account): bool
    {
        // Loop through each type and add it if it does not exist
        foreach ($this->typesData as $objDefData) {
            // First try loading to see if it already exists
            $def = $this->defDatamapper->fetchByName($objDefData['obj_type'], $account->getAccountId());
            if (!$def) {
                $def = new EntityDefinition($objDefData['obj_type'], $account->getAccountId());
                $def->fromArray($objDefData);
                $this->defDatamapper->save($def);
                if (!$def->getEntityDefinitionId()) {
                    throw new \RuntimeException("Could not save " . $this->defDatamapper->getLastError());
                }
            }

            // Make sure it has all the latest changes from the local data/entity_definitions/
            $this->defDatamapper->updateSystemDefinition($def);

            // Clear any cache for the definition
            $this->defLoader->clearCache($objDefData['obj_type'], $account->getAccountId());
        }

        return true;
    }
}
