<?php

namespace Database\Seeders;

use App\Modules\DpdIreland\src\Models\DpdIreland;
use Illuminate\Database\Seeder;

class DpdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DpdIreland::query()->create([
            'live' => false,
            'user' => 'someuser',
            'password' => 'somepassword',
            'token' => 'sometoken',
            'contact' => 'DPD Contact',
            'contact_telephone' => '0860000000',
            'contact_email' => 'testemail@dpd.ie',
            'business_name' => 'DPD API Test Limited',
            'address_line_1' => 'Athlone Business Park',
            'address_line_2' => 'Dublin Road',
            'address_line_3' => 'Athlone',
            'address_line_4' => 'Co. Westmeath',
            'country_code' => 'IE',
        ]);
    }
}
