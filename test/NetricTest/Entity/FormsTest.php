<?php

/**
 * Test the forms factory for getting and setting entity forms for the UI
 *
 * We use the comment type entity since we do not allow the user to customize it
 */

namespace NetricTest\Entity;

use Netric\Entity;
use PHPUnit\Framework\TestCase;
use Netric\Entity\FormsFactory;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

class FormsTest extends TestCase
{
    /**
     * Tennant account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Form service
     *
     * @var \Netric\Entity\Form
     */
    private $formService = null;

    /**
     * Administrative user
     *
     * We test for this user since he will never have customized forms
     *
     * @var \Netric\User
     */
    private $user = null;

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sm = $this->account->getServiceManager();
        $this->formService = $sm->get(FormsFactory::class);
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
    }

    public function testCreateForUser()
    {
        $testXml = "<field name='name' />";
        $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $def = $defLoader->get(ObjectTypes::COMMENT);

        // Save new small form
        $this->formService->saveForUser($def, $this->user->getEntityId(), "test", $testXml);

        // Get the form for the account and see if it matches what we just saved
        $testSaveXml = $this->formService->getFormUiXml($def, $this->user, "test");
        $this->assertEquals($testXml, $testSaveXml);

        // Get the form using ::getDeviceForms()
        $deviceForms = $this->formService->getDeviceForms($def, $this->user);

        // Cleanup by setting it to null
        $this->formService->saveForUser($def, $this->user->getEntityId(), "test", null);
    }

    public function testCreateForAccount()
    {
        $testXml = "<field name='name' />";
        $defLoader = $this->account->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
        $def = $defLoader->get(ObjectTypes::COMMENT);

        // Save new small form
        $this->formService->saveForAccount($def, "test", $testXml);

        // Get the form for the account and see if it matches what we just saved
        $testSaveXml = $this->formService->getFormUiXml($def, $this->user, "test");
        $this->assertEquals($testXml, $testSaveXml);

        // Cleanup by setting it to null
        $this->formService->saveForAccount($def, "test", null);
    }
}
