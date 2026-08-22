<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Application\Launcher\Actions\GetLauncherCatalogAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Launcher\LauncherCatalogRequest;
use App\Http\Resources\Api\V1\Launcher\LauncherCatalogItemResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LauncherCatalogController extends Controller
{
    public function index(
        LauncherCatalogRequest $request,
        GetLauncherCatalogAction $action,
    ): JsonResponse {
        return LauncherCatalogItemResource::collection(
            $action->execute(
                $request->user(),
                $request->validated('search'),
                $request->perPage(),
            )
        )->response()->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function show(
        Request $request,
        int $saleItem,
        GetLauncherCatalogAction $action,
    ): JsonResponse {
        return LauncherCatalogItemResource::make(
            $action->find($request->user(), $saleItem)
        )->response()->header('Cache-Control', 'private, no-store, max-age=0');
    }
}
