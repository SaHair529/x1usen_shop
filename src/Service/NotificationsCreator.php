<?php

namespace App\Service;


use App\Entity\Notification;
use App\Entity\Order;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

class NotificationsCreator
{
    public function __construct(private NotificationRepository $notificationRep,
                                private DataMapping $dataMapping,
                                private UserRepository $userRep)
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

    /**
     * @throws Exception
     */
    public function createNewCommentNotification(Order $order)
    {
        $notification = new Notification();
        $notification->setAction((new DataMapping())->getKeyByValue('notification_actions', 'new_comment'));
        $notification->setUpdatedOrder($order);
        $notification->setRecipient($order->getCustomer());
        $notification->setCreatedAt(new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow')));

        $this->notificationRep->save($notification, true);
    }

    /**
     * @throws Exception
     */
    public function createNewCommentNotificationForAdmins(Order $order)
    {
        $adminIds = $this->dataMapping->getData('admin_ids');

        foreach ($adminIds as $id) {
            $notification = new Notification();
            $notification->setAction((new DataMapping())->getKeyByValue('notification_actions', 'new_comment'));
            $notification->setUpdatedOrder($order);
            $notification->setRecipient($this->userRep->find($id));
            $notification->setCreatedAt(new DateTimeImmutable('now', new DateTimeZone('Europe/Moscow')));

            $this->notificationRep->save($notification, true);
        }
    }
}