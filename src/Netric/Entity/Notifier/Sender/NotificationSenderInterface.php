<?php

declare(strict_types=1);

namespace Netric\Entity\Notifier\Sender;

use Netric\Entity\ObjType\NotificationEntity;
use Netric\Entity\ObjType\UserEntity;

interface NotificationSenderInterface
{
    /**
     * Interface used to send notifications to a user for a given notification
     *
     * @param NotificationEntity $notification
     * @param UserEntity $user
     * @return bool True if a notice was sent, otherwise false (not necessarily an error)
     */
    public function sendNotification(NotificationEntity $notification, UserEntity $user): bool;
}
