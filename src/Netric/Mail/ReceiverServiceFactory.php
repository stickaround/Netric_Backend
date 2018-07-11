<?php
namespace Netric\Mail;

use Netric\Crypt\VaultServiceFactory;
use Netric\EntitySync\Collection\CollectionFactory;
use Netric\ServiceManager;
use Netric\Entity\EntityLoaderFactory;
use Netric\Config\ConfigFactory;
use Netric\EntitySync\EntitySyncFactory;
use Netric\EntityGroupings\LoaderFactory;
use Netric\Log\LogFactory;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Create a service for receiving mail from a mail server
 */
class ReceiverServiceFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return SenderService
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $user = $sl->getAccount()->getUser();
        $collectionFactory = new CollectionFactory($sl);
        $entitySyncServer = $sl->get(EntitySyncFactory::class);
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $groupingsLoader = $sl->get(LoaderFactory::class);
        $log = $sl->get(LogFactory::class);
        $index = $sl->get(IndexFactory::class);
        $vaultService = $sl->get(VaultServiceFactory::class);
        $config = $sl->get(ConfigFactory::class);
        $deliveryService = $sl->get(DeliveryServiceFactory::class);

        return new ReceiverService(
            $log,
            $user,
            $entitySyncServer,
            $collectionFactory,
            $entityLoader,
            $groupingsLoader,
            $index,
            $vaultService,
            $config->email,
            $deliveryService
        );
    }
}
