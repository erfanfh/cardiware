<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Application\Launcher\Actions\GetLauncherCatalogAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Launcher\LauncherCatalogRequest;
use App\Http\Resources\Api\V1\Launcher\LauncherCatalogItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LauncherCatalogController extends Controller
{
    public function index(
        LauncherCatalogRequest $request,
        GetLauncherCatalogAction $action,
    ): AnonymousResourceCollection {
        return LauncherCatalogItemResource::collection(
            $action->execute(
                $request->user(),
                $request->validated('search'),
                $request->perPage(),
            )
        );
    }

    public function show(
        Request $request,
        int $saleItem,
        GetLauncherCatalogAction $action,
    ): LauncherCatalogItemResource {
        return LauncherCatalogItemResource::make(
            $action->find($request->user(), $saleItem)
        );
    }
}
