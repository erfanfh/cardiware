<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Admin\AdminSearchController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * جست‌وجوی دسته‌های نوشته برای انتخابگرهای پنل.
 *
 * تفاوت با کنترلر پایه: دسته‌های «مادر» هم برگردانده می‌شوند و برچسب هر نتیجه
 * مسیر کاملش را نشان می‌دهد («ورزش › پچ‌های PS21») تا چند دسته‌ی هم‌نام از هم
 * قابل تفکیک باشند. جست‌وجو روی عنوان، نامک و مسیر انجام می‌شود.
 */
class PostCategorySearchController extends AdminSearchController
{
    protected function modelClass(): string
    {
        return TaxonomyTerm::class;
    }

    protected function permissionName(): string
    {
        return 'category.manage';
    }

    protected function baseQuery(): Builder
    {
        return parent::baseQuery()
            ->where('taxonomy', TaxonomyKeyEnum::POST_CATEGORY);
    }

    /**
     * @return array<int, string>
     */
    protected function selectColumns(): array
    {
        return ['id', 'title', 'slug', 'path', 'parent_id'];
    }

    protected function applySearchConditions(Builder $builder, string $query): void
    {
        $builder->where(function (Builder $inner) use ($query): void {
            $inner->where('title', 'like', '%' . $query . '%')
                ->orWhere('slug', 'like', '%' . $query . '%')
                ->orWhere('path', 'like', '%' . $query . '%');

            if (ctype_digit($query)) {
                $inner->orWhere('id', (int) $query);
            }
        });
    }

    protected function formatLabel(Model $model): string
    {
        /** @var TaxonomyTerm $model */
        return $this->hierarchyLabel($model) . " ({$model->getKey()})";
    }

    /**
     * برچسب سلسله‌مراتبی؛ دسته‌های بدون والد با پیشوند «دسته مادر» مشخص می‌شوند.
     */
    private function hierarchyLabel(TaxonomyTerm $term): string
    {
        $parentTitle = $term->parent_id
            ? TaxonomyTerm::query()->whereKey($term->parent_id)->value('title')
            : null;

        if ($parentTitle) {
            return $parentTitle . ' › ' . $term->title;
        }

        return $term->title . ' — دسته مادر';
    }
}
