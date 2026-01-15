<?php

namespace Tests\Unit\Contracts;

use Cofa\NotificationViaFirebaseAndDatabase\Contracts\DatabasePayload;
use Cofa\NotificationViaFirebaseAndDatabase\Contracts\Payload;
use PHPUnit\Framework\TestCase;

class DatabasePayloadTest extends TestCase
{
    private DatabasePayload $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payload = new DatabasePayload();
    }

    public function test_extends_payload_class(): void
    {
        $this->assertInstanceOf(Payload::class, $this->payload);
    }

    public function test_can_set_and_get_data(): void
    {
        $data = [
            'type' => 'message',
            'content' => 'Database notification content',
            'user_id' => 123
        ];

        $this->payload->setData($data);

        $result = $this->payload->getData();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
    }
}
