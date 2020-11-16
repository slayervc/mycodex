<?php

namespace App\EventSubscriber;

use App\Entity\PurchaseOrder\PurchaseOrder;
use App\Entity\PurchaseOrder\PurchaseOrderCommodity;
use App\Entity\SupplierTrip\SupplierTripSchedule;
use App\Entity\SupplierTrip\SupplierTripScheduleSetting;
use App\Event\PurchaseOrder\PurchaseOrderCreatedEvent;
use App\Event\PurchaseOrder\PurchaseOrderUpdatedEvent;
use App\Event\SupplierTripSchedule\SupplierTripScheduleCreatedEvent;
use App\Event\SupplierTripSchedule\SupplierTripScheduleDeletedEvent;
use App\Event\SupplierTripScheduleSetting\SupplierTripScheduleSettingCreatedEvent;
use App\Event\SupplierTripScheduleSetting\SupplierTripScheduleSettingUpdatedEvent;
use App\Event\SupplierTripSchedule\SupplierTripScheduleUpdatedEvent;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This Doctrine event subscriber is called for all entities, when events listed in getSubscribedEvents are triggered.
 * Its purpose is automatically generate our Symfony events.
 */
class DoctrineToSymfonyEventTranslateSubscriber implements EventSubscriber
{
    private const EVENT_MAP = [
        PurchaseOrder::class        => [
            'update' => PurchaseOrderUpdatedEvent::class,
            'create' => PurchaseOrderCreatedEvent::class,
        ],
        SupplierTripSchedule::class => [
            'update' => SupplierTripScheduleUpdatedEvent::class,
            'create' => SupplierTripScheduleCreatedEvent::class,
            'delete' => SupplierTripScheduleDeletedEvent::class,
        ],
        SupplierTripScheduleSetting::class => [
            'update' => SupplierTripScheduleSettingUpdatedEvent::class,
            'create' => SupplierTripScheduleSettingCreatedEvent::class,
        ],
    ];

    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        if (array_key_exists(PurchaseOrderCommodity::class, self::EVENT_MAP)) {
            throw new \LogicException(sprintf(
                'You described for %1$s changes in %2$s. Such subscription may cause publishing of thousands internal'
                . 'application events, which will be transferred via MQ server. %1$s changing events may be generated'
                . 'only manually!',
                PurchaseOrderCommodity::class,
                self::class
            ));
        }

        $this->dispatcher = $dispatcher;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->translateEvent('create', $args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->translateEvent('update', $args);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->translateEvent('delete', $args);
    }

    private function translateEvent(string $action, LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $entityClass = get_class($entity);
        if (!array_key_exists($entityClass, self::EVENT_MAP) || !array_key_exists($action, self::EVENT_MAP[$entityClass])) {
            return;
        }

        try {
            $eventClass = self::EVENT_MAP[$entityClass][$action];
            $event = new $eventClass($entity);
            $this->dispatcher->dispatch($event);
        } catch (\Throwable $e) {
            return;
        }
    }
}