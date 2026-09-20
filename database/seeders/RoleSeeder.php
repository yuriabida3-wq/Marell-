<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder {
    public function run(): void {
        foreach (['principal','dos','bursar','teacher','parent'] as $role) {
            Role::firstOrCreate(['name'=>$role, 'guard_name'=>'web']);
        }
    }
}
