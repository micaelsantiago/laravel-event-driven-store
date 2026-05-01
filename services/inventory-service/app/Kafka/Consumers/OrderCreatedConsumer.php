<?php

namespace App\Kafka\Consumers;

use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Contracts\KafkaConsumerMessage;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

class OrderCreatedConsumer
{
    public function __invoke($message): void
    {
        $body = $message->getBody();
        $orderId = $body['order_id'] ?? null;
        $items = $body['items'] ?? [];

        if (!$orderId || empty($items)) {
            Log::warning("Received order.created without valid data", ['body' => $body]);
            return;
        }

        Log::info("Processing inventory for order {$orderId}");

        $missingItems = [];
        
        try {
            DB::beginTransaction();

            foreach ($items as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];

                $product = Product::where('id', $productId)->lockForUpdate()->first();

                if (!$product || $product->stock < $quantity) {
                    $missingItems[] = $productId;
                }
            }

            if (!empty($missingItems)) {
                DB::rollBack();
                $this->publishFailure($orderId, $missingItems);
                return;
            }

            // All items available, update stock
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                $product->decrement('stock', $item['quantity']);
            }

            $reservation = Reservation::create([
                'order_id' => $orderId,
                'status' => 'RESERVED'
            ]);

            DB::commit();
            $this->publishSuccess($orderId, $reservation->id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing inventory for order {$orderId}: " . $e->getMessage());
        }
    }

    private function publishSuccess(string $orderId, string $reservationId): void
    {
        Log::info("Inventory reserved for order {$orderId}");

        $message = new Message(
            body: [
                'order_id' => $orderId,
                'reservation_id' => $reservationId,
                'status' => 'reserved'
            ]
        );

        Kafka::publish()
            ->onTopic('inventory.reserved')
            ->withMessage($message)
            ->send();
    }

    private function publishFailure(string $orderId, array $missingItems): void
    {
        Log::warning("Inventory failed for order {$orderId}", ['missing_items' => $missingItems]);

        $message = new Message(
            body: [
                'order_id' => $orderId,
                'reason' => 'out_of_stock',
                'missing_items' => $missingItems
            ]
        );

        Kafka::publish()
            ->onTopic('inventory.failed')
            ->withMessage($message)
            ->send();
    }
}
