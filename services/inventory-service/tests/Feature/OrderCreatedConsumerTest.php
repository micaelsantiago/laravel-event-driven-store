<?php

use App\Kafka\Consumers\OrderCreatedConsumer;
use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

uses(RefreshDatabase::class);

beforeEach(function () {
    Kafka::fake();
    
    // Seed products
    Product::create(['id' => 101, 'name' => 'Smartphone', 'stock' => 10]);
    Product::create(['id' => 102, 'name' => 'Laptop', 'stock' => 5]);
});

it('reserves inventory and publishes inventory.reserved on success', function () {
    $orderId = (string) str()->uuid();
    $payload = [
        'order_id' => $orderId,
        'items' => [
            ['product_id' => 101, 'quantity' => 2],
            ['product_id' => 102, 'quantity' => 1],
        ],
    ];

    $message = Mockery::mock(KafkaConsumerMessage::class);
    $message->shouldReceive('getBody')->andReturn($payload);
    $message->shouldReceive('getTopicName')->andReturn('order.created');

    $consumer = new OrderCreatedConsumer();
    $consumer($message);

    // Verify stock was decremented
    expect(Product::find(101)->stock)->toBe(8);
    expect(Product::find(102)->stock)->toBe(4);

    // Verify reservation was created
    $this->assertDatabaseHas('reservations', [
        'order_id' => $orderId,
        'status' => 'RESERVED',
        'items' => json_encode($payload['items']),
    ]);

    // Verify Kafka event
    Kafka::assertPublishedOn('inventory.reserved', null, function (Message $message) use ($orderId) {
        return $message->getBody()['order_id'] === $orderId 
            && $message->getBody()['status'] === 'reserved';
    });
});

it('publishes inventory.failed when stock is insufficient', function () {
    $orderId = (string) str()->uuid();
    $payload = [
        'order_id' => $orderId,
        'items' => [
            ['product_id' => 101, 'quantity' => 20], // More than available (10)
        ],
    ];

    $message = Mockery::mock(KafkaConsumerMessage::class);
    $message->shouldReceive('getBody')->andReturn($payload);
    $message->shouldReceive('getTopicName')->andReturn('order.created');

    $consumer = new OrderCreatedConsumer();
    $consumer($message);

    // Verify stock was NOT decremented
    expect(Product::find(101)->stock)->toBe(10);

    // Verify NO reservation was created
    $this->assertDatabaseMissing('reservations', [
        'order_id' => $orderId,
    ]);

    // Verify Kafka event
    Kafka::assertPublishedOn('inventory.failed', null, function (Message $message) use ($orderId) {
        return $message->getBody()['order_id'] === $orderId 
            && $message->getBody()['reason'] === 'out_of_stock'
            && in_array(101, $message->getBody()['missing_items']);
    });
});

it('publishes inventory.failed when product does not exist', function () {
    $orderId = (string) str()->uuid();
    $payload = [
        'order_id' => $orderId,
        'items' => [
            ['product_id' => 999, 'quantity' => 1], // Non-existent
        ],
    ];

    $message = Mockery::mock(KafkaConsumerMessage::class);
    $message->shouldReceive('getBody')->andReturn($payload);
    $message->shouldReceive('getTopicName')->andReturn('order.created');

    $consumer = new OrderCreatedConsumer();
    $consumer($message);

    // Verify Kafka event
    Kafka::assertPublishedOn('inventory.failed', null, function (Message $message) use ($orderId) {
        return $message->getBody()['order_id'] === $orderId 
            && in_array(999, $message->getBody()['missing_items']);
    });
});
