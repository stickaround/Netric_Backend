<?php
namespace NetricTest\Application;

use Netric\Application\DataMapperInterface;
use Netric\Application\DataMapperPgsql;
use Netric\Config;
use PHPUnit_Framework_TestCase;

class DataMapperPgsqlTest extends AbstractDataMapperTests
{
    /**
     * Get an implementation specific DataMapper
     *
     * @return DataMapperInterface
     */
    protected function getDataMapper()
    {
        $config = new Config();
        return new DataMapperPgsql(
            $config->db['syshost'],
            $config->db['sysdb'],
            $config->db['user'],
            $config->db['password']
        );
    }
}