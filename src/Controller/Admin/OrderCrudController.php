<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\OrderComment;
use App\Entity\User;
use App\Form\UpdateOrderFormType;
use App\Form\WriteOrderCommentFormType;
use App\Repository\OrderCommentRepository;
use App\Repository\OrderRepository;
use App\Service\DataMapping;
use App\Service\NotificationsCreator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use JetBrains\PhpStorm\Pure;

class OrderCrudController extends AbstractCrudController
{
    private array $statuses;

    #[Pure]
    public function __construct(private OrderRepository $orderRep, private NotificationsCreator $notificationsCreator)
    {
        $dataMapping = new DataMapping();
        $this->statuses = array_flip($dataMapping->getData('order_statuses'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $updateOrderAction = Action::new('updateOrder', 'Обновить')
            ->linkToCrudAction('updateOrder');

        return $actions->add(Crud::PAGE_INDEX, $updateOrderAction)
            ->remove(Crud::PAGE_INDEX, Action::EDIT);
    }

    public function updateOrder(AdminContext $context, OrderCommentRepository $commentRep)
    {
        /** @var User $user */
        $user = $this->getUser();

        $updateOrderStatusForm = $this->createForm(UpdateOrderFormType::class, null, [
            'attr' => [
                'placeholder' => $context->getEntity()->getInstance()->getStatus()
            ]
        ]);
        $updateOrderStatusForm->handleRequest($context->getRequest());
        if ($updateOrderStatusForm->isSubmitted()) {
            /** @var Order $order */
            $order = $context->getEntity()->getInstance();
            if ($order->getStatus() !== $updateOrderStatusForm->getData()['order_status']) {
                $order->setStatus($updateOrderStatusForm->getData()['order_status']);
                $this->orderRep->save($order, true);

                $this->notificationsCreator->createChangeStatusNotification($order);
            }
        }

        $comment = new OrderComment();
        $commentForm = $this->createForm(WriteOrderCommentFormType::class, $comment);
        $commentForm->handleRequest($context->getRequest());
        if ($commentForm->isSubmitted()) {
            $order = $this->getContext()->getEntity()->getInstance();
            $comment->setParentOrder($order)
                ->setSender($user);
            $commentRep->save($comment, true);

            $this->notificationsCreator->createNewCommentNotification($order);
        }

        return $this->render('admin/order/update_status.html.twig', [
            'form' => $updateOrderStatusForm,
            'comment_form' => $commentForm,
            'order' => $this->getContext()->getEntity()->getInstance()
        ]);
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield Field::new('id')->hideOnForm();
        yield Field::new('created_at')->setLabel('Дата оформления')->hideOnForm();
        yield Field::new('city')->setLabel('Город')->hideOnForm();
        yield Field::new('address')->setLabel('Адрес')->hideOnForm();
        yield Field::new('phone_number')->setLabel('Номер телефона')->hideOnForm();
        yield ChoiceField::new('status')->setLabel('Статус')->setChoices($this->statuses);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add(ChoiceFilter::new('status')
                ->setChoices($this->statuses)
            );
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
