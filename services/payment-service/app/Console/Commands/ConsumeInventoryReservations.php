<?php

namespace App\Console\Commands;

use App\Kafka\Consumers\InventoryReservedConsumer;
use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;

class ConsumeInventoryReservations extends Command
{
    protected $signature = 'kafka:consume-reservations';
    protected $description = 'Consume inventory.reserved events from Kafka';

    public function handle()
    {
        $this->info("Starting Inventory Reservations Consumer...");

        $consumer = Kafka::consumer(['inventory.reserved'])
            ->withConsumerGroupId('payment-service-reservations')
            ->withHandler(new InventoryReservedConsumer())
            ->build();

        $consumer->consume();
    }
}
