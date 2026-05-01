<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $order = DB::transaction(function () use ($validated) {
            $order = Order::create([
                'customer_id' => $validated['customer_id'],
                'total_amount' => collect($validated['items'])->sum(fn($item) => $item['quantity'] * $item['price']),
                'status' => 'PENDING',
            ]);

            foreach ($validated['items'] as $item) {
                $order->items()->create($item);
            }

            return $order;
        });

        // Publish to Kafka
        $message = new Message(
            body: [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'items' => $order->items->map(fn($item) => [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                ])->toArray(),
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at->toISOString(),
            ]
        );

        Kafka::publish()
            ->onTopic('order.created')
            ->withMessage($message)
            ->send();

        return response()->json([
            'message' => 'Order created successfully',
            'order_id' => $order->id,
            'status' => $order->status,
        ], 201);
    }
}
