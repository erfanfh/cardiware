<?php

namespace App\Application\Content\Actions;

use App\Support\Landings;

final class GetProductLandingPageDataAction
{
    public function execute(string $slug): array
    {
        $locale = Landings::localeFromRequest();
        $data = Landings::resolve(Landings::product($slug), $locale);

        abort_if($data === null, 404);

        return compact('slug', 'data', 'locale');
    }
}
