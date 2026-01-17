<?php

namespace Cofa\NotificationViaFirebaseAndDatabase\Contracts;

abstract class Payload
{
    protected ?array $payload;
    public function __construct()
    {
        $this->payload = [];
    }

    public function setData(array $data)
    {
        $this->payload['data'] = $data;
    }

    public function getData(): array
    {
        return $this->payload;
    }
}