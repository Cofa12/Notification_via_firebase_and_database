<?php

namespace Cofa\NotificationViaFirebaseAndDatabase\Contracts;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

abstract class FirebaseNotification implements Notification
{
    protected FirebasePayload $payload;

    public function __construct(FirebasePayload $payload)
    {
        $this->payload = $payload;
    }

    public function sendNotification(array $targets): void
    {
        $credentialsPath = config('firebase-notification.firebase.credentials');

        if (! is_string($credentialsPath) || ! file_exists($credentialsPath)) {
            throw new \RuntimeException(
                'Firebase credentials file not found or not configured.'
            );
        }

        $factory = (new Factory())->withServiceAccount($credentialsPath);
        $messaging = $factory->createMessaging();

        $message = CloudMessage::fromArray(
            $this->payload->getPayload()
        );

        $messaging->sendMulticast($message, $targets);
    }
}
