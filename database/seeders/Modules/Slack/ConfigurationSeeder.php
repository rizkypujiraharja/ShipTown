<?php

namespace Database\Seeders\Modules\Slack;

use App\Models\OrderStatus;
use App\Modules\Automations\src\Actions\Order\AttachTagsAction;
use App\Modules\Automations\src\Conditions\Order\DoesntHaveTagsCondition;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use App\Modules\Slack\src\Automations\SendSlackNotificationAction;
use App\Modules\Slack\src\Models\SlackConfig;
use Illuminate\Database\Seeder;

class ConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        if (env('TEST_MODULES_SLACK_INCOMING_WEBHOOK_URL')) {
            SlackConfig::query()->firstOrCreate([
                'incoming_webhook_url' => env('TEST_MODULES_SLACK_INCOMING_WEBHOOK_URL'),
            ]);

            OrderStatus::query()->firstOrCreate([
                'name' => 'paid',
                'code' => 'paid',
            ]);

            OrderStatus::query()->firstOrCreate([
                'name' => 'store_collection',
                'code' => 'store_collection',
            ]);

            /** @var Automation $automation */
            $automation = Automation::query()->create([
                'name' => 'Send Slack message when order is ready for collection',
                'enabled' => true,
            ]);

            Condition::query()->create([
                'automation_id' => $automation->id,
                'condition_class' => StatusCodeEqualsCondition::class,
                'condition_value' => 'store_collection',
            ]);

            Condition::query()->create([
                'automation_id' => $automation->id,
                'condition_class' => DoesntHaveTagsCondition::class,
                'condition_value' => 'store_notification_sent',
            ]);

            Action::query()->create([
                'automation_id' => $automation->id,
                'action_class' => SendSlackNotificationAction::class,
                'action_value' => 'Store Collection order has been placed, please prepare the order for collection',
            ]);

            Action::query()->create([
                'automation_id' => $automation->id,
                'action_class' => AttachTagsAction::class,
                'action_value' => 'store_notification_sent',
            ]);
        }
    }
}
