<?php

use App\Kafka\Consumers\PaymentFailedConsumer;
use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Facades\Kafka;

uses(RefreshDatabase::class);

beforeEach(function () {
    Kafka::fake();
    
    // Seed products
    Product::create(['id' => 101, 'name' => 'Smartphone', 'stock' => 10]);
});

it('restores inventory and cancels reservation on payment.failed', function () {
    $orderId = (string) str()->uuid();
    
    // Setup existing reservation and reduced stock
    $product = Product::find(101);
    $product->decrement('stock', 2); // 10 -> 8
    
    Reservation::create([
        'order_id' => $orderId,
        'items' => [['product_id' => 101, 'quantity' => 2]],
        'status' => 'RESERVED'
    ]);

    $payload = [
        'order_id' => $orderId,
        'reason' => 'insufficient_funds'
    ];

    $message = Mockery::mock(KafkaConsumerMessage::class);
    $message->shouldReceive('getBody')->andReturn($payload);
    $message->shouldReceive('getTopicName')->andReturn('payment.failed');

    $consumer = new PaymentFailedConsumer();
    $consumer($message);

    // Verify stock was restored
    expect(Product::find(101)->stock)->toBe(10);

    // Verify reservation status was updated
    $this->assertDatabaseHas('reservations', [
        'order_id' => $orderId,
        'status' => 'CANCELLED',
    ]);
});

it('does nothing if no reservation exists', function () {
    $orderId = (string) str()->uuid();
    $payload = ['order_id' => $orderId];

    $message = Mockery::mock(KafkaConsumerMessage::class);
    $message->shouldReceive('getBody')->andReturn($payload);
    $message->shouldReceive('getTopicName')->andReturn('payment.failed');

    $consumer = new PaymentFailedConsumer();
    $consumer($message);

    // No exceptions thrown, stock remains 10
    expect(Product::find(101)->stock)->toBe(10);
});
