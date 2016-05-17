<?php
/**
 * Db implementation of module DataMapper test
 */
namespace NetricTest\Account\Module\DataMapper;

use Netric\Account\Module\DataMapper;

class DataMapperDbTest extends AbstractDataMapperTests
{
    /**
     * Get Db implementation of DataMapper
     *
     * @return DataMapper\DataMapperInterface
     */
    public function getDataMapper($setUserAdmin=true)
    {
        $account = \NetricTest\Bootstrap::getAccount();

        $sl = $account->getServiceManager();
        $db = $sl->get("Db");
        $config = $sl->get("Config");

        // Setup a user for testing
        $loader = $account->getServiceManager()->get("EntityLoader");

        // If we set the user as admin, then we will be able to get the settings module
        if($setUserAdmin)
            $user = $loader->get("user", \Netric\Entity\ObjType\UserEntity::USER_ADMINISTRATOR);
        else
            $user = $loader->get("user", \Netric\Entity\ObjType\UserEntity::USER_WORKFLOW);

        return new DataMapper\DataMapperDb($db, $config, $user);
    }
}