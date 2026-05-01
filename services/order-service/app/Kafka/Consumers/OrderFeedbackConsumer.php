<?php

namespace App\Kafka\Consumers;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Contracts\KafkaConsumerMessage;

class OrderFeedbackConsumer
{
    public function __invoke($message): void
    {
        $body = $message->getBody();
        $topic = $message->getTopicName();
        $orderId = $body['order_id'] ?? null;

        if (!$orderId) {
            Log::warning("Received event on topic {$topic} without order_id", ['body' => $body]);
            return;
        }

        $order = Order::find($orderId);

        if (!$order) {
            Log::warning("Received event on topic {$topic} for non-existent order: {$orderId}");
            return;
        }

        Log::info("Processing event {$topic} for order {$orderId}");

        switch ($topic) {
            case 'inventory.failed':
                $order->update(['status' => 'CANCELLED']);
                Log::info("Order {$orderId} marked as CANCELLED (Inventory Failed)");
                break;

            case 'payment.approved':
                $order->update(['status' => 'COMPLETED']);
                Log::info("Order {$orderId} marked as COMPLETED (Payment Approved)");
                break;

            case 'payment.failed':
                $order->update(['status' => 'PAYMENT_FAILED']);
                Log::info("Order {$orderId} marked as PAYMENT_FAILED (Payment Failed)");
                break;
            
            default:
                Log::warning("No handler for topic: {$topic}");
        }
    }
}
