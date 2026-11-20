<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = Setting::defaultSettings();

        foreach ($defaults as $key => $item) {
            Setting::updateOrCreate(
                ['key' => $key],
                $item
            );
        }
    }
}
