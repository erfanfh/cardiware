<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Application\Taxonomy\Actions\CreateCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Taxonomy\QuickCreateCategoryRequest;
use Illuminate\Http\JsonResponse;
use Throwable;

class QuickCreateCategoryController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @throws Throwable
     */
    public function __invoke(
        QuickCreateCategoryRequest $request,
        CreateCategoryAction       $action
    ): JsonResponse
    {
        $category = $action->execute($request->validated());

        return response()->json([
            'message' => 'دسته با موفقیت ایجاد شد.',
            'item' => $category,
        ]);
    }
}
