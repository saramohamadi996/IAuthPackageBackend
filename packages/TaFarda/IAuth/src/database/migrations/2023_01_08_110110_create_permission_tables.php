<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;
use \TaFarda\IAuth\Models\Admin;
use \TaFarda\IAuth\Models\Product;
use Illuminate\Support\Facades\Hash;
use \Spatie\Permission\Models\Role;
use \Spatie\Permission\Models\Permission;
use \Illuminate\Support\Str;

class CreatePermissionTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teams = config('permission.teams');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }
        if ($teams && empty($columnNames['team_foreign_key'] ?? null)) {
            throw new \Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id'); // permission id
            $table->string('name', 125);       // For MySQL 8.0 use string('name', 125);
            $table->string('fa_name', 125);       // For MySQL 8.0 use string('name', 125);
            $table->string('guard_name', 125); // For MySQL 8.0 use string('guard_name', 125);
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
            $table->bigIncrements('id'); // role id
            if ($teams || config('permission.testing')) { // permission.testing is a fix for sqlite testing
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name', 125);       // For MySQL 8.0 use string('name', 125);
            $table->string('fa_name', 125);       // For MySQL 8.0 use string('name', 125);
            $table->string('guard_name', 125); // For MySQL 8.0 use string('guard_name', 125);
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $teams) {
            $table->unsignedBigInteger(PermissionRegistrar::$pivotPermission);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign(PermissionRegistrar::$pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], PermissionRegistrar::$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([PermissionRegistrar::$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary');
            }

        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $teams) {
            $table->unsignedBigInteger(PermissionRegistrar::$pivotRole);

            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign(PermissionRegistrar::$pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary([$columnNames['team_foreign_key'], PermissionRegistrar::$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([PermissionRegistrar::$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary');
            }
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->unsignedBigInteger(PermissionRegistrar::$pivotPermission);
            $table->unsignedBigInteger(PermissionRegistrar::$pivotRole);

            $table->foreign(PermissionRegistrar::$pivotPermission)
                ->references('id') // permission id
                ->on($tableNames['permissions'])
                ->onDelete('cascade');

            $table->foreign(PermissionRegistrar::$pivotRole)
                ->references('id') // role id
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary([PermissionRegistrar::$pivotPermission, PermissionRegistrar::$pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));

        // create roles
        Role::create(['name' => 'super-admin', 'fa_name' => 'سوپرادمین']);
        Role::create(['name' => 'admin', 'fa_name' => 'ادمین']);

        // create admin permissions
        Permission::create(['name' => 'admins-index', 'fa_name' => 'نمایش لیست ادمین ها']);
        Permission::create(['name' => 'admins-store', 'fa_name' => 'افزودن ادمین جدید']);
        Permission::create(['name' => 'admins-update', 'fa_name' => 'ویرایش ادمین']);
        //create product permissions
        Permission::create(['name' => 'products-index', 'fa_name' => 'نمایش لیست محصولات']);
        Permission::create(['name' => 'products-store', 'fa_name' => 'افزودن محصول جدید']);
        Permission::create(['name' => 'products-update', 'fa_name' => 'ویرایش محصول']);
        //create user permissions
        Permission::create(['name' => 'users-index', 'fa_name' => 'نمایش لیست کاربران']);
        Permission::create(['name' => 'users-store', 'fa_name' => 'افزودن کاربر جدید']);
        Permission::create(['name' => 'users-update', 'fa_name' => 'ویرایش کاربر']);

        $admin = Admin::create([
            'email' => 'admin@tafarda.studio',
            'password' => Hash::make('TaFardaAdmin'),
            'webservice_call_token' => Str::random(12),
            'status' => 1
        ]);
        $admin->assignRole('super-admin');
        $admin->givePermissionTo(Permission::all());

        $rsoonAdmin = Admin::create([
            'email' => 'rsoon@tafarda.studio',
            'password' => Hash::make('TaFardaRsoon'),
            'webservice_call_token' => Str::random(12),
            'status' => 1
        ]);
        $rsoonAdmin->assignRole('admin');
        $rsoonAdmin->givePermissionTo(Permission::all());

        $rsoonProduct = Product::create([
            'title' => 'آرسون',
            'description' => 'آرسون',
            'sms_verify_template' => 'rsoon-verify',
            'email_verify_template' => 'rsoon-verify',
            'status' => 1,
            'admin_id' => $rsoonAdmin->id
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function down()
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
}
