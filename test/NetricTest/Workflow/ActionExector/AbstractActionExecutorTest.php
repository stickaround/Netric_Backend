<?php

declare(strict_types=1);

namespace NetricTest\Workflow\ActionExecutor;

use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\Workflow\ActionExecutor\AbstractActionExecutor;
use Netric\Error\Error;
use Netric\EntityDefinition\Field;
use Netric\EntityDefinition\EntityDefinition;

/**
 * Test action executor
 */
class AbstractActionExecutorTest extends TestCase
{
    /**
     * The base abstract class we are testing
     */
    private AbstractActionExecutor $actionExectorBase;

    /**
     * mock dependencies
     */
    private EntityLoader $mockEntityLoader;
    private WorkflowActionEntity $mockActionEntity;

    /**
     * Mock and stub out the action exector
     */
    protected function setUp(): void
    {
        $this->mockActionEntity = $this->createMock(WorkflowActionEntity::class);
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);

        // Create a new instance from the Abstract Class
        // This is a tad messy and magical, but it allows us to test the internals
        // of the abstract class without too much tooling.
        $this->actionExectorBase = new class(
            $this->mockEntityLoader,
            $this->mockActionEntity,
            'http://localhost' // Normally this comes form config
        ) extends AbstractActionExecutor
        {
            // Pass-through just to expose the getParam function which is protected
            public function getParamTest(string $name, EntityInterface $enitty)
            {
                return parent::getParam($name, $enitty);
            }
        };
    }

    /**
     * Make sure we can get plain values
     *
     * @return void
     */
    public function testGetParam(): void
    {
        $testData = ['param1' => 'test2'];

        // Set the entity action data
        $this->mockActionEntity->method("getData")->willReturn($testData);

        $testEntity = $this->createMock(EntityInterface::class);

        $this->assertEquals(
            $testData['param1'],
            $this->actionExectorBase->getParamTest('param1', $testEntity)
        );
    }

    /**
     * Make sure we can merge a value from the entity being acted on
     *
     * @return void
     */
    public function testGetParamWithEntityMergeField(): void
    {
        // Set data that will returned when getData is called on the action entity
        $testData = [
            'param1' => 'Hi <%first_name%>',
            'param2' => 'Hi <%last_name%>'
        ];
        $this->mockActionEntity->method("getData")->willReturn($testData);

        // Create entity mock and return value for first_name call to getValue
        $testEntity = $this->createMock(EntityInterface::class);
        $mockReturnMap = [
            ['first_name', 'Sky'],
        ];
        $testEntity->method('getValue')->will($this->returnValueMap($mockReturnMap));

        // Should have merged
        $this->assertEquals(
            'Hi Sky',
            $this->actionExectorBase->getParamTest('param1', $testEntity)
        );

        // Should be empty - empty values get replaced with null so the merge vars are not left
        $this->assertEquals(
            'Hi ',
            $this->actionExectorBase->getParamTest('param2', $testEntity)
        );
    }

    /**
     * Test <%entity_link%>
     *
     * @return void
     */
    public function testGetParamWithEntityLinkMergeField(): void
    {
        // Set data that will returned when getData is called on the action entity
        $testData = [
            'param1' => 'Link: <%entity_link%>',
        ];
        $this->mockActionEntity->method("getData")->willReturn($testData);


        // Create entity mock and assure we can get the ID
        $testEntity = $this->createMock(EntityInterface::class);
        $testEntity->method("getEntityId")->willReturn('TEST-UUID');

        // Should have merged
        $this->assertEquals(
            'Link: http://localhost/browse/TEST-UUID',
            $this->actionExectorBase->getParamTest('param1', $testEntity)
        );
    }

    /**
     * Test <%id%> changed to <%entity_id%>
     *
     * @return void
     */
    public function testGetParamWithEntityIdMergeField(): void
    {
        // Set data that will returned when getData is called on the action entity
        $testData = [
            'param1' => 'ID: <%id%>',
        ];
        $this->mockActionEntity->method("getData")->willReturn($testData);


        // Create entity mock and assure we can get the ID
        $testEntity = $this->createMock(EntityInterface::class);
        $testEntity->method("getEntityId")->willReturn('TEST-UUID');

        // Should have merged
        $this->assertEquals(
            'ID: TEST-UUID',
            $this->actionExectorBase->getParamTest('param1', $testEntity)
        );
    }

    /**
     * Test <%objectfieldname.referencedfieldvalue%> to see if we can do deep dereferences
     *
     * This is often used for things like owner_id.email to send emails
     *
     * @return void
     */
    public function testGetParamWithDeepEntityMergeField(): void
    {
        $testUUID = 'TEST-UUID';

        // Set data that will returned when getData is called on the action entity
        $testData = [
            'param1' => '<%assigned_user.email%>',
        ];
        $this->mockActionEntity->method("getData")->willReturn($testData);

        // Create entity mock and return value for first_name call to getValue
        $testEntityActedOn = $this->createMock(EntityInterface::class);
        $testEntityActedOn->method('getAccountId')->willReturn('ACC-UUID');
        $testEntityActedOn->method('getValue')->will($this->returnValueMap([
            ['assigned_user', $testUUID],
        ]));

        // Mock entity definition and field
        $field = new Field();
        $field->type = Field::TYPE_OBJECT;
        $mockEntityDefinition = $this->createStub(EntityDefinition::class);
        $mockEntityDefinition->method('getField')->willReturn($field); // Should only be called once
        $testEntityActedOn->method("getDefinition")->willReturn($mockEntityDefinition);

        // Now mock entityLoader returning the referenced entity if the ID is right
        $testEntityReference = $this->createMock(EntityInterface::class);
        $testEntityReference->method('getValue')->will($this->returnValueMap([
            ['email', 'test@test.com'],
        ]));
        $this->mockEntityLoader->method('getEntityByid')->will($this->returnValueMap([
            [$testUUID, 'ACC-UUID', $testEntityReference],
        ]));

        // Should work great with the right field type
        $this->assertEquals(
            'test@test.com',
            $this->actionExectorBase->getParamTest('param1', $testEntityActedOn)
        );
    }

    /**
     * Make sure get last error works
     *
     * @return void
     */
    public function testGetLastError(): void
    {
        $this->actionExectorBase->addError(new Error('Test'));
        $this->assertNotNull($this->actionExectorBase->getLastError());
    }

    /**
     * Soemtimes we want to return all errors for logging, make sure that works to
     *
     * @return void
     */
    public function testGetErrors(): void
    {
        $this->actionExectorBase->addError(new Error('Test'));
        $this->assertNotNUll($this->actionExectorBase->getLastError());
    }
}
