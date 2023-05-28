
use App\Entity\Order;
use App\Service\NotificationsCreator;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;

class OrderEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private NotificationsCreator $notificationsCreator, private LoggerInterface $logger)
    {
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::preUpdate
        ];
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();

        $this->logger->info('статусуки');
        $this->logger->info($args->getOldValue('status'));
        $this->logger->info($args->getNewValue('status'));
        if ($entity instanceof Order) {
            $onlyStatusChanged = count($args->getEntityChangeSet()) === 1 && $args->hasChangedField('status');
            if ($onlyStatusChanged) {
//                $this->notificationsCreator->createChangeStatusNotification($entity->getCustomer()); # todo Разобраться почему бесконечно отрабатывает
            }
        }
    }
}