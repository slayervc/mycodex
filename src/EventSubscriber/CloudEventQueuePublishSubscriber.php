<?php

namespace App\EventSubscriber;

use App\Event\AbstractEvent;
use App\Event\PurchaseOrder\PurchaseOrderCommoditiesUnboundEvent;
use App\Event\PurchaseOrder\PurchaseOrderCreatedEvent;
use App\Event\PurchaseOrder\PurchaseOrderUpdatedEvent;
use App\Event\PurchaseOrderCommodity\PurchaseOrderCommodityUpdatedEvent;
use App\Event\SupplierTripSchedule\SupplierTripScheduleCreatedEvent;
use App\Event\SupplierTripSchedule\SupplierTripScheduleDeletedEvent;
use App\Event\SupplierTripScheduleSetting\SupplierTripScheduleSettingCreatedEvent;
use App\Event\SupplierTripScheduleSetting\SupplierTripScheduleSettingUpdatedEvent;
use App\Event\SupplierTripSchedule\SupplierTripScheduleUpdatedEvent;
use App\Service\Exchange\CloudEventGenerator;
use App\Service\Queue\QueuePublisherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * When events (must extend App\Event\AbstractEvent) which listed in getSubscribedEvents are triggered, this class
 * extracts an entity from the event, wraps it with CloudEvent and publish it via MQ broker.
 */
class CloudEventQueuePublishSubscriber implements EventSubscriberInterface
{
    /** @var QueuePublisherInterface */
    private $publisher;

    /** @var CloudEventGenerator */
    private $cloudEventGenerator;

    public function __construct(
        QueuePublisherInterface $publisher,
        CloudEventGenerator $cloudEventGenerator
    ) {
        $this->publisher = $publisher;
        $this->cloudEventGenerator = $cloudEventGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            PurchaseOrderUpdatedEvent::class               => [
                ['onAbstractEvent', 0]
            ],
            PurchaseOrderCreatedEvent::class               => [
                ['onAbstractEvent', 0]
            ],
            SupplierTripScheduleUpdatedEvent::class        => [
                ['onAbstractEvent', 0]
            ],
            SupplierTripScheduleCreatedEvent::class        => [
                ['onAbstractEvent', 0]
            ],
            SupplierTripScheduleDeletedEvent::class        => [
                ['onAbstractEvent', 0]
            ],
            SupplierTripScheduleSettingUpdatedEvent::class => [
                ['onAbstractEvent', 0]
            ],
            SupplierTripScheduleSettingCreatedEvent::class => [
                ['onAbstractEvent', 0]
            ],
            PurchaseOrderCommoditiesUnboundEvent::class    => [
                ['onAbstractEvent', 0]
            ],
            PurchaseOrderCommodityUpdatedEvent::class      => [
                ['onAbstractEvent', 0]
            ],
        ];
    }

    public function onAbstractEvent(AbstractEvent $event): void
    {
        $cloudEvent = $this->cloudEventGenerator->generateFromObject($event->getObject(), $event->getType());
        $serializedEvent = json_encode($cloudEvent->toArray());
        $this->publisher->publish($cloudEvent->getType(), $serializedEvent);
    }
}