<?php

namespace Database\Seeders;

use App\Modules\Webhooks\src\Models\WebhooksConfiguration;
use App\Modules\Webhooks\src\WebhooksServiceProviderBase;
use Illuminate\Database\Seeder;

class WebhooksTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (empty(env('TEST_SNS_TOPIC_ARN'))) {
            return;
        }

        WebhooksConfiguration::query()->updateOrCreate([], [
            'topic_arn' => env('TEST_SNS_TOPIC_ARN'),
        ]);

        WebhooksServiceProviderBase::enableModule();
    }
}
