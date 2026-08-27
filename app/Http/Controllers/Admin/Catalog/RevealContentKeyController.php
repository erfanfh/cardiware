<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Application\Catalog\Actions\ProductInformation\RevealContentKeyAction;
use App\Http\Requests\Admin\Catalog\RevealContentKeyRequest;
use Illuminate\Http\JsonResponse;

class RevealContentKeyController
{
    public function __invoke(
        RevealContentKeyRequest $request,
        RevealContentKeyAction  $revealContentKeyAction,
    ): JsonResponse
    {
        $result = $revealContentKeyAction->execute(
            saleItemId: (int)$request->input('sale_item_id'),
            vaultPassword: (string)$request->input('vault_password'),
            adminUserId: (int)$request->user()->getKey(),
        );

        if ($result['ok'] !== true) {
            return response()->json($result, 422);
        }

        // A single global grant is enough here: only someone who already knows the
        // vault password can reach this branch at all, so extending trust to "any
        // content-key field on this admin session, for a short window" matches how
        // an owner actually works -- unlock once, edit several products.
        $request->session()->put(
            'content_key_vault_unlocked_until',
            now()->addMinutes((int)config('launcher_security.content_key_vault_unlock_minutes', 10))->timestamp
        );

        return response()->json($result);
    }
}
