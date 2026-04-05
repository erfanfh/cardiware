<?php

namespace App\Application\Discussion\DTOs;

use App\Domain\Discussion\Enums\CommentSortEnum;
use Illuminate\Database\Eloquent\Model;

class GetCommentsForCommentableDataDTO
{
    public function __construct(
        public Model           $commentable,
        public CommentSortEnum $sort = CommentSortEnum::NEWEST,
        public int             $perPage = 10,
        public bool            $onlyApproved = true,
    )
    {
    }
}
