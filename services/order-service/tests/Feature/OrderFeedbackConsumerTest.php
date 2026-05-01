<?php

use App\Kafka\Consumers\OrderFeedbackConsumer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Junges\Kafka\Message\ConsumedMessage;
use Junges\Kafka\Contracts\KafkaConsumerMessage;

uses(RefreshDatabase::class);

it('updates order status to COMPLETED when payment is approved', function () {
    $order = Order::create([
        'customer_id' => 1,
        'total_amount' => 100.00,
        'status' => 'PENDING',
    ]);

    $consumer = new OrderFeedbackConsumer();
    
    // Mocking the ConsumedMessage
    $message = Mockery::mock(KafkaConsumerMessage::class);
    $message->shouldReceive('getBody')->andReturn([
        'order_id' => $order->id,
        'status' => 'approved'
    ]);
    $message->shouldReceive('getTopicName')->andReturn('payment.approved');

    $consumer($message);

    expect($order->fresh()->status)->toBe('COMPLETED');
});

it('updates order status to CANCELLED when inventory fails', function () {
    $order = Order::create([
        'customer_id' => 1,
        'total_amount' => 100.00,
        'status' => 'PENDING',
    ]);

    $consumer = new OrderFeedbackConsumer();
    
    $message = Mockery::mock(KafkaConsumerMessage::class);
    $message->shouldReceive('getBody')->andReturn([
        'order_id' => $order->id,
        'reason' => 'out_of_stock'
    ]);
    $message->shouldReceive('getTopicName')->andReturn('inventory.failed');

    $consumer($message);

    expect($order->fresh()->status)->toBe('CANCELLED');
});

it('updates order status to PAYMENT_FAILED when payment fails', function () {
    $order = Order::create([
        'customer_id' => 1,
        'total_amount' => 100.00,
        'status' => 'PENDING',
    ]);

    $consumer = new OrderFeedbackConsumer();
    
    $message = Mockery::mock(KafkaConsumerMessage::class);
    $message->shouldReceive('getBody')->andReturn([
        'order_id' => $order->id,
        'reason' => 'insufficient_funds'
    ]);
    $message->shouldReceive('getTopicName')->andReturn('payment.failed');

    $consumer($message);

    expect($order->fresh()->status)->toBe('PAYMENT_FAILED');
});
