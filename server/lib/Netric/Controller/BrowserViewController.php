<?php
/**
 * Controller for handling Browser View
 */
namespace Netric\Controller;

use Netric\Mvc;
use Netric\Entity\BrowserView\BrowserView;

class BrowserViewController extends Mvc\AbstractController
{
    /**
     * Save a browser view
     */
    public function postSaveAction()
    {
        $params = $this->getRequest()->getParams();
        $ret = array();

        // Decode the json structure
        $objData = json_decode($params['raw_body'], true);

        if (!isset($objData['obj_type']))
        {
            return $this->sendOutput(array("error" => "obj_type is a required param"));
        }

        $serviceManager = $this->account->getServiceManager();
        $browserViewService = $serviceManager->get("Netric/Entity/BrowserView/BrowserViewService");

        $view = new BrowserView();
        $view->fromArray($objData);

        $result = $browserViewService->saveView($view);

        return $this->sendOutput($result);
    }

    /**
     * Put a browser view
     */
    public function putSaveAction()
    {
        return $this->postSaveAction();
    }
}