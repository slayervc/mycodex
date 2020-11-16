<?php

namespace App\Service\Queue;

use NatsStreaming\Connection;

class NatsViaPhpClientPublisher implements QueuePublisherInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(NatsConnectionFactory $natsOptions)
    {
        $this->connection = $natsOptions->getConnectionToPublish();
    }

    public function publish(string $subject, string $data): void
    {
        $this->connection->connect();
        $request = $this->connection->publish($subject, $data);
        $request->wait();
        $this->connection->close();
    }
}