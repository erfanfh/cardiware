<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Application\Taxonomy\Actions\CreateTaxonomyTermAction;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Taxonomy\QuickCreateCategoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class VideoQuickCreateCategoryController extends Controller
{
    public function __invoke(QuickCreateCategoryRequest $request, CreateTaxonomyTermAction $action): JsonResponse
    {
        Gate::authorize('video.category');
        $category = $action->execute(TaxonomyKeyEnum::VIDEO_CATEGORY, $request->validated());
        return response()->json(['message' => 'دسته با موفقیت ایجاد شد.', 'item' => $category]);
    }
}
