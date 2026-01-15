<?php

namespace Cofa\NotificationViaFirebaseAndDatabase\Contracts;

interface Notification
{
    public function sendNotification(array $targets): void;
}