<?php

namespace App\Service\Queue;


interface QueuePublisherInterface
{
    public function publish(string $subject, string $data): void;
}