<?php

namespace App\Service\Exchange;

use App\Entity\PurchaseOrder\PurchaseOrder;
use App\Entity\SupplierTrip\SupplierTripSchedule;
use App\Entity\SupplierTrip\SupplierTripScheduleSetting;
use App\Event\CloudEvent;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CloudEventGenerator
{
    private const DEFAULT_SERIALIZE_GROUPS = ['cloud_event'];

    //Here are listed routes of API endpoints which return entity by its GUID. It's used to generate a "source"
    //field value in CloudEvent
    private const ENTITY_ROUTES_MAP = [
        PurchaseOrder::class               => 'purchase_order-get',
        SupplierTripSchedule::class        => 'supplier_trip_schedule-get',
        SupplierTripScheduleSetting::class => 'supplier_trip_schedule_setting-get',
    ];

    /** @var SerializerInterface */
    private $serializer;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(SerializerInterface $serializer, UrlGeneratorInterface $router, LoggerInterface $logger)
    {
        $this->serializer = $serializer;
        $this->router = $router;
        $this->logger = $logger;
    }

    public function generateFromObject(
        $object,
        $type,
        array $serializeGroups = self::DEFAULT_SERIALIZE_GROUPS
    ): CloudEvent {
        $event = CloudEvent::create(
            Uuid::uuid4()->toString(),
            $this->generateSource($object),
            $type
        );

        $event->setData($this->serializer->serialize(
            $object,
            'json',
            ['groups' => $serializeGroups]
        ));

        return $event;
    }

    private function generateSource($object): string
    {
        if (array_key_exists(get_class($object), self::ENTITY_ROUTES_MAP)) {
            try {
                return $this->router->generate(
                    self::ENTITY_ROUTES_MAP[get_class($object)],
                    ['guid' => $object->getGuid()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
            } catch (\Throwable $e) {
                $this->logger->error('Не удалось сгенерировать роут', [
                    'e'     => $e,
                    'route' => self::ENTITY_ROUTES_MAP[get_class($object)]
                ]);
            }
        }

        return $this->router->generate('sonata_admin_redirect', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}