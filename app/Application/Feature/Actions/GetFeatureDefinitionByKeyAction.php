<?php

namespace App\Application\Feature\Actions;

use App\Domain\Feature\Enums\FeatureKeyEnum;
use App\Domain\Feature\Models\FeatureDefinition;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetFeatureDefinitionByKeyAction
{
    /**
     * @param FeatureKeyEnum $key
     * @return FeatureDefinition
     */
    public function execute(FeatureKeyEnum $key): FeatureDefinition
    {
        $definition = FeatureDefinition::query()
            ->where('key', $key->value)
            ->first();

        if ($definition === null) {
            throw new ModelNotFoundException(sprintf(
                'Feature definition with key [%s] was not found.',
                $key->value
            ));
        }

        return $definition;
    }
}
