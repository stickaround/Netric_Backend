<?php
namespace NetricTest\EntityDefinition;

use Netric\EntityDefinition\Field;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    /**
     * Test to assure that object refernece fields are seen as a reference
     *
     * @return void
     */
    public function testIsObjectReference()
    {
        $field = new Field();
        $field->type = 'object';
        $this->assertTrue($field->isObjectReference());
    }

    /**
     * Make sure that multi-value object fields are seen as an object refernce
     *
     * @return void
     */
    public function testIsObjectReferenceMulti()
    {
        $field = new Field();
        $field->type = 'object_multi';
        $this->assertTrue($field->isObjectReference());
    }

    /**
     * Assure that non-object fields are not seen as an object reference
     *
     * @return void
     */
    public function testIsNotObjectReference()
    {
        $field = new Field();
        $field->type = 'integer';
        $this->assertFalse($field->isObjectReference());
    }

    /**
     * Grouping fields should be seen as a grouping reference
     *
     * @return void
     */
    public function testIsGroupingReference()
    {
        $field = new Field();
        $field->type = 'fkey';
        $this->assertTrue($field->isGroupingReference());
    }

    /**
     * Assure that multi-value grouping fields are seen as a grouping reference
     *
     * @return void
     */
    public function testIsGroupingReferenceMulti()
    {
        $field = new Field();
        $field->type = 'fkey_multi';
        $this->assertTrue($field->isGroupingReference());
    }

    /**
     * Assure that non-grouping fields are not mistaken for references
     *
     * @return void
     */
    public function testIsNotGroupingReference()
    {
        $field = new Field();
        $field->type = 'integer';
        $this->assertFalse($field->isGroupingReference());
    }

    /**
     * Test the multi-value grouping refrence fields are recognized
     *
     * @return void
     */
    public function testIsMultiValueGrouping()
    {
        $field = new Field();
        $field->type = 'fkey_multi';
        $this->assertTrue($field->isMultiValue());
    }

    /**
     * Test the multi-value object refrence fields are recognized
     *
     * @return void
     */
    public function testIsMultiValueObject()
    {
        $field = new Field();
        $field->type = 'object_multi';
        $this->assertTrue($field->isMultiValue());
    }

    /**
     * Assure the grouping files that are not multi-value are not mistaken
     *
     * @return void
     */
    public function testIsNotMultiValue()
    {
        $field = new Field();
        $field->type = 'fkey';
        $this->assertFalse($field->isMultiValue());
    }
}
