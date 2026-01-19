<?php

namespace Cofa\NotificationViaFirebaseAndDatabase\Contracts;

abstract class Payload
{
    protected ?array $payload;
    public function __construct()
    {
        $this->payload = [];
    }

    public function setData(array $data):void
    {
        $this->payload['data'] = $data;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

}