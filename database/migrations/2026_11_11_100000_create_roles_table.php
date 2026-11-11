<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('key')->unique();
            $table->json('permissions');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        $allPermissions = [];
        foreach (config('admin_permissions') as $modules) {
            foreach ($modules as $module => $actions) {
                foreach ($actions as $action) {
                    $allPermissions[$module.'.'.Str::slug($action, '_')] = true;
                }
            }
        }

        Role::create([
            'name' => 'Administrator',
            'key' => 'admin',
            'permissions' => $allPermissions,
            'is_system' => true,
        ]);
        Role::create([
            'name' => 'Staff',
            'key' => 'staff',
            'permissions' => [],
            'is_system' => true,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
