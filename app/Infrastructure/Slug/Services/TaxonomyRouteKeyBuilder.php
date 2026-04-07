<?php

namespace App\Infrastructure\Slug\Services;

use Illuminate\Database\Eloquent\Model;

class TaxonomyRouteKeyBuilder
{
    /**
     * Build the final route key for the taxonomy model.
     *
     * For now, this implementation returns the provided slug as-is.
     * It can later be extended to build hierarchical paths based on
     * the parent term and taxonomy tree.
     */
    public function build(Model $model, string $finalSlug): string
    {
        if ($model->parent_id)
            return app(TaxonomyRouteKeyBuilder::class)->build($model->parent, $model->parent->slug) . '/' . $finalSlug;

        return $finalSlug;
    }
}
