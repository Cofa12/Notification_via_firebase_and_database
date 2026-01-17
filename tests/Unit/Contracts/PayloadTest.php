<?php

namespace Tests\Unit\Contracts;

use Cofa\NotificationViaFirebaseAndDatabase\Contracts\Payload;
use PHPUnit\Framework\TestCase;

class PayloadTest extends TestCase
{
    private Payload $payload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payload = new class extends Payload {};
    }

    public function test_constructor_initializes_payload_as_empty_array(): void
    {
        $result = $this->payload->getData();
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_set_data_stores_data_in_payload_array(): void
    {
        $data = ['message' => 'test', 'title' => 'Test Title'];

        $this->payload->setData($data);

        $result = $this->payload->getData();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals($data, $result['data']);
    }

    public function test_set_data_overwrites_previous_data(): void
    {
        $firstData = ['message' => 'first'];
        $secondData = ['message' => 'second'];

        $this->payload->setData($firstData);
        $this->payload->setData($secondData);

        $result = $this->payload->getData();
        $this->assertEquals($secondData, $result['data']);
    }

    public function test_set_data_with_empty_array(): void
    {
        $this->payload->setData([]);

        $result = $this->payload->getData();
        $this->assertArrayHasKey('data', $result);
        $this->assertEmpty($result['data']);
    }
}
