<?php
/*
 * Interface definition for a file system data mapper
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\EntityQuery\Index;

/**
 * Define
 */
interface FSDataMapperInterface
{
    public function readFile(File $file, $bytes=null, $offset=null);

    public function uploadFile(File $file)
}