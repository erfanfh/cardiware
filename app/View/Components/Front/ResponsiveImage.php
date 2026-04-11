<?php

namespace App\View\Components\Front;

use App\Application\Media\Services\MediaImagePresenter;
use App\Domain\Media\Enums\MediaFileVariantEnum;
use App\Domain\Media\Models\MediaFile;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ResponsiveImage extends Component
{
    public ?MediaImagePresenter $image;

    public function __construct(
        public ?MediaFile $media = null,
        public ?string    $alt = null,
        public string     $variant = 'medium',
        public ?string    $sizes = null,
        public string     $class = '',
        public bool       $lazy = true,
        public bool       $async = true,
        public ?string    $fallback = null,
        public bool       $showPlaceholder = false,
        public ?int       $width = null,
        public ?int       $height = null,
        public ?string    $fetchpriority = null,
    )
    {
        $this->image = $media ? new MediaImagePresenter($media) : null;
    }

    public function render(): View|Closure|string
    {
        return view('components.front.responsive-image');
    }

    public function src(): ?string
    {
        if (!$this->image) {
            return $this->fallback;
        }

        return $this->image->url($this->variantEnum()) ?? $this->fallback;
    }

    public function srcset(): ?string
    {
        if (!$this->image) {
            return null;
        }

        $srcset = $this->image->srcSet();

        return $srcset !== '' ? $srcset : null;
    }

    public function resolvedSizes(): ?string
    {
        return $this->sizes ?: $this->defaultSizes();
    }

    public function loading(): string
    {
        return $this->lazy ? 'lazy' : 'eager';
    }

    public function decoding(): string
    {
        return $this->async ? 'async' : 'auto';
    }

    public function variantEnum(): MediaFileVariantEnum
    {
        return MediaFileVariantEnum::tryFrom($this->variant) ?? MediaFileVariantEnum::MEDIUM;
    }

    private function defaultSizes(): string
    {
        return match ($this->variantEnum()) {
            MediaFileVariantEnum::THUMBNAIL => '150px',
            MediaFileVariantEnum::MEDIUM => '(max-width: 640px) 100vw, 300px',
            MediaFileVariantEnum::MEDIUM_LARGE => '(max-width: 768px) 100vw, 768px',
            MediaFileVariantEnum::LARGE => '100vw',
            MediaFileVariantEnum::ORIGINAL => '100vw',
        };
    }
}
