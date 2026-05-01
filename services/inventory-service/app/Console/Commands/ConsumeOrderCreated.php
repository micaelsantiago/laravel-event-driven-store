<?php

namespace App\Console\Commands;

use App\Kafka\Consumers\OrderCreatedConsumer;
use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;

class ConsumeOrderCreated extends Command
{
    protected $signature = 'kafka:consume-orders';
    protected $description = 'Consume order.created events from Kafka';

    public function handle()
    {
        $this->info("Starting Order Created Consumer...");

        $consumer = Kafka::consumer(['order.created'])
            ->withConsumerGroupId('inventory-service-orders')
            ->withHandler(new OrderCreatedConsumer())
            ->build();

        $consumer->consume();
    }
}
