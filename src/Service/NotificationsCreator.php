<?php

namespace App\Service;


use App\Entity\Notification;
use App\Entity\User;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class NotificationsCreator
{
    public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger)
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

        $this->em->persist($notification);
        $this->em->flush();
    }
}