<?php

namespace Tests;

use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebasePayload;
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\DatabasePayload;
use Orchestra\Testbench\TestCase;

class PayloadTest extends TestCase
{
    public function test_firebase_payload_stores_data()
    {
        $payload = new FirebasePayload();
        $data = ['title' => 'Test', 'body' => 'Body'];
        $payload->setData($data);
        
        $this->assertEquals(['data' => $data], $payload->getPayload());
    }

    public function test_firebase_payload_stores_android_config()
    {
        $payload = new FirebasePayload();
        $config = ['priority' => 'high'];
        $payload->setAndroidConfiguration($config);
        
        $this->assertEquals(['android' => $config], $payload->getPayload());
    }

    public function test_firebase_payload_stores_ios_config()
    {
        $payload = new FirebasePayload();
        $config = ['badge' => 1];
        $payload->setIOSConfiguration($config);
        
        $this->assertEquals(['apns' => $config], $payload->getPayload());
    }

    public function test_database_payload_stores_data()
    {
        $payload = new DatabasePayload();
        $data = ['key' => 'value'];
        $payload->setData($data);
        
        $this->assertEquals(['data' => $data], $payload->getPayload());
    }
}
