<?php

namespace App\Http\Controllers\Api\V1\Library;

use App\Application\Commerce\Actions\Access\GetUserLibraryAction;
use App\Application\Commerce\Actions\Access\GetUserLibraryItemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Library\LibraryIndexRequest;
use App\Http\Resources\Api\V1\Library\LibraryItemResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    /**
     * Get user library
     *
     * Returns the authenticated user's currently usable purchase accesses.
     *
     * Each file includes its own download usage statistics. The download limit
     * applies independently to each file within the purchase access.
     *
     * @group Library
     *
     * @responseFile status=200 scenario="Library returned successfully" storage/responses/api/v1/library/index.success.json
     * @responseFile status=401 scenario="Unauthenticated" storage/responses/api/v1/errors/unauthenticated.json
     */
    public function index(
        LibraryIndexRequest  $request,
        GetUserLibraryAction $action,
    ): JsonResponse
    {
        $purchaseAccesses = $action->execute(
            user: $request->user(),
            perPage: $request->perPage(),
        );

        return LibraryItemResource::collection($purchaseAccesses)
            ->response()
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    /**
     * Get library item
     *
     * Returns one purchase access belonging to the authenticated user,
     * including its product, sale item, files, and download statistics.
     *
     * A `404` response is returned when the access does not exist,
     * is not usable, or does not belong to the authenticated user.
     *
     * @group Library
     *
     * @urlParam purchaseAccess integer required The purchase access ID. Example: 145
     *
     * @responseFile status=200 scenario="Library item returned successfully" storage/responses/api/v1/library/show.success.json
     * @responseFile status=401 scenario="Unauthenticated" storage/responses/api/v1/errors/unauthenticated.json
     * @responseFile status=404 scenario="Purchase access not found" storage/responses/api/v1/errors/not-found.json
     */
    public function show(
        Request                  $request,
        int                      $purchaseAccess,
        GetUserLibraryItemAction $action,
    ): JsonResponse
    {
        $access = $action->execute(
            user: $request->user(),
            purchaseAccessId: $purchaseAccess,
        );

        return LibraryItemResource::make($access)
            ->response()
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }
}
