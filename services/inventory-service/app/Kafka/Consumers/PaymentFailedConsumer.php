<?php

namespace App\Kafka\Consumers;

use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentFailedConsumer
{
    public function __invoke($message): void
    {
        $body = $message->getBody();
        $orderId = $body['order_id'] ?? null;

        if (!$orderId) {
            Log::warning("Received payment.failed without order_id", ['body' => $body]);
            return;
        }

        Log::info("Rollback: Processing payment failure for order {$orderId}");

        $reservation = Reservation::where('order_id', $orderId)
            ->where('status', 'RESERVED')
            ->first();

        if (!$reservation) {
            Log::warning("No active reservation found for order {$orderId} to rollback");
            return;
        }

        try {
            DB::beginTransaction();

            $items = $reservation->items ?? [];

            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->increment('stock', $item['quantity']);
                    Log::info("Restored {$item['quantity']} units of product {$item['product_id']} for order {$orderId}");
                }
            }

            $reservation->update(['status' => 'CANCELLED']);

            DB::commit();
            Log::info("Rollback: Inventory restoration completed for order {$orderId}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Rollback: Error restoring inventory for order {$orderId}: " . $e->getMessage());
        }
    }
}
