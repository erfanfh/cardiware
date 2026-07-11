<?php

namespace App\Http\Controllers\Api\V1\Download;

use App\Application\Commerce\Actions\Access\PreparePurchaseAccessFileDownloadAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrepareDownloadController extends Controller
{
    /**
     * Prepare file download
     *
     * Validates the purchase access, verifies that the selected file belongs
     * to the purchased sale item, checks the per-file download limit,
     * creates a download record, and returns the file URL.
     *
     * Each successful call consumes one download from the selected file's limit.
     *
     * @group Downloads
     *
     * @urlParam purchaseAccess integer required The purchase access ID. Example: 145
     * @urlParam file integer required The product sale item file ID. Example: 81
     *
     * @responseFile status=200 scenario="Download prepared successfully" storage/responses/api/v1/downloads/prepare.success.json
     * @responseFile status=401 scenario="Unauthenticated" storage/responses/api/v1/errors/unauthenticated.json
     * @responseFile status=403 scenario="Download limit exceeded" storage/responses/api/v1/errors/download-limit-exceeded.json
     * @responseFile status=403 scenario="Purchase access is not usable" storage/responses/api/v1/errors/purchase-access-not-usable.json
     * @responseFile status=404 scenario="File or purchase access not found" storage/responses/api/v1/errors/not-found.json
     * @responseFile status=503 scenario="Download file unavailable" storage/responses/api/v1/errors/download-file-unavailable.json
     */
    public function __invoke(
        Request                                 $request,
        int                                     $purchaseAccess,
        int                                     $file,
        PreparePurchaseAccessFileDownloadAction $action,
    ): JsonResponse
    {
        $result = $action->execute(
            user: $request->user(),
            purchaseAccessId: $purchaseAccess,
            fileId: $file,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json([
            'message' => 'Download prepared successfully.',
            'data' => $result,
        ]);
    }
}
