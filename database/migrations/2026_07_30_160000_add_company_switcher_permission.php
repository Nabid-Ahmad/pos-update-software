<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Clean up redundant Company Switcher permission row
        \DB::table('permission_role')->whereIn('permission_id', \DB::table('permissions')->where('slug_name', 'companies.switch')->pluck('id'))->delete();
        \DB::table('role_permissions')->whereIn('fk_permission_id', \DB::table('permissions')->where('slug_name', 'companies.switch')->pluck('id'))->delete();
        \DB::table('permissions')->where('slug_name', 'companies.switch')->delete();
    }

    public function down(): void
    {
    }
};
