<?php

namespace App\Application\Discussion\Support;

use App\Application\Discussion\DTOs\CommentReactionIdentityDTO;
use Illuminate\Support\Facades\Auth;

class CommentReactionIdentityResolver
{
    public function resolve(): CommentReactionIdentityDTO
    {
        $userId = Auth::id();

        if ($userId) {
            return new CommentReactionIdentityDTO(
                userId: (int)$userId,
                ipHash: null,
            );
        }

        return new CommentReactionIdentityDTO(
            userId: null,
            ipHash: $this->resolveIpHash(),
        );
    }

    public function rateLimitIdentity(): string
    {
        if (Auth::check()) {
            return 'user:' . Auth::id();
        }

        return 'ip:' . $this->resolveIpHash();
    }

    private function resolveIpHash(): string
    {
        return hash_hmac(
            'sha256',
            (string)request()->ip(),
            (string)config('app.key'),
        );
    }
}
