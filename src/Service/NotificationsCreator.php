<?php

namespace App\Service;


use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class NotificationsCreator
{
    public function __construct(private NotificationRepository $notificationRep)
    {
    }

    /**
     * @throws Exception
     */
    public function createChangeStatusNotification(User $recipient)
    {
        $notification = new Notification();
        $notification->setAction((new DataMapping())->getKeyByValue('notification_actions', 'order_status_changed'));
        $notification->setRecipient($recipient);
        $notification->setCreatedAt(new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow')));

        $this->notificationRep->save($notification, true);
    }
}