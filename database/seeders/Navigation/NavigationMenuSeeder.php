<?php

namespace Database\Seeders\Navigation;

use App\Domain\Navigation\Models\NavigationMenu;
use Illuminate\Database\Seeder;

class NavigationMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            [
                'slug' => 'header',
                'title' => 'هدر سایت',
            ]
        ];

        $items = [];

        foreach ($menus as $menu) {
            $items[] = NavigationMenu::updateOrCreate([
                'slug' => $menu['slug'],
            ], [
                'title' => $menu['title'],
            ])->id;
        }

        NavigationMenu::whereNotIn('id', $items)->delete();
    }
}
