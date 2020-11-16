<?php

namespace App\Event\PurchaseOrder;

use App\Entity\PurchaseOrder\PurchaseOrder;
use App\Event\AbstractEvent;

class PurchaseOrderUpdatedEvent extends AbstractEvent
{
    public const TYPE = 'purchase_order.updated';

    /** @var PurchaseOrder */
    private $purchaseOrder;

    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }

    public function getPurchaseOrder(): PurchaseOrder
    {
        return $this->purchaseOrder;
    }

    public function getObject()
    {
        return $this->purchaseOrder;
    }
}