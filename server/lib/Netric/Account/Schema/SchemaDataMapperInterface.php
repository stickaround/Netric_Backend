<?php
/**
 * Interface for DataMappers that will handle schema creation and updates
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2015 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\Account\Schema;

interface SchemaDataMapperInterface
{
	/**
	 * Create the initial schema
	 * 
	 * @param array $schema The schema defintion array
	 */
	public function create(array $schemaDef);

	/**
	 * Update an existing schema to match the definition
	 *
	 * @param array $schema The schema defintion array
	 */
	public function update(array $schemaDef);
}