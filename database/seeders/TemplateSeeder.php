<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        
        $templates = [
            [
                'name' => 'Template 1',
                'path' => 'client.template.business-plan.template',
                'type' => 'business-plan',
                'image_url' => 'assets/img/templates/business-plan/Template(1).svg',
                'is_default' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Template 2',
                'path' => 'client.template.business-plan.template2',
                'type' => 'business-plan',
                'image_url' => 'assets/img/templates/bplan/Template(2).svg',
                'is_default' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Template 3',
                'path' => 'client.template.business-plan.template3',
                'type' => 'business-plan',
                'image_url' => 'assets/img/templates/bplan/Template(3).svg',
                'is_default' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Template 4',
                'path' => 'client.template.business-plan.template4',
                'type' => 'business-plan',
                'image_url' => 'assets/img/templates/bplan/Template(4).svg',
                'is_default' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Template 5',
                'path' => 'client.template.business-plan.template5',
                'type' => 'business-plan',
                'image_url' => 'assets/img/templates/bplan/Template(5).svg',
                'is_default' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('templates')->insert($templates);
    }
}
