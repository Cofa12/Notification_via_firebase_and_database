<?php

namespace Tests\Unit\Contracts;

use Cofa\NotificationViaFirebaseAndDatabase\Contracts\FirebasePayload;
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\Payload;
use PHPUnit\Framework\TestCase;

class FirebasePayloadTest extends TestCase
{
    private FirebasePayload $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payload = new FirebasePayload();
    }

    public function test_extends_payload_class(): void
    {
        $this->assertInstanceOf(Payload::class, $this->payload);
    }

    public function test_can_set_and_get_data(): void
    {
        $data = [
            'notification' => [
                'title' => 'Test Notification',
                'body' => 'This is a test message'
            ],
            'data' => [
                'key' => 'value'
            ]
        ];

        $this->payload->setData($data);

        $result = $this->payload->getPayload();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
    }
}
