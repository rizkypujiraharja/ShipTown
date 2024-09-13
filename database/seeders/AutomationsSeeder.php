<?php

namespace Database\Seeders;

use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\ShippingMethodCodeEqualsCondition;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Models\Action;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\Automations\src\Models\Condition;
use Illuminate\Database\Seeder;

class AutomationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => 'Store Pickup',
            'priority' => 1,
        ]);

        Condition::create([
            'automation_id' => $automation->getKey(),
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'paid',
        ]);

        Condition::create([
            'automation_id' => $automation->getKey(),
            'condition_class' => ShippingMethodCodeEqualsCondition::class,
            'condition_value' => 'store_pickup',
        ]);

        Action::create([
            'automation_id' => $automation->getKey(),
            'priority' => 1,
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'store_pickup',
        ]);

        $automation->update(['enabled' => true]);
    }
}
