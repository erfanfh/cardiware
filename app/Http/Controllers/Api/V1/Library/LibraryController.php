<?php

namespace App\Http\Controllers\Api\V1\Library;

use App\Application\Commerce\Actions\Access\GetUserLibraryAction;
use App\Application\Commerce\Actions\Access\GetUserLibraryItemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Library\LibraryIndexRequest;
use App\Http\Resources\Api\V1\Library\LibraryItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LibraryController extends Controller
{
    public function index(
        LibraryIndexRequest  $request,
        GetUserLibraryAction $action,
    ): AnonymousResourceCollection
    {
        $purchaseAccesses = $action->execute(
            user: $request->user(),
            perPage: $request->perPage(),
        );

        return LibraryItemResource::collection($purchaseAccesses);
    }

    public function show(
        Request                  $request,
        int                      $purchaseAccess,
        GetUserLibraryItemAction $action,
    ): LibraryItemResource
    {
        $access = $action->execute(
            user: $request->user(),
            purchaseAccessId: $purchaseAccess,
        );

        return LibraryItemResource::make($access);
    }
}
