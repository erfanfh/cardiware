<?php

namespace App\Application\Slug\Actions;

use App\Application\Slug\DTOs\CheckSlugDTO;
use App\Application\Slug\DTOs\SlugCheckResultDTO;
use App\Application\Slug\Services\SlugManager;

class CheckSlugAvailabilityAction
{
    public function __construct(
        private readonly SlugManager $slugManager,
    )
    {
    }

    /**
     * Execute the slug availability check workflow.
     */
    public function execute(CheckSlugDTO $dto): SlugCheckResultDTO
    {
        return $this->slugManager->check($dto);
    }
}
