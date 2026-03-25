<?php

namespace App\Http\Controllers\Admin\Slug;

use App\Application\Slug\Actions\CheckSlugAvailabilityAction;
use App\Application\Slug\DTOs\CheckSlugDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Slug\CheckSlugRequest;
use Illuminate\Http\JsonResponse;

class SlugController extends Controller
{
    /**
     * Check whether a slug is available for the provided scope.
     */
    public function check(
        CheckSlugRequest            $request,
        CheckSlugAvailabilityAction $action,
    ): JsonResponse
    {
        $dto = CheckSlugDTO::fromArray($request->validated());
        $result = $action->execute($dto);

        return response()->json($result->toArray());
    }
}
