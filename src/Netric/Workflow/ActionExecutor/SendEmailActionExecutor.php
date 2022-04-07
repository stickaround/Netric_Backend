<?php

declare(strict_types=1);

namespace Netric\Workflow\ActionExecutor;

use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\UserEntity;
use Netric\Mail\SenderService;
use Netric\Entity\ObjType\WorkflowActionEntity;

/**
 * Action to send email messages
 *
 * Params in the 'data' field
 *
 *  template_id string(uuid) OPTIONAL The id of an html_template entity (it's called fid because it used to be a file)
 *  subject     string The subject of the message to send (if not fid has been supplied)
 *  body        string The body to send (if no fid has been supplied)
 *  from        string Email address to be sent from
 *  to          string[] Array of addresses to send to
 *  to_other    string Comma separated list of people to send to - appended to 'to'
 *  cc          string[] Array of addresses to cc
 *  cc_other    string Comma separated list of people to cc - appended to 'cc'
 *  bcc         string[] Array of addresses to bcc
 *  bcc_other   string Comma separated list of people to bcc - appended to 'bcc'
 */
class SendEmailActionExecutor extends AbstractActionExecutor implements ActionExecutorInterface
{
    /**
     * Senders service for sending messages
     *
     * @var SenderService
     */
    private SenderService $senderService;

    /**
     * Constructor
     *
     * @param EntityLoader $entityLoader
     * @param WorkflowActionEntity $actionEntity
     * @param string $appliactionUrl
     */
    public function __construct(
        EntityLoader $entityLoader,
        WorkflowActionEntity $actionEntity,
        string $applicationUrl,
        SenderService $senderService
    ) {
        $this->senderService = $senderService;

        // Should always call the parent constructor for base dependencies
        parent::__construct($entityLoader, $actionEntity, $applicationUrl);
    }

    /**
     * Execute an action on an entity
     *
     * @param EntityInterface $actOnEntity The entity (any type) we are acting on
     * @param UserEntity $user The user who is initiating the action
     * @return bool true on success, false on failure
     */
    public function execute(EntityInterface $actOnEntity, UserEntity $user): bool
    {
        // Get params
        $templateId = $this->getParam('template_id', $actOnEntity);
        $subject = $this->getParam('subject', $actOnEntity);
        $body = $this->getParam('body', $actOnEntity);
        $from = $this->getParam('from', $actOnEntity);
        $to = $this->getParam('to', $actOnEntity);
        $cc = $this->getParam('cc', $actOnEntity);
        $bcc = $this->getParam('bcc', $actOnEntity);
        $bodyIsHtml = false;

        // Check if we are using an html_template id for loading a template
        if (isset($templateId)) {
            $template = $this->getEntityloader()->getEntityById(
                $templateId,
                $this->getActionAccountId()
            );
            $body = $template->getValue("body_html");
            $subject = ($template->getValue("subject")) ? $template->getValue("subject") : $template->getValue("name");

            // Merge any variable with the entity we are executing against
            $body = $this->replaceParamVariables($actOnEntity, $body);
            $subject = $this->replaceParamVariables($actOnEntity, $subject);
            $bodyIsHtml = true;
        }

        if ($this->senderService->send($to, "", $from, "", $subject, $body)) {
            return true;
        }

        // TODO: log failure here
        return false;
    }
}
