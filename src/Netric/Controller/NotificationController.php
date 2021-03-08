<?php

namespace Netric\Controller;

use Netric\Mvc;
use Netric\Mvc\ControllerInterface;
use Netric\Mvc\AbstractFactoriedController;
use Netric\Account\AccountContainerFactory;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Request\HttpRequest;
use Netric\Authentication\AuthenticationService;
use Netric\Account\Module\ModuleService;

/**
 * Controller for interacting with notifications
 */
class NotificationController extends AbstractFactoriedController implements ControllerInterface
{
    /**
     * Setup a subscription to a push notification channel
     *
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public function postSubscribeAction(HttpRequest $request): HttpResponse
    {
    }
}
