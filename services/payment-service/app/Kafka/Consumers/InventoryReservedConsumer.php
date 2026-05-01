<?php

namespace App\Kafka\Consumers;

use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

class InventoryReservedConsumer
{
    public function __invoke($message): void
    {
        $body = $message->getBody();
        $orderId = $body['order_id'] ?? null;

        if (!$orderId) {
            Log::warning("Received inventory.reserved without order_id", ['body' => $body]);
            return;
        }

        Log::info("Processing payment for order {$orderId}");

        try {
            // Simulate payment processing
            // In a real app, we might need the amount, but for now we approve
            $payment = Payment::create([
                'order_id' => $orderId,
                'transaction_id' => 'TX-' . strtoupper(bin2hex(random_bytes(4))),
                'amount' => 0, // Should come from a lookup or previous event
                'status' => 'APPROVED'
            ]);

            $this->publishSuccess($orderId, $payment->transaction_id);

        } catch (\Exception $e) {
            Log::error("Error processing payment for order {$orderId}: " . $e->getMessage());
            $this->publishFailure($orderId, "payment_gateway_error");
        }
    }

    private function publishSuccess(string $orderId, string $transactionId): void
    {
        Log::info("Payment approved for order {$orderId}");

        $message = new Message(
            body: [
                'order_id' => $orderId,
                'transaction_id' => $transactionId,
                'status' => 'approved'
            ]
        );

        Kafka::publish()
            ->onTopic('payment.approved')
            ->withMessage($message)
            ->send();
    }

    private function publishFailure(string $orderId, string $reason): void
    {
        Log::warning("Payment failed for order {$orderId}", ['reason' => $reason]);

        $message = new Message(
            body: [
                'order_id' => $orderId,
                'reason' => $reason
            ]
        );

        Kafka::publish()
            ->onTopic('payment.failed')
            ->withMessage($message)
            ->send();
    }
}
