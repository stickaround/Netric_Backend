<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014 Aereus
 */
namespace Netric\Controller;

use Netric\Application\Response\ConsoleResponse;
use Netric\Mvc;
use Netric\Entity\ObjType\UserEntity;
use Netric\Permissions\Dacl;
use Netric\WorkFlow\Action\ActionFactory;
use Netric\WorkFlow\WorkFlow;

/**
 * Controller used for setting up netric - mostly from the command line
 */
class WorkflowController extends Mvc\AbstractController
{
    /**
     * Get array of all workflows for an account
     */
    public function getWorkflowsAction()
    {
        $objType = null;
        if ($this->getRequest()->getParam("obj_type"))
            $objType = $this->getRequest()->getParam("obj_type");

        $serviceManager = $this->account->getServiceManager();
        $workFlowManager = $serviceManager->get("Netric/WorkFlow/WorkFlowManager");
        $workFlows = $workFlowManager->getWorkFlows($objType);

        $workFlowsData = array();
        foreach ($workFlows as $workFlow) {
            $workFlowsData[] = $workFlow->toArray();
        }

        return $this->sendOutput($workFlowsData);
    }

    /**
     * Get a WorkFlow by id
     */
    public function getWorkflowAction()
    {
        $workFlowId = $this->getRequest()->getParam("id");
        if (!$workFlowId)
            return $this->sendOutput(array("error"=>"'id' is a required param"));

        $serviceManager = $this->account->getServiceManager();
        $workFlowManager = $serviceManager->get("Netric/WorkFlow/WorkFlowManager");
        $workFlow = $workFlowManager->getWorkFlowById($workFlowId);
        return $this->sendOutput($workFlow->toArray());
    }

    /**
     * Save a WorkFlow
     */
    public function postSaveAction()
    {
        $rawBody = $this->getRequest()->getBody();
        if (!$rawBody)
            return $this->sendOutput(array("error"=>"No JSON body was sent"));

        // Decode the json structure
        $workFlowData = json_decode($rawBody, true);

        // Load the workflow from the data and save it
        $serviceManager = $this->account->getServiceManager();
        $actionFactory = new ActionFactory($serviceManager);
        $workFlow = new WorkFlow($actionFactory);
        $workFlow->fromArray($workFlowData);
        $workFlowManager = $serviceManager->get("Netric/WorkFlow/WorkFlowManager");
        if (!$workFlowManager->saveWorkFlow($workFlow))
            return $this->sendOutput(array("error"=>$workFlowManager->getLastError()->getMessage()));

        // Return the saved WorkFlow
        return $this->sendOutput($workFlow->toArray());
    }
}
