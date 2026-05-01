<?php

namespace App\Console\Commands;

use App\Kafka\Consumers\OrderCreatedConsumer;
use App\Kafka\Consumers\PaymentFailedConsumer;
use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;

class ConsumeOrderCreated extends Command
{
    protected $signature = 'kafka:consume-orders';
    protected $description = 'Consume order and payment events from Kafka';

    public function handle()
    {
        $this->info("Starting Inventory Consumers...");

        $consumer = Kafka::consumer(['order.created', 'payment.failed'])
            ->withConsumerGroupId('inventory-service-orders')
            ->withHandler(function ($message) {
                $topic = $message->getTopicName();
                
                if ($topic === 'order.created') {
                    return (new OrderCreatedConsumer())($message);
                }
                
                if ($topic === 'payment.failed') {
                    return (new PaymentFailedConsumer())($message);
                }
            })
            ->withOption('auto.offset.reset', 'earliest')
            ->build();

        $consumer->consume();
    }
}
