<?php

use App\Kafka\Consumers\InventoryReservedConsumer;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

uses(RefreshDatabase::class);

beforeEach(function () {
    Kafka::fake();
});

it('processes payment and publishes payment.approved on success', function () {
    $orderId = (string) str()->uuid();
    $payload = [
        'order_id' => $orderId,
        'reservation_id' => (string) str()->uuid(),
        'status' => 'reserved'
    ];

    $message = Mockery::mock(KafkaConsumerMessage::class);
    $message->shouldReceive('getBody')->andReturn($payload);
    $message->shouldReceive('getTopicName')->andReturn('inventory.reserved');

    $consumer = new InventoryReservedConsumer();
    $consumer($message);

    // Verify payment record
    $this->assertDatabaseHas('payments', [
        'order_id' => $orderId,
        'status' => 'APPROVED',
    ]);

    // Verify Kafka event
    Kafka::assertPublishedOn('payment.approved', null, function (Message $message) use ($orderId) {
        return $message->getBody()['order_id'] === $orderId 
            && $message->getBody()['status'] === 'approved';
    });
});
