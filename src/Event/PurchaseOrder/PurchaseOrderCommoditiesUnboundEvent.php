<?php

namespace App\Event\PurchaseOrder;

use App\DTO\PurchaseOrder\UnboundCommodityGuids;
use App\Event\AbstractEvent;

class PurchaseOrderCommoditiesUnboundEvent extends AbstractEvent
{
    public const TYPE = 'purchase_order.commodities_unbound';

    /** @var UnboundCommodityGuids */
    private $commodityGuids;

    public function __construct(UnboundCommodityGuids $commodityGuids)
    {
        $this->commodityGuids = $commodityGuids;
    }

    public function getCommodityGuids(): UnboundCommodityGuids
    {
        return $this->commodityGuids;
    }

    public function getObject()
    {
        return $this->commodityGuids;
    }
}