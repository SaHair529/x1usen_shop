<?php

namespace App\Service;


use App\Entity\Notification;
use App\Entity\Order;
use App\Repository\NotificationRepository;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

class NotificationsCreator
{
    public function __construct(private NotificationRepository $notificationRep)
    {
    }

    /**
     * @throws Exception
     */
    public function createChangeStatusNotification(Order $order)
    {
        $notification = new Notification();
        $notification->setAction((new DataMapping())->getKeyByValue('notification_actions', 'order_status_changed'));
        $notification->setUpdatedOrder($order);
        $notification->setRecipient($order->getCustomer());
        $notification->setCreatedAt(new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow')));

        $this->notificationRep->save($notification, true);
    }
}