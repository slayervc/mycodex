# Code example for the Docler team 

This example is a small fragment of the Symfony 4 project of procurement service.

## Solved task description

The task is to integrate the application with a message queueing server. At the moment when the task was assigned,
[NATS Streaming](https://docs.nats.io/nats-streaming-concepts/intro) was being used in the company as a standard.
But due to a big amount of complaints from many development teams there's a high probability of switching to RabbitMQ
or Kafka.

[CloudEvents](https://github.com/cloudevents/spec) is also used in the company as an internal standard for events
transfering.

## Solution description

### MQ Server connection

`App\Service\Queue\QueuePublisherInterface` - starting point of the solution - publisher interface which solves the
only task: publishing to a queue an event which contains some `data` and has some `subject`.

`App\Service\Queue\NatsViaPhpClientPublisher` - specific publisher implementation. There is 99% probability that it
will be replaced in the feature. Based on the only available
[community PHP library](https://github.com/byrnedo/php-nats-streaming) which has strongly coupled code. 

`App\Service\Queue\NatsConnectionFactory` - the class which encapsulates work with
[community NATS library](https://github.com/byrnedo/php-nats-streaming) and provides a convenient interface to
configure connection to NATS using Symfony DIC.

### Events implementation

`App\Event\AbstractEvent` - the class which describes common principles of work with events within the project:
an event must include an object (entity or DTO) and belong to a particular type which is named using
`service.entity.event` pattern. Since the event-driven approach is used in this project only for work with MQ server
and not used for other needs (for example, business logic implementation), this class is named just `AbstractEvent`
instead of for example `MessageBrokerAbstractEvent`.

`App\Event\PurchaseOrder\` - contains `AbstractEvent` implementations for the `PurchaseOrder` entity.

`App\Event\CloudEvent` - [CloudEvents](https://github.com/cloudevents/spec) implementation.

### Event publishing

`App\EventSubscriber\CloudEventQueuePublishSubscriber` - Symfony subscriber which wraps a project event instance with
CloudEvent implementation instance and publishes it via MQ broker.

`App\EventSubscriber\DoctrineToSymfonyEventTranslateSubscriber` - Doctrine events subscriber, which automatically
publishes `App\Event\AbstractEvent` for some entities. It causes automatic publishing events via MQ broker when
some entities are changed in DB.

## Usage example

### Automatic events publishing when entities are created, changed or deleted

Add elements to array `App\EventSubscriber\DoctrineToSymfonyEventTranslateSubscriber::EVENT_MAP`, for example:

```php
private const EVENT_MAP = [
    SupplierTripSchedule::class => [
        'update' => SupplierTripScheduleUpdatedEvent::class,
        'create' => SupplierTripScheduleCreatedEvent::class,
        'delete' => SupplierTripScheduleDeletedEvent::class,
    ]
];
```

### Manual event publishing

1. inject `Symfony\Component\EventDispatcher\EventDispatcherInterface`
2. instance and publish an event (the event class must extend `App\Event\AbstractEvent`):

```php
class PurchaseOrderCommodityService
{
    public function __construct(EventDispatcherInterface $eventDispatcher) {
        //...
        $this->eventDispatcher = $eventDispatcher;
    }
    
    public function changeQuantity(PurchaseOrderCommodity $commodity, int $quantity): void
    {
        $commodity->setQuantity($quantity);
        //...
        $this->eventDispatcher->dispatch(new PurchaseOrderCommodityUpdatedEvent($commodity));
    }
}
```