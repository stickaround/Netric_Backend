<?php

declare(strict_types=1);

namespace Netric\Mail\DataMapper;

use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

class MailDataMapperFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $sl
     * @return void
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $relationalDbCon = $sl->get(RelationalDbContainerFactory::class);
        return new MailDataMapperPgsql($relationalDbCon);
    }
}
