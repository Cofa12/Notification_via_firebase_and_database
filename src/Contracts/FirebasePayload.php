<?php

namespace Cofa\NotificationViaFirebaseAndDatabase\Contracts;

class FirebasePayload extends Payload
{

    public function setAndroidConfiguration(array $androidConfiguration):void
    {
        $this->payload['android'] = $androidConfiguration;
    }

    public function setIOSConfiguration(array $iosConfiguration): void
    {
        $this->payload['apns'] = $iosConfiguration;
    }

}