<?php

namespace App\Application\Navigation\Support;

use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\Navigation\Enums\NavigationItemTypeEnum;
use App\Domain\Project\Models\Project;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class NavigationItemUrlResolver
{
    public function resolve(
        string $type,
        int    $referenceId,
        string $referenceType,
    ): string
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

        return match ($type) {
            NavigationItemTypeEnum::CATEGORY->value => $this->resolveCategoryUrl($model),
            NavigationItemTypeEnum::TAG->value => $this->resolveTagUrl($model),
            NavigationItemTypeEnum::POST->value => $this->resolvePostUrl($model),
            NavigationItemTypeEnum::PAGE->value => $this->resolvePageUrl($model),
            NavigationItemTypeEnum::PROJECT->value => $this->resolveProjectUrl($model),
            default => throw ValidationException::withMessages([
                'items_payload' => 'امکان ساخت لینک برای یکی از آیتم‌های فهرست وجود ندارد.',
            ]),
        };
    }

    protected function resolveCategoryUrl(Model $model): string
    {
        if (!$model instanceof TaxonomyTerm) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع دسته‌بندی معتبر نیست.',
            ]);
        }

        return '/' . ltrim($model->path ?: $model->slug, '/');
    }

    protected function resolveTagUrl(Model $model): string
    {
        if (!$model instanceof TaxonomyTerm) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع برچسب معتبر نیست.',
            ]);
        }

        return '/' . ltrim($model->path ?: $model->slug, '/');
    }

    protected function resolvePostUrl(Model $model): string
    {
        if (!$model instanceof Post) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع نوشته معتبر نیست.',
            ]);
        }

        return '/' . ltrim($model->slug, '/');
    }

    protected function resolvePageUrl(Model $model): string
    {
        if (!$model instanceof Page) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع برگه معتبر نیست.',
            ]);
        }

        return '/' . ltrim($model->slug, '/');
    }

    protected function resolveProjectUrl(Model $model): string
    {
        if (!$model instanceof Project) {
            throw ValidationException::withMessages([
                'items_payload' => 'مرجع پروژه معتبر نیست.',
            ]);
        }

        return '/' . ltrim($model->slug, '/');
    }
}
