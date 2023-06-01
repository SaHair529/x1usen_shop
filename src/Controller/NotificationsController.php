<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/notification')]
#[IsGranted('ROLE_USER')]
class NotificationsController extends AbstractController
{
    #[Route('/ajax/clear_notifications', name: 'clear_order_notifications')]
    public function clearOrderStatusChangedNotifications(Request $req, OrderRepository $orderRep, NotificationRepository $notificationRep): JsonResponse
    {
        $requestData = json_decode($req->getContent(), true);
        if (!$requestData ||
            !isset($requestData['order_ids_with_notifications']) ||
            empty($requestData['order_ids_with_notifications']))
        {
            return new JsonResponse([
                'message' => 'Invalid or empty request data',
                'request data example' => '{"order_ids_with_notifications":["1","2","3"]}'
            ], Response::HTTP_BAD_REQUEST);
        }
        $orders = $orderRep->findBy(['id' => $requestData['order_ids_with_notifications']]);
        $notificationsToDelete = [];
        foreach ($orders as $order) {
            foreach ($order->getNotifications()->getIterator() as $orderNotification) {
                if ($orderNotification->getAction() === 1) {
                    $notificationsToDelete[] = $orderNotification;
                }
            }
        }
        foreach ($notificationsToDelete as $key => $notification) {
            $notificationRep->remove($notification, $key+1 === count($notificationsToDelete));
        }

        return new JsonResponse([
            'message' => 'ok'
        ]);
    }
}
