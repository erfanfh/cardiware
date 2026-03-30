<?php

namespace Database\Seeders\Feature;

use App\Domain\Feature\Enums\FeatureKeyEnum;
use App\Domain\Feature\Enums\FrontBadgeOptionEnum;
use App\Domain\Feature\Enums\ValueTypeEnum;
use App\Domain\Feature\Models\FeatureDefinition;
use Illuminate\Database\Seeder;

class FeatureDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $definitions = [
            [
                'key' => FeatureKeyEnum::IS_FEATURED->value,
                'title' => 'نوشته ویژه',
                'value_type' => ValueTypeEnum::FLAG,
                'allowed_values' => null,
            ],
            [
                'key' => FeatureKeyEnum::IS_VIP->value,
                'title' => 'نوشته VIP',
                'value_type' => ValueTypeEnum::FLAG,
                'allowed_values' => null,
            ],
            [
                'key' => FeatureKeyEnum::IS_UPDATED->value,
                'title' => 'نوشته به‌روزرسانی شده',
                'value_type' => ValueTypeEnum::FLAG,
                'allowed_values' => null,
            ],
            [
                'key' => FeatureKeyEnum::FRONT_BADGE->value,
                'title' => 'لیبل نوشته',
                'value_type' => ValueTypeEnum::STRING,
                'allowed_values' => FrontBadgeOptionEnum::values(),
            ],
        ];

        foreach ($definitions as $definition) {
            FeatureDefinition::query()->updateOrCreate(
                [
                    'key' => $definition['key'],
                ],
                [
                    'title' => $definition['title'],
                    'value_type' => $definition['value_type'],
                    'allowed_values' => $definition['allowed_values'],
                ]
            );
        }
    }
}
