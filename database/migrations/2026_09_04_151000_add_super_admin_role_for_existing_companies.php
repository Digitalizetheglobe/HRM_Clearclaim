<?php

use App\Models\Utility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up()
    {
        $companyIds = DB::table('users')->where('type', 'company')->pluck('id');

        $roleCompanyIds = DB::table('roles')
            ->where('guard_name', 'web')
            ->where('created_by', '>', 0)
            ->distinct()
            ->pluck('created_by');

        $ids = $companyIds->merge($roleCompanyIds)->unique()->filter();

        foreach ($ids as $companyId) {
            $role = Role::where('name', 'super-admin')
                ->where('created_by', $companyId)
                ->where('guard_name', 'web')
                ->first();

            if (empty($role)) {
                $role = new Role();
                $role->name = 'super-admin';
                $role->guard_name = 'web';
                $role->created_by = $companyId;
                $role->save();
            }

            $companyRole = Role::where('name', 'company')->where('guard_name', 'web')->orderBy('id')->first();
            $permissions = $companyRole ? $companyRole->permissions : Permission::all();
            $role->syncPermissions($permissions);

            if (method_exists(Utility::class, 'syncSuperAdminRolePermissions')) {
                Utility::syncSuperAdminRolePermissions($companyId);
            }
        }
    }

    public function down()
    {
        Role::where('name', 'super-admin')->where('guard_name', 'web')->delete();
    }
};
