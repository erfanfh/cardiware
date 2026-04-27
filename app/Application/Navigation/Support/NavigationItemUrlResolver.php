<?php

namespace App\Application\Navigation\Support;

use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\Navigation\Enums\NavigationItemTypeEnum;
use App\Domain\Navigation\Models\NavigationItem;
use App\Domain\Project\Models\Project;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class NavigationItemUrlResolver
{
    public function resolveForItem(NavigationItem $item): ?string
    {
        if ($item->type === NavigationItemTypeEnum::CUSTOM_LINK) {
            return $item->url;
        }

        if (!$item->reference) {
            return null;
        }

        return match ($item->type) {
            NavigationItemTypeEnum::CATEGORY => $this->resolveCategoryUrl($item->reference),
            NavigationItemTypeEnum::TAG => $this->resolveTagUrl($item->reference),
            NavigationItemTypeEnum::POST => $this->resolvePostUrl($item->reference),
            NavigationItemTypeEnum::PAGE => $this->resolvePageUrl($item->reference),
            NavigationItemTypeEnum::PROJECT => $this->resolveProjectUrl($item->reference),
            default => null,
        };
    }

    public function assertResolvable(
        string $type,
        int    $referenceId,
        string $referenceType,
    ): void
    {
        if (!class_exists($referenceType)) {
            throw ValidationException::withMessages([
                'items_payload' => 'کلاس مرجع یکی از آیتم‌های فهرست معتبر نیست.',
            ]);
        }

        /** @var class-string<Model> $referenceType */
        $model = $referenceType::query()->find($referenceId);

        if (!$model) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع یکی از آیتم‌های فهرست یافت نشد.',
            ]);
        }

        match ($type) {
            NavigationItemTypeEnum::CATEGORY->value => $this->ensureTaxonomyTerm($model, 'مرجع دسته‌بندی معتبر نیست.'),
            NavigationItemTypeEnum::TAG->value => $this->ensureTaxonomyTerm($model, 'مرجع برچسب معتبر نیست.'),
            NavigationItemTypeEnum::POST->value => $this->ensurePost($model),
            NavigationItemTypeEnum::PAGE->value => $this->ensurePage($model),
            NavigationItemTypeEnum::PROJECT->value => $this->ensureProject($model),
            default => throw ValidationException::withMessages([
                'items_payload' => 'امکان ساخت لینک برای یکی از آیتم‌های فهرست وجود ندارد.',
            ]),
        };
    }

    protected function resolveCategoryUrl(Model $model): string
    {
        $this->ensureTaxonomyTerm($model, 'مرجع دسته‌بندی معتبر نیست.');

        return front_route('posts.categories', $model);
    }

    protected function resolveTagUrl(Model $model): string
    {
        $this->ensureTaxonomyTerm($model, 'مرجع برچسب معتبر نیست.');

        return front_route('posts.tags', $model);
    }

    protected function resolvePostUrl(Model $model): string
    {
        $this->ensurePost($model);

        return front_route('contents.detail', [
            'slug' => $model->slug,
        ]);
    }

    protected function resolvePageUrl(Model $model): string
    {
        $this->ensurePage($model);

        return front_route('contents.detail', [
            'slug' => $model->slug,
        ]);
    }

    protected function resolveProjectUrl(Model $model): string
    {
        $this->ensureProject($model);

        return front_route('projects.detail', $model);
    }

    protected function ensureTaxonomyTerm(Model $model, string $message): void
    {
        if (!$model instanceof TaxonomyTerm) {
            throw ValidationException::withMessages([
                'items_payload' => $message,
            ]);
        }
    }

    protected function ensurePost(Model $model): void
    {
        if (!$model instanceof Post) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع نوشته معتبر نیست.',
            ]);
        }
    }

    protected function ensurePage(Model $model): void
    {
        if (!$model instanceof Page) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع برگه معتبر نیست.',
            ]);
        }
    }

    protected function ensureProject(Model $model): void
    {
        if (!$model instanceof Project) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع پروژه معتبر نیست.',
            ]);
        }
    }
}
