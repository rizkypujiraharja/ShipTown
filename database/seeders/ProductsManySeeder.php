<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;

class ProductsManySeeder extends Seeder
{
    public function run(): void
    {
        $i = 1000;

        while ($i > 0) {
            $this->createRecords(500);
            $i--;
        }
    }

    public static function createRecords(int $i): array
    {
        $records = [];

        while ($i > 0) {
            try {
                $rand = \Str::uuid();
                $records[] = ['sku' => $rand, 'name' => 'test_'.$rand];
                $i--;
            } catch (\Exception $e) {
                report($e);
            }
        }

        DB::table('products')->insert($records);

        return $records;
    }
}
