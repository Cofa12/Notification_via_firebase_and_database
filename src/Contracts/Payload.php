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

    public function setAndroidConfiguration(array $iosConfiguration):void
    {
        $this->payload['android'] = $iosConfiguration;
    }

    public function setIOSConfiguration(array $iosConfiguration): void
    {
        $this->payload['apns'] = $iosConfiguration;
    }

}