<?php

namespace App\Http\Controllers\Api\V1\Download;

use App\Application\Commerce\Actions\Access\PreparePurchaseAccessFileDownloadAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrepareDownloadController extends Controller
{
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
            'message' => 'دانلود با موفقیت آماده شد.',
            'data' => $result,
        ]);
    }
}
