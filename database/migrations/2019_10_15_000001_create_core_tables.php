<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->integer('printer_id')->nullable();
                $table->string('address_label_template')->nullable();
                $table->boolean('ask_for_shipping_number')->default(true);
                $table->string('name');
                $table->string('email')->unique();
                $table->foreignId('warehouse_id')->nullable(true);
                $table->foreignId('location_id')->nullable(true);
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('two_factor_code')->nullable();
                $table->dateTime('two_factor_expires_at')->nullable();
                $table->string('default_dashboard_uri')->nullable();
                $table->rememberToken();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->unique();
                $table->foreignId('user_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('payload');
                $table->integer('last_activity');
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('navigation_menu')) {
            Schema::create('navigation_menu', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('url', 999)->default('');
                $table->string('group', 100);
                $table->timestamps();
            });
        }

        $this->installSpatiePermissions();
        $this->createDefaultUserRoles();
    }

    private function createDefaultUserRoles(): void
    {
        Role::findOrCreate('user');
        $admin = Role::findOrCreate('admin');

        $defaultAdminPermissions = ['manage users', 'list users', 'invite users', 'list roles'];

        foreach ($defaultAdminPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            $admin->givePermissionTo($permission);
        }
    }

    private function installSpatiePermissions(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        if (! Schema::hasTable('permissions')) {
            Schema::create($tableNames['permissions'], function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('roles')) {
            Schema::create($tableNames['roles'], function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('model_has_permissions')) {
            Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames) {
                $table->unsignedBigInteger('permission_id');

                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);
                $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

                $table->foreign('permission_id')
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                $table->primary(
                    ['permission_id', $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            });
        }

        if (! Schema::hasTable('model_has_roles')) {
            Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames) {
                $table->unsignedBigInteger('role_id');

                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);
                $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

                $table->foreign('role_id')
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                $table->primary(
                    ['role_id', $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            });
        }

        if (! Schema::hasTable('role_has_permissions')) {
            Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');

                $table->foreign('permission_id')
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                $table->foreign('role_id')
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
            });
        }

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }
};
