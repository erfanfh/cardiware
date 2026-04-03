<?php

namespace Database\Seeders\Identity;

use App\Domain\Identity\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'post' => [
                'list' => 'لیست نوشته ها',
                'create' => 'ایجاد نوشته',
                'edit' => 'ویرایش نوشته',
                'delete' => 'حذف نوشته',
            ],
            'project' => [
                'list' => 'لیست پروژه ها',
                'create' => 'ایجاد پروژه',
                'edit' => 'ویرایش پروژه',
                'delete' => 'حذف پروژه',
            ],
        ];

        $permissionIds = [];

        foreach ($permissions as $group => $items) {
            foreach ($items as $ability => $label) {
                $permission = Permission::updateOrCreate([
                    'group' => $group,
                    'ability' => $ability,
                ], [
                    'label' => $label,
                ]);

                $permissionIds[] = $permission->id;
            }
        }

        Permission::whereNotIn('id', $permissionIds)->delete();
    }
}
