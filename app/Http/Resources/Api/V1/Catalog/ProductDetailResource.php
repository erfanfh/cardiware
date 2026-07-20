<?php

namespace App\Http\Resources\Api\V1\Catalog;

use App\Application\Catalog\Actions\ResolveCatalogProductPricingAction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pricing = app(ResolveCatalogProductPricingAction::class)->execute($this->saleItems);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'excerpt' => $this->excerpt,
            'body' => $this->body,

            'type' => $this->type->value,
            'content_type' => $this->content_type->value,
            'structure' => $this->structure->value,

            'web_url' => $this->url,

            'cover' => $this->picture
                ? [
                    'id' => $this->picture->id,
                    'url' => $this->picture->url,
                ]
                : null,

            'store' => $this->store
                ? [
                    'id' => $this->store->id,
                    'title' => $this->store->title,
                    'slug' => $this->store->slug,
                    'activity_field' => $this->store->activity_field,

                    'picture_url' => $this->store->picture?->url,
                ]
                : null,

            'pricing' => $pricing,

            'user_access' => [
                'authenticated' => (bool)$this->getAttribute('viewer_authenticated'),
                'is_purchased' => $this->getAttribute('is_purchased'),
                'has_usable_access' => $this->getAttribute('has_usable_access'),
            ],

            'categories' => $this->categories
                ->map(
                    static fn($category): array => [
                        'id' => $category->id,
                        'title' => $category->title,
                        'slug' => $category->slug,
                        'path' => $category->path,
                    ]
                )
                ->values(),

            'tags' => $this->tags
                ->map(
                    static fn($tag): array => [
                        'id' => $tag->id,
                        'title' => $tag->title,
                        'slug' => $tag->slug,
                        'path' => $tag->path,
                    ]
                )
                ->values(),

            'image_gallery' => $this->imageGallery
                ->map(
                    static fn($media): array => [
                        'id' => $media->id,
                        'title' => $media->title,
                        'url' => $media->mediaFile?->url,
                        'sort_order' => $media->sort_order,
                        'meta' => $media->meta,
                    ]
                )
                ->values(),

            'video_gallery' => $this->videoGallery
                ->map(
                    static fn($media): array => [
                        'id' => $media->id,
                        'title' => $media->title,
                        'url' => $media->mediaFile?->url,
                        'sort_order' => $media->sort_order,
                        'meta' => $media->meta,
                    ]
                )
                ->values(),

            'requirements' => $this->requirements
                ->map(
                    static fn($requirement): array => [
                        'id' => $requirement->id,
                        'title' => $requirement->title,
                        'value' => $requirement->value,
                        'sort_order' => $requirement->sort_order,
                    ]
                )
                ->values(),

            'installation_steps' => $this->installationSteps
                ->map(
                    static fn($step): array => [
                        'id' => $step->id,
                        'body' => $step->body,
                        'sort_order' => $step->sort_order,
                    ]
                )
                ->values(),

            'faqs' => $this->faqs
                ->map(
                    static fn($faq): array => [
                        'id' => $faq->id,
                        'question' => $faq->question,
                        'answer' => $faq->answer,
                        'sort_order' => $faq->sort_order,
                    ]
                )
                ->values(),

            'releases' => $this->releases
                ->map(
                    static fn($release): array => [
                        'id' => $release->id,
                        'title' => $release->title,
                        'excerpt' => $release->excerpt,

                        'released_at' =>
                            $release->released_at?->toDateString(),

                        'changes' => $release->releaseChanges
                            ->map(
                                static fn($change): array => [
                                    'id' => $change->id,
                                    'title' => $change->title,
                                    'is_special' => $change->is_special,
                                    'sort_order' => $change->sort_order,
                                ]
                            )
                            ->values(),
                    ]
                )
                ->values(),

            'information' => $this->informationEntries
                ->map(
                    static fn($entry): array => [
                        'field_id' => $entry->product_information_field_id,
                        'title' => $entry->field?->title,
                        'value' => $entry->value,
                    ]
                )
                ->values(),

            'attributes' => $this->attributeAssignments
                ->map(
                    static fn($assignment): array => [
                        'id' => $assignment->id,

                        'attribute' => [
                            'id' => $assignment->attribute?->id,
                            'title' => $assignment->attribute?->title,
                        ],

                        'is_variation_defining' =>
                            (bool)$assignment->is_variation_defining,

                        'values' => $assignment->assignmentValues
                            ->map(
                                static fn($assignmentValue): array => [
                                    'id' => $assignmentValue->value?->id,
                                    'title' => $assignmentValue->value?->title,
                                    'sort_order' =>
                                        $assignmentValue->sort_order,
                                ]
                            )
                            ->values(),
                    ]
                )
                ->values(),

            'sale_items' => ProductSaleItemResource::collection($this->saleItems),

            'addons' => $this->addons
                ->map(
                    static fn($addon): array => [
                        'id' => $addon->id,
                        'title' => $addon->title,
                        'description' => $addon->description,

                        'pricing' => [
                            'base_price' => (int)$addon->price,
                            'final_price' => (int)$addon->price,
                            'currency' => 'IRT',
                        ],

                        'applies_to_all_sale_items' => true,

                        'sort_order' => $addon->sort_order,
                        'metadata' => $addon->metadata,
                    ]
                )
                ->values(),

            'published_at' => $this->published_at?->toISOString(),
        ];
    }
}
