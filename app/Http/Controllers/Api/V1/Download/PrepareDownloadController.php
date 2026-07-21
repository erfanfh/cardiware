<?php

namespace App\Http\Controllers\Api\V1\Download;

use App\Application\Commerce\Actions\Access\PreparePurchaseAccessFileDownloadAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Download\PrepareDownloadRequest;
use Illuminate\Http\JsonResponse;

class PrepareDownloadController extends Controller
{
    /**
     * Prepare file download
     *
     * Creates or reuses an idempotent download session and returns
     * a temporary file URL.
     *
     * A new download session consumes one download from the selected
     * file's limit. Retrying the same session does not consume another
     * download.
     *
     * @group Downloads
     *
     * @urlParam purchaseAccess integer required The purchase access ID. Example: 145
     * @urlParam file integer required The product sale item file ID. Example: 81
     *
     * @bodyParam download_session_id string required A UUID generated once per user-initiated download operation. The same UUID must be reused for retries. Example: c0429807-f641-47b5-a619-9c14ac858d97
     *
     * @responseFile status=200 scenario="Download prepared successfully" storage/responses/api/v1/downloads/prepare.success.json
     * @responseFile status=401 scenario="Unauthenticated" storage/responses/api/v1/errors/unauthenticated.json
     * @responseFile status=403 scenario="Download limit exceeded" storage/responses/api/v1/errors/download-limit-exceeded.json
     * @responseFile status=403 scenario="Purchase access is not usable" storage/responses/api/v1/errors/purchase-access-not-usable.json
     * @responseFile status=404 scenario="File or purchase access not found" storage/responses/api/v1/errors/not-found.json
     * @responseFile status=409 scenario="Download session expired" storage/responses/api/v1/errors/download-session-expired.json
     * @responseFile status=422 scenario="Validation failed" storage/responses/api/v1/errors/validation.json
     * @responseFile status=503 scenario="Download file unavailable" storage/responses/api/v1/errors/download-file-unavailable.json
     */
    public function __invoke(
        PrepareDownloadRequest                  $request,
        int                                     $purchaseAccess,
        int                                     $file,
        PreparePurchaseAccessFileDownloadAction $action,
    ): JsonResponse
    {
        $result = $action->execute(
            user: $request->user(),
            purchaseAccessId: $purchaseAccess,
            fileId: $file,
            downloadSessionId: $request->string('download_session_id')->toString(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json([
            'message' => 'Download prepared successfully.',
            'data' => $result,
        ]);
    }
}
