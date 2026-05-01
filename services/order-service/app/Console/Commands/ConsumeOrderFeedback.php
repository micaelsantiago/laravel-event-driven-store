<?php

namespace App\Console\Commands;

use App\Kafka\Consumers\OrderFeedbackConsumer;
use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;

class ConsumeOrderFeedback extends Command
{
    protected $signature = 'kafka:consume-feedback';
    protected $description = 'Consume order feedback events from Kafka (inventory and payment)';

    public function handle()
    {
        $this->info("Starting Order Feedback Consumer...");

        $consumer = Kafka::consumer(['inventory.failed', 'payment.approved', 'payment.failed'])
            ->withConsumerGroupId('order-service-feedback')
            ->withHandler(new OrderFeedbackConsumer())
            ->build();

        $consumer->consume();
    }
}
