<?php

namespace App\Console\Commands;

use App\Models\Utility;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EnsureSuperAdminRole extends Command
{
    protected $signature = 'hrm:ensure-super-admin-role';

    protected $description = 'Create the super-admin role for existing companies so it appears in Create User';

    public function handle()
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
                $this->info("Created super-admin role for company {$companyId}");
            } else {
                $this->info("super-admin role already exists for company {$companyId}");
            }

            $companyRole = Role::where('name', 'company')->where('guard_name', 'web')->orderBy('id')->first();
            $permissions = $companyRole ? $companyRole->permissions : Permission::all();
            $role->syncPermissions($permissions);

            if (method_exists(Utility::class, 'syncSuperAdminRolePermissions')) {
                Utility::syncSuperAdminRolePermissions($companyId);
            }
        }

        $this->info('Done. Open Staff > User > Create New User and select super-admin.');

        return Command::SUCCESS;
    }
}
