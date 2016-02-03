<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Members;

use Netric\Entity\Notifier\Notifier;
use Netric\Entity\ObjType\ActivityEntity;
use Netric\Entity;

/**
 * Manages the notifications for members
 */
class Members
{

    /**
     * The notifier that will send the notification to members
     *
     * @var Notifier
     */
    private $notifier = null;

    /**
     * Class constructor and dependency setter
     *
     * @param UserEntity $user The current authenticated user
     * @param EntityLoader $entityLoader To create, find, and save entities
     * @param IndexInterface $index An entity index for querying existing notifications
     */
    public function __construct(Notifer $notifier)
    {
        $this->notifier = $notifier;
    }

    public function sendNotifications(Entity $entity) {

        $notificationIds = $this->notifier->send($entity, ActivityEntity::VERB_CREATED);

    }
}
