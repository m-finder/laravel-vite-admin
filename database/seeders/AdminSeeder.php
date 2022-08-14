<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //清空表 然后再填充数据
        /**
         * @var Admin $admin
         * @var Role $role
         */
        $admin = Admin::query()->create([
            'name' => 'admin',
            'email' => 'admin@qingbotech.com',
            'phone' => '18016391011',
            'password' => bcrypt('abc123'),
        ]);

        $role = Role::create([
            'name' => '超级管理员',
            'guard_name' => 'custom'
        ]);

        $admin->assignRole($role);
    }
}
