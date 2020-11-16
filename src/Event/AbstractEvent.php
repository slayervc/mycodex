<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractEvent extends Event
{
    private const SERVICE_NAME = 'alpha';
    public const TYPE = '';

    /**
     * This method must return an object (entity, DTO, etc.), nested to the event
     *
     * @return object
     */
    abstract public function getObject();

    public function getType(): string
    {
        return self::SERVICE_NAME . '.' . static::TYPE;
    }
}