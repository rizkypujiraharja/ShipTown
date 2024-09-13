<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TemporaryTable;
use App\Http\Controllers\Controller;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CsvImportController extends Controller
{
    /**
     * @var string[][]
     */
    private array $rules = [
        'data_collection_id' => ['required', 'exists:data_collections,id'],
        'data.*.product_sku' => ['required_if:product_id,null', 'string'],
        'data.*.product_id' => ['required_if:product_sku,null', 'integer'],
        'data.*.quantity_requested' => ['nullable', 'numeric'],
        'data.*.quantity_scanned' => ['nullable', 'numeric'],
    ];

    public function store(Request $request): JsonResource
    {
        $validatedData = Validator::make($request->all(), $this->rules)->validate();

        TemporaryTable::fromArray('tempTable_csv_import', $validatedData['data'], function (Blueprint $table) {
            $table->temporary();
            $table->id();
            $table->string('product_sku')->nullable();
            $table->foreignId('product_id')->nullable();
            $table->double('quantity_requested')->nullable();
            $table->double('quantity_scanned')->nullable();
            $table->double('quantity_to_scan')->nullable();
            $table->timestamps();
        });

        DB::statement('
            UPDATE tempTable_csv_import
            LEFT JOIN products_aliases ON tempTable_csv_import.product_sku = products_aliases.alias
            SET tempTable_csv_import.product_id = products_aliases.product_id
            WHERE tempTable_csv_import.product_id IS NULL
        ');

        $skuNotFoundErrors = DB::table('tempTable_csv_import')
            ->whereNull('product_id')
            ->select('product_sku')
            ->get()
            ->map(function ($item) use (&$errors) {
                return 'SKU not found: '.$item->product_sku;
            })
            ->filter();

        if ($skuNotFoundErrors->isNotEmpty()) {
            throw ValidationException::withMessages($skuNotFoundErrors->toArray());
        }

        DB::statement('
            INSERT INTO data_collection_records (
                data_collection_id,
                inventory_id,
                product_id,
                warehouse_id,
                warehouse_code,
                quantity_requested,
                quantity_scanned,
                unit_cost,
                unit_full_price,
                unit_sold_price,
                created_at,
                updated_at
            )
            SELECT '.$validatedData['data_collection_id'].',
                inventory.id,
                tempTable.product_id,
                data_collections.warehouse_id,
                data_collections.warehouse_code,
                tempTable.quantity_requested,
                IFNULL(tempTable.quantity_scanned, 0) as quantity_scanned,
                IFNULL(products_prices.cost, 0) as unit_cost,
                IFNULL(products_prices.price, 0) as unit_full_price,
                IFNULL(products_prices.sale_price, 0) as unit_sold_price,
                NOW(),
                NOW()

            FROM tempTable_csv_import as tempTable
            LEFT JOIN data_collections
                ON data_collections.id = '.$validatedData['data_collection_id'].'
            LEFT JOIN inventory
                ON inventory.product_id = tempTable.product_id
                AND inventory.warehouse_id = data_collections.warehouse_id
            LEFT JOIN products_prices
                ON products_prices.inventory_id = inventory.id
        ');

        return JsonResource::make(['success' => true]);
    }

    /**
     * @param $data
     */
    public function fromArray(string $tempTableName, \Closure $callback, $data): void
    {
    }
}
