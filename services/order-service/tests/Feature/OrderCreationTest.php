<?php

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

uses(RefreshDatabase::class);

beforeEach(function () {
    Kafka::fake();
});

it('can create an order and publish a kafka event', function () {
    $payload = [
        'customer_id' => 1,
        'items' => [
            ['product_id' => 101, 'quantity' => 2, 'price' => 50.00],
            ['product_id' => 102, 'quantity' => 1, 'price' => 100.00],
        ],
    ];

    $response = $this->postJson('/api/orders', $payload);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['order_id', 'status']
        ]);

    $orderId = $response->json('data.order_id');

    // Verify database
    $this->assertDatabaseHas('orders', [
        'id' => $orderId,
        'customer_id' => 1,
        'total_amount' => 200.00,
        'status' => 'PENDING',
    ]);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $orderId,
        'product_id' => 101,
        'quantity' => 2,
    ]);

    // Verify Kafka event was published
    Kafka::assertPublished();
    
    Kafka::assertPublishedOn('order.created');

    Kafka::assertPublished(null, function (Message $message) use ($orderId) {
        return $message->getBody()['order_id'] === $orderId 
            && (float) $message->getBody()['total_amount'] === 200.00;
    });
});

it('validates required fields during order creation', function () {
    $response = $this->postJson('/api/orders', []);

    $response->assertStatus(422)
        ->assertJson([
            'status' => 'error',
            'message' => 'Validation failed.',
        ])
        ->assertJsonStructure([
            'details' => ['customer_id', 'items']
        ]);
});
