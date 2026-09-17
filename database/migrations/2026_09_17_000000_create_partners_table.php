<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table): void {
            $table->id();
            $table->json('title')->nullable();
            $table->unsignedBigInteger('image_id')->nullable()->index();
            $table->string('url', 2048)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        foreach (['view', 'create', 'update', 'delete', 'export'] as $action) {
            $permission = Permission::findOrCreate('partners.'.$action, 'web');
            $roles = match ($action) {
                'view' => ['Owner', 'Editor', 'Viewer'],
                'delete' => ['Owner'],
                default => ['Owner', 'Editor'],
            };
            foreach (DB::table('roles')->whereIn('name', $roles)->where('guard_name', 'web')->pluck('id') as $roleId) {
                DB::table('role_has_permissions')->insertOrIgnore(['permission_id' => $permission->id, 'role_id' => $roleId]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
        $ids = DB::table('permissions')->where('guard_name', 'web')->whereIn('name', array_map(fn (string $action) => 'partners.'.$action, ['view', 'create', 'update', 'delete', 'export']))->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
