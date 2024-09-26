<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('oauth_auth_codes')) {
            Schema::create('oauth_auth_codes', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->foreignId('user_id')->index();
                $table->foreignId('client_id');
                $table->text('scopes')->nullable();
                $table->boolean('revoked');
                $table->dateTime('expires_at')->nullable();
            });
        }

        if (! Schema::hasTable('oauth_access_tokens')) {
            Schema::create('oauth_access_tokens', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->foreignId('client_id');
                $table->string('name')->nullable();
                $table->text('scopes')->nullable();
                $table->boolean('revoked');
                $table->timestamps();
                $table->dateTime('expires_at')->nullable();
            });
        }

        if (! Schema::hasTable('oauth_refresh_tokens')) {
            Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
                $table->string('id', 100)->primary();
                $table->string('access_token_id', 100)->index();
                $table->boolean('revoked');
                $table->dateTime('expires_at')->nullable();
            });
        }

        if (! Schema::hasTable('oauth_clients')) {
            Schema::create('oauth_clients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('name');
                $table->string('secret', 100)->nullable();
                $table->string('provider')->nullable();
                $table->text('redirect');
                $table->boolean('personal_access_client');
                $table->boolean('password_client');
                $table->boolean('revoked');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('oauth_personal_access_clients')) {
            Schema::create('oauth_personal_access_clients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id');
                $table->timestamps();
            });
        }

        Schema::create(config('queue-monitor.table'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('job_id')->index();
            $table->string('name')->nullable();
            $table->string('queue')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->string('started_at_exact')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('finished_at_exact')->nullable();
            $table->float('time_elapsed', 12, 6)->nullable()->index();
            $table->boolean('failed')->default(false)->index();
            $table->integer('attempt')->default(0);
            $table->integer('progress')->nullable();
            $table->longText('exception')->nullable();
            $table->text('exception_message')->nullable();
            $table->text('exception_class')->nullable();
            $table->longText('data')->nullable();
        });

        Schema::create('mail_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->default('');
            $table->string('mailable');
            $table->string('to', 255)->nullable();
            $table->string('reply_to', 100)->nullable();
            $table->text('subject')->nullable();
            $table->longtext('html_template');
            $table->longtext('text_template')->nullable();
            $table->timestamps();
        });

        Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'),
            function (Blueprint $table) {
                $table->id();
                $table->string('log_name')->nullable()->index();
                $table->text('description');
                $table->foreignId('subject_id')->nullable();
                $table->string('subject_type')->nullable();
                $table->string('event')->nullable();
                $table->foreignId('causer_id')->nullable();
                $table->string('causer_type')->nullable();
                $table->json('properties')->nullable();
                $table->uuid('batch_uuid')->nullable();
                $table->timestamps();
                $table->index(['subject_id', 'subject_type'], 'subject');
                $table->index(['causer_id', 'causer_type'], 'causer');
            });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable()->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('config');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('name', 100)->default('');
            $table->decimal('price', 10)->default(0);
            $table->decimal('sale_price', 10)->default(0);
            $table->date('sale_price_start_date')->default('1899-01-01');
            $table->date('sale_price_end_date')->default('1899-01-01');
            $table->string('commodity_code')->default('');
            $table->decimal('quantity', 10)->default(0)->index();
            $table->decimal('quantity_reserved', 10)->default(0)->index();
            $table->decimal('quantity_available', 10)->default(0)->index();
            $table->softDeletes();
            $table->timestamps();
            $table->index('deleted_at');
        });

        Schema::create('products_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');
            $table->string('alias')->unique();
            $table->timestamps();

            $table->foreign('product_id')
                ->on('products')
                ->references('id')
                ->onDelete('CASCADE');
        });

        Schema::create('orders_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('company')->default('');
            $table->string('gender')->default('');
            $table->string('address1')->default('');
            $table->string('address2')->default('');
            $table->string('postcode')->default('');
            $table->string('city')->default('');
            $table->string('state_code')->default('');
            $table->string('state_name')->default('');
            $table->string('country_code')->default('');
            $table->string('country_name')->default('');
            $table->string('fax')->default('');
            $table->string('website')->default('');
            $table->string('region')->default('');
            $table->longText('first_name_encrypted')->nullable();
            $table->longText('last_name_encrypted')->nullable();
            $table->longText('email_encrypted')->nullable();
            $table->longText('phone_encrypted')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique();
            $table->string('name');
            $table->foreignId('address_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('address_id')
                ->references('id')
                ->on('orders_addresses')
                ->onDelete('CASCADE');
        });

        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id');
            $table->foreignId('product_id')->index();
            $table->string('location_id')->default('');
            $table->string('warehouse_code', 5)->index();
            $table->string('shelve_location')->default('')->index();
            $table->timestamp('recalculated_at')->nullable()->index();
            $table->boolean('recount_required')->default(false)->index();

            $table->decimal('quantity_available', 20)
                ->storedAs('quantity - quantity_reserved')
                ->comment('quantity - quantity_reserved')->index();

            $table->decimal('quantity', 20)->default(0)->index();

            $table->boolean('is_in_stock')
                ->storedAs('quantity_available > 0')
                ->comment('quantity_available > 0')->index();

            $table->decimal('quantity_reserved', 20)->default(0)->index();
            $table->decimal('quantity_incoming', 20)->default(0)->index();

            $table->decimal('quantity_required', 20)
                ->storedAs('CASE WHEN (quantity - quantity_reserved + quantity_incoming) BETWEEN 0 AND reorder_point '.
                    'THEN restock_level - (quantity - quantity_reserved + quantity_incoming)'.
                    'ELSE 0 END')
                ->comment('CASE WHEN (quantity - quantity_reserved + quantity_incoming) BETWEEN 0 AND reorder_point '.
                    'THEN restock_level - (quantity - quantity_reserved + quantity_incoming)'.
                    'ELSE 0 END')->index();

            $table->decimal('reorder_point', 20)->default(0)->index();
            $table->decimal('restock_level', 20)->default(0)->index();
            $table->unsignedBigInteger('last_sequence_number')->nullable();
            $table->timestamp('first_movement_at')->nullable();
            $table->dateTime('last_movement_at')->nullable();
            $table->dateTime('first_received_at')->nullable();
            $table->dateTime('last_received_at')->nullable();
            $table->dateTime('first_sold_at')->nullable();
            $table->dateTime('last_sold_at')->nullable()->index();
            $table->timestamp('last_counted_at')->nullable()->index();
            $table->timestamp('in_stock_since')->nullable()->index();
            $table->dateTime('first_counted_at')->nullable()->index();
            $table->unsignedBigInteger('last_movement_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->foreign('warehouse_id')
                ->on('warehouses')
                ->references('id')
                ->onDelete('cascade');
        });

        DB::statement('CREATE INDEX last_sequence_number_desc_index ON inventory (last_sequence_number DESC)');
        DB::statement('CREATE INDEX last_movement_at_desc_index ON inventory (last_movement_at DESC)');
        DB::statement('CREATE INDEX first_received_at_desc_index ON inventory (first_received_at DESC)');
        DB::statement('CREATE INDEX last_received_at_desc_index ON inventory (last_received_at DESC)');
        DB::statement('CREATE INDEX first_sold_at_desc_index ON inventory (first_sold_at DESC)');

        Schema::create('orders_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->boolean('order_active')->default(true);
            $table->boolean('order_on_hold')->default(false);
            $table->boolean('hidden')->default(false);
            $table->boolean('sync_ecommerce')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('status_code')->default('')->index();
            $table->string('label_template')->default('')->index();
            $table->boolean('is_active')->default(0)->index();
            $table->boolean('is_on_hold')->default(false)->index();
            $table->boolean('is_editing')->default(0);

            $table->boolean('is_fully_paid')
                ->storedAs('total_products + total_shipping - total_discounts - total_paid < 0.01')
                ->comment('total_products + total_shipping - total_discounts - total_paid < 0.01');

            $table->integer('product_line_count')->nullable()->default(null);

            $table->decimal('total_products', 13)->nullable()->default(null);
            $table->decimal('total_shipping', 13)->default(0);
            $table->decimal('total_discounts', 13)->default(0);
            $table->decimal('total_order', 13)
                ->storedAs('total_products + total_shipping - total_discounts')
                ->comment('total_products + total_shipping - total_discounts');

            $table->decimal('total_paid', 13)->default(0);
            $table->decimal('total_outstanding', 13)
                ->storedAs('total_products + total_shipping - total_discounts - total_paid')
                ->comment('total_products + total_shipping - total_discounts - total_paid');

            $table->foreignId('shipping_address_id')->nullable();
            $table->string('shipping_method_code')->default('')->nullable(true);
            $table->string('shipping_method_name')->default('')->nullable(true);
            $table->foreignId('packer_user_id')->nullable();
            $table->timestamp('order_placed_at')->useCurrent()->nullable()->index();
            $table->timestamp('picked_at')->nullable();
            $table->timestamp('packed_at')->nullable();
            $table->timestamp('order_closed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->string('custom_unique_reference_id')->unique()->nullable();

            $table->fullText(['order_number', 'status_code']);

            $table->foreign('status_code')
                ->on('orders_statuses')
                ->references('code')
                ->onDelete('RESTRICT');

            $table->foreign('shipping_address_id')
                ->on('orders_addresses')
                ->references('id')
                ->onDelete('SET NULL');

            $table->foreign('packer_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
        });

        Schema::create('orders_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('product_id')->nullable();
            $table->string('sku_ordered');
            $table->string('name_ordered');
            $table->boolean('is_shipped')
                ->storedAs('quantity_ordered - quantity_split - quantity_shipped <= 0')
                ->comment('quantity_ordered - quantity_split - quantity_shipped <= 0')->index();

            $table->decimal('price', 10, 3)->default(0);
            $table->decimal('quantity_ordered', 10)->default(0);
            $table->decimal('quantity_split', 10)->default(0);
            $table->decimal('total_price', 20)
                ->storedAs('(quantity_ordered - quantity_split) * price')
                ->comment('(quantity_ordered - quantity_split) * price');
            $table->decimal('quantity_shipped', 10)->default(0);
            $table->decimal('quantity_to_pick', 10)
                ->storedAs('quantity_ordered - quantity_split - quantity_picked - quantity_skipped_picking')
                ->comment('quantity_ordered - quantity_split - quantity_picked - quantity_skipped_picking');
            $table->decimal('quantity_to_ship', 10)
                ->storedAs('quantity_ordered - quantity_split - quantity_shipped')
                ->comment('quantity_ordered - quantity_split - quantity_shipped');
            $table->decimal('quantity_picked', 10)->default(0);
            $table->decimal('quantity_skipped_picking', 10)->default(0);
            $table->decimal('quantity_not_picked', 10)->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->string('custom_unique_reference_id')->unique()->nullable();

            $table->foreign('product_id')
                ->on('products')
                ->references('id')
                ->onDelete('SET NULL');

            $table->foreign('order_id')
                ->on('orders')
                ->references('id')
                ->onDelete('cascade');
        });

        Schema::create('orders_products_totals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique();
            $table->integer('count')->default(0);
            $table->decimal('quantity_ordered', 20)->default(0);
            $table->decimal('quantity_split', 20)->default(0);
            $table->decimal('total_price', 20)->default(0);
            $table->decimal('quantity_picked', 20)->default(0);
            $table->decimal('quantity_skipped_picking', 20)->default(0);
            $table->decimal('quantity_not_picked', 20)->default(0);
            $table->decimal('quantity_shipped', 20)->default(0);
            $table->decimal('quantity_to_pick', 20)->default(0);
            $table->decimal('quantity_to_ship', 20)->default(0);
            $table->timestamp('max_updated_at')->default('2000-01-01 00:00:00');
            $table->timestamps();

            $table->index('count');
            $table->index('quantity_ordered');
            $table->index('quantity_split');
            $table->index('quantity_picked');
            $table->index('quantity_skipped_picking');
            $table->index('quantity_not_picked');
            $table->index('quantity_shipped');
            $table->index('quantity_to_pick');
            $table->index('quantity_to_ship');
            $table->index('updated_at');

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('CASCADE');
        });

        Schema::create('orders_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('order_id');
            $table->string('shipping_number');
            $table->string('carrier')->default('');
            $table->string('service')->default('');
            $table->string('tracking_url')->default('');
            $table->string('content_type')->default('');
            $table->longText('base64_pdf_labels');
            $table->timestamps();

            $table->fullText(['shipping_number']);

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('CASCADE');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
        });

        Schema::create('orders_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('user_id')->nullable();
            $table->string('comment');
            $table->timestamps();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('CASCADE');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
        });

        Schema::create('picks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('product_id')->nullable();
            $table->string('warehouse_code')->nullable();
            $table->string('sku_ordered');
            $table->string('name_ordered');
            $table->decimal('quantity_picked', 10, 2)->default(0);
            $table->decimal('quantity_skipped_picking', 10, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('SET NULL');
        });

        Schema::create('shipping_services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 25)->unique();
            $table->string('service_provider_class');
            $table->timestamps();
        });

        Schema::create('picks_orders_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pick_id')->constrained('picks')->cascadeOnDelete();
            $table->foreignId('order_product_id')->nullable()->constrained('orders_products')->nullOnDelete();
            $table->decimal('quantity_picked', 10, 2)->default(0);
            $table->decimal('quantity_skipped_picking', 10, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->json('name');
            $table->json('slug');
            $table->string('type')->nullable();
            $table->integer('order_column')->nullable();
            $table->timestamps();
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->integer('tag_id')->unsigned();
            $table->morphs('taggable');
            $table->unique(['tag_id', 'taggable_id', 'taggable_type']);
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        });

        Schema::create('products_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->index();
            $table->foreignId('warehouse_id');
            $table->string('location_id')->default('');
            $table->string('warehouse_code', 5)->index();
            $table->decimal('cost', 20, 2)->default(0);
            $table->decimal('price', 20)->default(0);
            $table->decimal('sale_price', 20)->default(0);
            $table->date('sale_price_start_date')->default('2000-01-01');
            $table->date('sale_price_end_date')->default('2000-01-01');
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);

            $table->foreign('product_id')
                ->on('products')
                ->references('id')
                ->onDelete('CASCADE');

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('CASCADE');
        });

        Schema::create('modules_printnode_clients', function (Blueprint $table) {
            $table->id();
            $table->string('api_key');
            $table->timestamps();
        });

        Schema::create('module_auto_status_pickings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('modules_dpd_ireland_configuration', function (Blueprint $table) {
            $table->id();
            $table->boolean('live')->default(false);
            $table->longText('token');
            $table->longText('user');
            $table->longText('password');
            $table->string('contact')->default('');
            $table->string('contact_telephone')->default('');
            $table->string('contact_email')->default('');
            $table->string('business_name')->default('');
            $table->string('address_line_1')->default('');
            $table->string('address_line_2')->default('');
            $table->string('address_line_3')->default('');
            $table->string('address_line_4')->default('');
            $table->string('country_code', 10)->default('');
            $table->timestamps();
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('service_provider_class');
            $table->boolean('enabled')->default(false);
            $table->timestamps();

            $table->unique('service_provider_class');
        });

        Schema::create('modules_autostatus_picking_configurations', function (Blueprint $table) {
            $table->id();
            $table->integer('max_batch_size')->default(10);
            $table->integer('max_order_age')->default(5);
            $table->timestamps();
        });

        Schema::create('modules_printnode_print_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // = 'Url Print';
            $table->string('printer_id'); // = $printerId;
            $table->string('content_type'); // = 'pdf_base64';
            $table->longText('content'); // = $base64PdfString;
            $table->integer('expire_after');
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('modules_automations', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('priority')->default(0);
            $table->boolean('enabled')->default(false);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('modules_automations_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id');
            $table->string('condition_class')->nullable();
            $table->string('condition_value')->default('');
            $table->timestamps();

            $table->unique(
                ['automation_id', 'condition_class', 'condition_value'],
                'modules_automations_conditions_automation_id_class_value_unique'
            );

            $table->foreign('automation_id')
                ->references('id')
                ->on('modules_automations')
                ->onDelete('CASCADE');
        });

        Schema::create('modules_automations_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id');
            $table->smallInteger('priority')->default(0);
            $table->string('action_class')->nullable();
            $table->string('action_value')->default('');
            $table->timestamps();

            $table->foreign('automation_id')
                ->references('id')
                ->on('modules_automations')
                ->onDelete('CASCADE');
        });

        Schema::create('orders_products_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->foreignId('order_id')->nullable();
            $table->foreignId('order_product_id');
            $table->foreignId('order_shipment_id')->nullable();
            $table->string('sku_shipped')->default('');
            $table->decimal('quantity_shipped', 10);
            $table->timestamps();

            $table->foreign('order_id')
                ->on('orders')
                ->references('id')
                ->onDelete('SET NULL');
        });

        Schema::create('modules_rmsapi_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->nullable();
            $table->string('location_id');
            $table->string('url');
            $table->string('username');
            $table->string('password');
            $table->string('price_field_name')->default('price');
            $table->unsignedBigInteger('products_last_timestamp')->default(0);
            $table->unsignedBigInteger('shippings_last_timestamp')->default(0);
            $table->unsignedBigInteger('sales_last_timestamp')->default(0);
            $table->timestamps();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->cascadeOnDelete();
        });

        Schema::create('modules_rmsapi_shipping_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id');
            $table->json('raw_import');
            $table->timestamps();

            $table->foreign('connection_id')
                ->references('id')
                ->on('modules_rmsapi_connections')
                ->onDelete('cascade');
        });

        Schema::create('modules_rmsapi_products_imports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('connection_id')->index();
            $table->string('warehouse_code')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('rms_product_id')->nullable()->index();
            $table->string('sku')->nullable();
            $table->string('name')->nullable();
            $table->boolean('is_web_item')->nullable()->index();
            $table->decimal('quantity_on_hand', 20)->nullable();
            $table->decimal('quantity_committed', 20)->nullable();
            $table->decimal('quantity_available', 20)->nullable();
            $table->decimal('quantity_on_order', 20)->nullable();
            $table->decimal('reorder_point', 20)->nullable();
            $table->decimal('restock_level', 20)->nullable();
            $table->decimal('price', 20)->nullable();
            $table->decimal('price_a', 20)->nullable();
            $table->decimal('cost', 20)->nullable();
            $table->decimal('sale_price', 20)->nullable();
            $table->timestamp('sale_price_start_date')->nullable();
            $table->timestamp('sale_price_end_date')->nullable();
            $table->string('department_name')->nullable();
            $table->string('category_name')->nullable();
            $table->string('sub_description_1')->nullable();
            $table->string('sub_description_2')->nullable();
            $table->string('sub_description_3')->nullable();
            $table->string('supplier_name')->nullable();
            $table->timestamp('reserved_at')->nullable()->index();
            $table->timestamp('processed_at')->nullable()->index();
            $table->uuid('batch_uuid')->nullable();
            $table->json('raw_import')->nullable();
            $table->timestamps();

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventory')
                ->cascadeOnDelete();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('connection_id')
                ->references('id')
                ->on('modules_rmsapi_connections')
                ->cascadeOnDelete();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->cascadeOnDelete();

            $table->foreign('warehouse_code')
                ->references('code')
                ->on('warehouses')
                ->cascadeOnDelete();
        });

        Schema::create('modules_api2cart_connections', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('');
            $table->string('url')->default('');
            $table->string('inventory_source_warehouse_tag')->nullable();
            $table->foreignId('inventory_source_warehouse_tag_id')->nullable();
            $table->foreignId('pricing_source_warehouse_id')->nullable();
            $table->char('prefix', 10)->default('');
            $table->string('bridge_api_key')->nullable();
            $table->unsignedBigInteger('magento_store_id')->nullable();
            $table->string('magento_warehouse_id')->nullable();
            $table->string('pricing_location_id', 5)->nullable(true);
            $table->dateTime('last_synced_modified_at')->default('2020-01-01 00:00:00');
            $table->timestamps();
        });

        Schema::create('modules_api2cart_order_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id')->nullable();
            $table->foreignId('order_id')->nullable();
            $table->string('shipping_method_name')->nullable(true);
            $table->string('shipping_method_code')->nullable(true);
            $table->dateTime('when_processed')->nullable()->index();
            $table->string('order_number')->nullable()->index();
            $table->integer('api2cart_order_id');
            $table->json('raw_import');
            $table->timestamps();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('SET NULL');

            $table->foreign('connection_id')
                ->references('id')
                ->on('modules_api2cart_connections')
                ->onDelete('SET NULL');
        });

        Schema::create('modules_api2cart_product_links', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_in_sync')->nullable()->index();
            $table->foreignId('product_id');
            $table->foreignId('api2cart_connection_id');
            $table->string('api2cart_product_type')->nullable();
            $table->string('api2cart_product_id')->nullable();
            $table->timestamp('last_pushed_at')->nullable();
            $table->json('last_pushed_response')->nullable();
            $table->dateTime('last_fetched_at')->nullable();
            $table->json('last_fetched_data')->nullable();
            $table->decimal('api2cart_quantity', 10, 2)->nullable();
            $table->decimal('api2cart_price', 10, 2)->nullable();
            $table->decimal('api2cart_sale_price', 10, 2)->nullable();
            $table->date('api2cart_sale_price_start_date')->nullable();
            $table->date('api2cart_sale_price_end_date')->nullable();
            $table->timestamps();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('CASCADE');

            $table->foreign('api2cart_connection_id')
                ->references('id')
                ->on('modules_api2cart_connections')
                ->onDelete('CASCADE');

            $table->unique(['api2cart_connection_id', 'api2cart_product_id'], 'api2cart_connection_product_id_unique');
        });

        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->default('');
            $table->string('database_version')->default('0.0.0');
            $table->boolean('disable_2fa')->default(0);
            $table->timestamps();
        });

        Schema::create('heartbeats', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('level')->default('error')->index();
            $table->string('error_message', 255)->nullable();
            $table->timestamp('expires_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('modules_dpduk_connections', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('password');
            $table->string('account_number');
            $table->foreignId('collection_address_id')->nullable();
            $table->string('geo_session')->nullable();
            $table->timestamps();

            $table->foreign('collection_address_id')
                ->references('id')
                ->on('orders_addresses')
                ->onDelete('SET NULL');
        });

        Schema::create('modules_boxtop_warehouse_stock', function (Blueprint $table) {
            $table->id();
            $table->string('SKUGroup');
            $table->string('SKUNumber');
            $table->string('SKUName');
            $table->string('Attributes');
            $table->string('Warehouse');
            $table->float('WarehouseQuantity');
            $table->float('Allocated');
            $table->float('Available');
            $table->timestamps();
        });

        Schema::create('modules_boxtop_order_lock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique();
            $table->timestamps();
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->string('warehouse_code', 5)->index();
            $table->dateTime('occurred_at')->index();
            $table->unsignedInteger('sequence_number')->nullable()->comment('row_number() over (partition by inventory_id order by occurred_at asc, id asc)')->index();
            $table->string('type', 50)->index();
            $table->string('custom_unique_reference_id')->nullable()->unique();
            $table->foreignId('inventory_id');
            $table->foreignId('product_id');
            $table->foreignId('warehouse_id');
            $table->decimal('quantity_delta', 20);
            $table->decimal('quantity_before', 20);
            $table->decimal('quantity_after', 20);
            $table->string('description', 255);

            $table->foreignId('user_id')->nullable();
            $table->timestamps();

            $table->index(['occurred_at', 'id']);
            $table->index(['inventory_id', 'occurred_at']);
            $table->unique(['inventory_id', 'sequence_number']);

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventory')
                ->cascadeOnDelete();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('warehouse_code')
                ->references('code')
                ->on('warehouses')
                ->restrictOnDelete();
        });

        DB::statement('CREATE INDEX occurred_at_sequence_number_index ON inventory_movements (occurred_at DESC, sequence_number DESC)');

        Schema::create('inventory_movements_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->foreignId('inventory_id');
            $table->foreignId('product_id')->index();
            $table->string('warehouse_code', 5)->index();

            $table->decimal('last7days_quantity_delta', 13, 2)->default(0);
            $table->decimal('last14days_quantity_delta', 13, 2)->default(0);
            $table->decimal('last28days_quantity_delta', 13, 2)->default(0);

            $table->unsignedBigInteger('last7days_min_movement_id')->nullable();
            $table->unsignedBigInteger('last7days_max_movement_id')->nullable();
            $table->unsignedBigInteger('last14days_min_movement_id')->nullable();
            $table->unsignedBigInteger('last14days_max_movement_id')->nullable();
            $table->unsignedBigInteger('last28days_min_movement_id')->nullable();
            $table->unsignedBigInteger('last28days_max_movement_id')->nullable();
            $table->dateTime('last_sold_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['type', 'inventory_id']);

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventory')
                ->cascadeOnDelete();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('warehouse_code')
                ->references('code')
                ->on('warehouses')
                ->cascadeOnDelete();
        });

        Schema::create('modules_webhooks_pending_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('model_class');
            $table->foreignId('model_id');
            $table->json('message');
            $table->string('sns_message_id')->nullable();
            $table->timestamp('reserved_at')->nullable()->index();
            $table->timestamp('available_at')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->index(['reserved_at', 'published_at']);
        });

        Schema::create('modules_webhooks_configuration', function (Blueprint $table) {
            $table->id();
            $table->string('topic_arn')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_totals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->decimal('quantity', 20)->default(0);
            $table->decimal('quantity_reserved', 20)->default(0);
            $table->decimal('quantity_available', 20)
                ->storedAs('quantity - quantity_reserved')
                ->comment('quantity - quantity_reserved');
            $table->decimal('quantity_incoming', 20)->default(0);
            $table->timestamp('max_inventory_updated_at')->default('2000-01-01 00:00:00');
            $table->timestamp('calculated_at')->nullable()->index();
            $table->timestamps();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
        });

        Schema::create('data_collections', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->foreignId('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('cascade');
            $table->unsignedBigInteger('destination_collection_id')->nullable();
            $table->unsignedBigInteger('destination_warehouse_id')->nullable();
            $table->string('name');
            $table->string('currently_running_task')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('destination_collection_id')
                ->references('id')
                ->on('data_collections')
                ->restrictOnDelete();

            $table->foreign('destination_warehouse_id')
                ->references('id')
                ->on('warehouses');
        });

        Schema::create('data_collection_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_collection_id')->constrained()->onDelete('cascade');

            $table->unsignedBigInteger('inventory_id');

            $table->foreignId('product_id');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->boolean('is_processed')->nullable();
            $table->double('total_transferred_in', 10)->default(0);
            $table->double('total_transferred_out', 10)->default(0);
            $table->decimal('quantity_requested', 20)->nullable();
            $table->decimal('quantity_scanned', 20)->default(0);

            $table->decimal('quantity_to_scan', 20)
                ->storedAs('GREATEST(0, IFNULL(quantity_requested, 0) - IFNULL(total_transferred_out, 0) - IFNULL(total_transferred_in, 0) - IFNULL(quantity_scanned, 0))')
                ->comment('GREATEST(0, IFNULL(quantity_requested, 0) - IFNULL(total_transferred_out, 0) - IFNULL(total_transferred_in, 0) - IFNULL(quantity_scanned, 0))');

            $table->boolean('is_requested')
                ->storedAs('IFNULL(data_collection_records.quantity_requested, 0) = 0')
                ->comment('IFNULL(data_collection_records.quantity_requested, 0) = 0')
                ->index();

            $table->boolean('is_fully_scanned')
                ->storedAs('quantity_to_scan <= 0')
                ->comment('quantity_to_scan <= 0')
                ->index();

            $table->boolean('is_over_scanned')
                ->storedAs('IFNULL(data_collection_records.quantity_scanned, 0) > IFNULL(data_collection_records.quantity_requested, 0)')
                ->comment('IFNULL(data_collection_records.quantity_scanned, 0) > IFNULL(data_collection_records.quantity_requested, 0)')
                ->index();

            $table->string('custom_uuid')->unique()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });

        Schema::create('stocktake_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->references('id')->on('inventory')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->references('id')->on('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->references('id')->on('warehouses')->cascadeOnDelete();
            $table->integer('points');
            $table->string('reason')->index();
            $table->timestamps();

            $table->index(['warehouse_id', 'reason']);
            $table->unique(['inventory_id', 'reason']);
            $table->index(['inventory_id', 'reason']);
        });

        Schema::create('modules_magento2api_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->boolean('exists_in_magento')->nullable();
            $table->decimal('magento_price', 20)->nullable();
            $table->decimal('magento_sale_price', 20)->nullable();
            $table->dateTime('magento_sale_price_start_date')->nullable();
            $table->dateTime('magento_sale_price_end_date')->nullable();
            $table->boolean('is_inventory_in_sync')->nullable();
            $table->decimal('quantity', 20)->nullable();
            $table->boolean('is_in_stock')->nullable();
            $table->timestamp('stock_items_fetched_at')->nullable();
            $table->json('stock_items_raw_import')->nullable();
            $table->timestamp('base_prices_fetched_at')->nullable();
            $table->json('base_prices_raw_import')->nullable();
            $table->timestamp('special_prices_fetched_at')->nullable();
            $table->json('special_prices_raw_import')->nullable();
            $table->timestamps();
        });

        Schema::create('modules_inventory_reservations_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->nullable();
            $table->timestamps();

            $table->foreign('warehouse_id', 'modules_inventory_reservations_warehouse_id_foreign')
                ->references('id')
                ->on('warehouses');

            $table->index('warehouse_id', 'modules_inventory_reservations_warehouse_id_index');
        });

        Schema::create('modules_magento2api_connections', function (Blueprint $table) {
            $table->id();
            $table->string('base_url');
            $table->integer('magento_store_id')->nullable();
            $table->integer('inventory_source_warehouse_tag_id')->nullable();
            $table->integer('pricing_source_warehouse_id')->nullable();
            $table->longText('access_token_encrypted')->nullable();
            $table->timestamps();
        });

        Schema::create('modules_rmsapi_sales_imports', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable()->index();
            $table->string('type')->nullable()->index();
            $table->unsignedBigInteger('inventory_movement_id')->nullable();
            $table->foreignId('connection_id');
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->foreignId('product_id')->nullable();
            $table->dateTime('reserved_at')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('price', 20)->nullable();
            $table->decimal('quantity', 20)->nullable();
            $table->timestamp('transaction_time')->nullable();
            $table->string('transaction_number')->nullable();
            $table->integer('transaction_entry_id')->nullable();
            $table->string('comment')->nullable();
            $table->json('raw_import');
            $table->timestamps();

            $table->foreign('inventory_movement_id')
                ->references('id')
                ->on('inventory_movements')
                ->onDelete('SET NULL');

            $table->foreign('connection_id')
                ->references('id')
                ->on('modules_rmsapi_connections')
                ->onDelete('cascade');

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventory');
        });

        if (! Schema::hasColumn(config('activitylog.table_name'), 'event')) {
            Schema::connection(config('activitylog.database_connection'))
                ->table(config('activitylog.table_name'), function (Blueprint $table) {
                    $table->string('event')->nullable()->after('subject_type');
                });
        }

        if (! Schema::hasColumn(config('activitylog.table_name'), 'batch_uuid')) {
            Schema::connection(config('activitylog.database_connection'))
                ->table(config('activitylog.table_name'), function (Blueprint $table) {
                    $table->uuid('batch_uuid')->nullable()->after('properties');
                });
        }

        Schema::create('modules_queue_monitor_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique();
            $table->string('job_class')->index();
            $table->timestamp('dispatched_at')->useCurrent()->index();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->bigInteger('seconds_dispatching')
                ->storedAs('TIMESTAMPDIFF(SECOND, dispatched_at, processing_at)')
                ->comment('TIMESTAMPDIFF(SECOND, dispatched_at, processing_at)');

            $table->bigInteger('seconds_running')
                ->storedAs('TIMESTAMPDIFF(SECOND, processing_at, processed_at)')
                ->comment('TIMESTAMPDIFF(SECOND, processing_at, processed_at)');
        });

        Schema::create('modules_inventory_movements_statistics_last28days_sale_movements', function (Blueprint $table) {
            $table->foreignId('inventory_movement_id')->unique('inventory_movement_id_index');
            $table->foreignId('inventory_id')->index('inventory_id_index');
            $table->unsignedBigInteger('warehouse_id')->nullable()->index('warehouse_id_index');
            $table->dateTime('sold_at')->index('sold_at_index');
            $table->decimal('quantity_sold', 20, 3);
            $table->boolean('included_in_7days')->nullable()->index('included_in_7days');
            $table->boolean('included_in_14days')->nullable()->index('included_in_14days');
            $table->boolean('included_in_28days')->nullable()->index('included_in_28days');
        });

        Schema::create('modules_slack_config', function (Blueprint $table) {
            $table->id();
            $table->string('incoming_webhook_url')->nullable();
            $table->timestamps();
        });

        Schema::create('modules_inventory_totals_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('totals_max_product_id_checked')->default(0);
            $table->unsignedBigInteger('totals_by_warehouse_tag_max_inventory_id_checked')->default(0);
            $table->timestamps();
        });

        Schema::create('inventory_totals_by_warehouse_tag', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedInteger('tag_id');
            $table->decimal('quantity', 20)->default(0);
            $table->decimal('quantity_reserved', 20)->default(0);
            $table->decimal('quantity_available', 20)
                ->storedAs('quantity - quantity_reserved')
                ->comment('quantity - quantity_reserved');
            $table->decimal('quantity_incoming', 20)->default(0);
            $table->timestamp('max_inventory_updated_at')->default('2000-01-01 00:00:00');
            $table->timestamp('calculated_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['product_id', 'tag_id'], 'uk_product_tag');

            $table->foreign('product_id', 'fk_inventory_totals_by_warehouse_tag_product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('tag_id', 'fk_inventory_totals_by_warehouse_tag_tag_id')
                ->references('id')
                ->on('tags')
                ->cascadeOnDelete();
        });

        Schema::create('modules_inventory_movements_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('totals_by_warehouse_tag_max_inventory_movement_id_checked')->default(0);
            $table->unsignedBigInteger('quantity_before_job_last_movement_id_checked')->default(0);
            $table->unsignedBigInteger('quantity_before_basic_job_last_movement_id_checked')->default(0);
            $table->unsignedBigInteger('quantity_before_stocktake_job_last_movement_id_checked')->default(0);
            $table->timestamps();
        });

        Schema::create('modules_stocktaking_suggestions_configurations', function (Blueprint $table) {
            $table->id();
            $table->date('min_count_date')->nullable();
            $table->timestamps();
        });

        Schema::create('manual_request_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_name');
            $table->string('job_class');
            $table->timestamps();

            $table->unique('job_name');
            $table->unique('job_class');
        });

        DB::statement('
            CREATE OR REPLACE VIEW modules_magento2api_products_prices_comparison_view AS
            SELECT
                modules_magento2api_products.connection_id as modules_magento2api_connection_id,
                modules_magento2api_products.id as modules_magento2api_products_id,
                products.sku,
                modules_magento2api_connections.magento_store_id,
                modules_magento2api_products.magento_price,
                products_prices.price as expected_price,

                modules_magento2api_products.magento_sale_price,
                products_prices.sale_price as expected_sale_price,

                modules_magento2api_products.magento_sale_price_start_date,
                products_prices.sale_price_start_date as expected_sale_price_start_date,

                modules_magento2api_products.magento_sale_price_end_date,
                products_prices.sale_price_end_date as expected_sale_price_end_date,

                modules_magento2api_products.base_prices_fetched_at,
                modules_magento2api_products.special_prices_fetched_at

            FROM modules_magento2api_products

            LEFT JOIN products ON products.id = modules_magento2api_products.product_id

            LEFT JOIN modules_magento2api_connections
              ON modules_magento2api_connections.id = modules_magento2api_products.connection_id

            LEFT JOIN products_prices
              ON products_prices.product_id = modules_magento2api_products.product_id
              AND products_prices.warehouse_id = modules_magento2api_connections.pricing_source_warehouse_id

            WHERE
                modules_magento2api_connections.pricing_source_warehouse_id IS NOT NULL
                AND IFNULL(modules_magento2api_products.exists_in_magento, 0) = 1
        ');

        DB::statement("
            CREATE OR REPLACE VIEW modules_magento2api_products_inventory_comparison_view AS
            SELECT
               modules_magento2api_products.connection_id as modules_magento2api_connection_id,
               modules_magento2api_products.id AS modules_magento2api_products_id,
               products.sku AS sku,
               floor(max(modules_magento2api_products.quantity)) AS magento_quantity,
               if((floor(sum(inventory.quantity_available)) < 0), 0, floor(sum(inventory.quantity_available))) AS expected_quantity,
               modules_magento2api_products.stock_items_fetched_at

            from modules_magento2api_products

            left join modules_magento2api_connections
              ON modules_magento2api_connections.id = modules_magento2api_products.connection_id

            left join taggables
              ON taggables.tag_id = modules_magento2api_connections.inventory_source_warehouse_tag_id
              AND taggables.taggable_type = 'App\\\\Models\\\\Warehouse'

            left join warehouses
              ON warehouses.id = taggables.taggable_id

            left join inventory
              on inventory.product_id = modules_magento2api_products.product_id
              and inventory.warehouse_id = warehouses.id

            left join products
              on products.id = modules_magento2api_products.product_id

            WHERE
                modules_magento2api_connections.inventory_source_warehouse_tag_id IS NOT NULL
                AND IFNULL(modules_magento2api_products.exists_in_magento, 1) = 1

            GROUP BY modules_magento2api_products.id
        ");

        DB::statement('
            CREATE OR REPLACE VIEW modules_rmsapi_products_quantity_comparison_view AS
                SELECT
                 modules_rmsapi_products_imports.id  as record_id,
                  modules_rmsapi_products_imports.sku as product_sku,
                  modules_rmsapi_products_imports.product_id as product_id,
                  modules_rmsapi_products_imports.warehouse_id as warehouse_id,
                  modules_rmsapi_products_imports.warehouse_code,
                  modules_rmsapi_products_imports.quantity_on_hand as rms_quantity,
                  inventory.quantity as pm_quantity,
                  modules_rmsapi_products_imports.quantity_on_hand - inventory.quantity as quantity_delta,
                  modules_rmsapi_products_imports.updated_at as modules_rmsapi_products_imports_updated_at,
                  inventory.id as inventory_id,
                  (
                      SELECT max(id)
                      FROM  inventory_movements
                      WHERE inventory_movements.inventory_id = inventory.id
                        AND inventory_movements.description = "stocktake"
                        AND inventory_movements.user_id = 1
                        AND inventory_movements.created_at > date_sub(now(), interval 7 day)
                  ) as movement_id

                FROM modules_rmsapi_products_imports
                INNER JOIN inventory
                   ON inventory.product_id = modules_rmsapi_products_imports.product_id
                   AND inventory.warehouse_id = modules_rmsapi_products_imports.warehouse_id

                WHERE modules_rmsapi_products_imports.id IN (
                    SELECT MAX(ID)
                    FROM modules_rmsapi_products_imports
                    GROUP BY
                        warehouse_id,
                        product_id
                );
        ');
    }
};
