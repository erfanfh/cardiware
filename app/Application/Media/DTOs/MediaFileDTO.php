<?php

namespace App\Application\Media\DTOs;

class MediaFileDTO
{
    public function __construct(
        public ?int    $id,
        public ?string $path
    )
    {
    }
}
