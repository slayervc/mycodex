<?php

namespace App\Service\Queue;

use Nats\ConnectionOptions as NatsConnectionOptions;
use NatsStreaming\Connection;
use NatsStreaming\ConnectionOptions as StreamingConnectionOptions;
use NatsStreaming\SubscriptionOptions;

class NatsConnectionFactory
{
    private $natsOptions;
    private $streamingOptions;
    private $subscriptionOptions;
    private $publishClientId;
    private $subscribeClientIdPrefix;

    public function __construct(
        array $natsOptions,
        array $streamingOptions,
        array $subscriptionOptions,
        string $publishClientId,
        string $subscribeClientIdPrefix
    ) {
        $this->natsOptions = $natsOptions;
        $this->streamingOptions = $streamingOptions;
        $this->subscriptionOptions = $subscriptionOptions;
        $this->publishClientId = $publishClientId;
        $this->subscribeClientIdPrefix = $subscribeClientIdPrefix;
    }

    public function getConnection(?string $clientId = null): Connection
    {
        $natsOptions = new NatsConnectionOptions($this->natsOptions);
        $streamingOptions = new StreamingConnectionOptions(array_merge(
            $this->streamingOptions,
            ['natsOptions' => $natsOptions]
        ));

        if ($clientId) {
            $streamingOptions->setClientID($clientId);
        }

        return new Connection($streamingOptions);
    }

    public function getConnectionToPublish(): Connection
    {
        return $this->getConnection($this->publishClientId);
    }

    public function getConnectionToSubscribe(string $messageType): Connection
    {
        return $this->getConnection($this->getSubscribeClientId($messageType));
    }

    public function getSubscribeClientId(string $messageType): string
    {
        $hash = '';
        for ($i = 1; $i <= 5; $i++) {
            $hash .= chr(rand(97, 122));
        }

        return $this->subscribeClientIdPrefix . '-' . str_replace('.', '_', $messageType) . '-' . $hash;
    }

    public function getSubscriptionOptions(): SubscriptionOptions
    {
        return new SubscriptionOptions($this->subscriptionOptions);
    }
}