<?php

declare(strict_types=1);

namespace NetricTest\Account\InitData\Sets;

use Netric\Mail\DataMapper\MailDataMapperFactory;
use Netric\Mail\DataMapper\MailDataMapperInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class MailDataMapperFactoryTest extends TestCase
{
    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testFactory()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $dataSet = $account->getServiceManager()->get(MailDataMapperFactory::class);
        $this->assertInstanceOf(MailDataMapperInterface::class, $dataSet);
    }
}
