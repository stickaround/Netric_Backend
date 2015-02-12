<?php
/**
 * Collection factory interface
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2003-2015 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\EntitySync\Collection;

interface CollectionFactoryInterface
{
	/**
	 * Factory for creating collections and injecting all dependencies
	 *
	 * @param \Netric\ServiceManager $sm
	 * @param int $type The type to load as defined by \Netric\EntitySync::COLL_TYPE_*
	 * @param array $data Optional data to initialize into the collection
	 */
	public static function create(\Netric\ServiceManager $sm, $type, $data=null);
}
