<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menu_items')->insert([
            'id' => 1,
            'title' => 'Dashboard',
            'path' => '/admin/dashboard',
            'icon' => 'DashboardIcon',
            'location' => 'main',
            'parent_id' => null,
            'order' => 0,
            'roles' => '["admin","guide"]',
            'created_at' => '2026-02-08 22:53:37',
            'updated_at' => '2026-02-08 22:53:37',
        ]);
    }
}
