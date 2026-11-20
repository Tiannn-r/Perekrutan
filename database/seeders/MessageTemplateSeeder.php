<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use Illuminate\Database\Seeder;

class MessageTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = MessageTemplate::defaultTemplates();

        foreach ($defaults as $key => $data) {
            MessageTemplate::updateOrCreate(
                ['key' => $key],
                $data
            );
        }
    }
}
