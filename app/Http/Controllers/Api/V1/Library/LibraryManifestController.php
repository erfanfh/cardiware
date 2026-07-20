<?php

namespace App\Http\Controllers\Api\V1\Library;

use App\Application\Commerce\Actions\Access\GetUserLibraryManifestAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryManifestController extends Controller
{
    /**
     * Get library item manifest
     *
     * Returns the installation, launch, capability, and file manifest
     * for a purchase access belonging to the authenticated user.
     *
     * The response never contains direct download URLs. Each file URL
     * must be resolved separately through the download preparation API.
     *
     * @group Library
     *
     * @urlParam purchaseAccess integer required The purchase access ID. Example: 145
     *
     * @responseFile status=200 scenario="Manifest returned successfully" storage/responses/api/v1/library/manifest.success.json
     * @responseFile status=401 scenario="Unauthenticated" storage/responses/api/v1/errors/unauthenticated.json
     * @responseFile status=403 scenario="Purchase access is not usable" storage/responses/api/v1/errors/purchase-access-not-usable.json
     * @responseFile status=404 scenario="Purchase access not found" storage/responses/api/v1/errors/not-found.json
     * @responseFile status=503 scenario="Manifest unavailable" storage/responses/api/v1/errors/product-manifest-unavailable.json
     */
    public function __invoke(
        Request                      $request,
        int                          $purchaseAccess,
        GetUserLibraryManifestAction $action,
    ): JsonResponse
    {
        return response()->json([
            'data' => $action->execute(
                user: $request->user(),
                purchaseAccessId: $purchaseAccess,
            ),
        ]);
    }
}
