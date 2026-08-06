<?php

namespace App\Http\Controllers\Admin\Icon;

use App\Application\Icon\Actions\ResolveIconDetailsAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IconLookupController
{
    public function __invoke(Request $request, ResolveIconDetailsAction $resolveIconDetails): JsonResponse
    {
        $validated = $request->validate([
            'names' => ['required', 'array', 'max:100'],
            'names.*' => ['required', 'string', 'max:255'],
        ]);

        return response()->json([
            'icons' => $resolveIconDetails->executeMany($validated['names']),
        ]);
    }
}
