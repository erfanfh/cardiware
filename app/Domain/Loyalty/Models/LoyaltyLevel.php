<?php

namespace App\Domain\Loyalty\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LoyaltyLevel extends Model
{
    protected $fillable = [
        'title',
        'badge',
        'min_xp',
        'reward_points',
        'purchase_multiplier',
        'color_key',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'min_xp' => 'integer',
        'reward_points' => 'integer',
        'purchase_multiplier' => 'float',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('min_xp');
    }

    /**
     * رنگ‌های ثابت UI برای هر سطح؛ کلید از پنل مدیریت می‌آید.
     *
     * @return array{chip: string, text: string, ring: string, bar: string, frame: string}
     */
    public function colorClasses(): array
    {
        return match ($this->color_key) {
            'slate' => [
                'chip' => 'border-slate-300/60 bg-slate-100 text-slate-700 dark:border-slate-400/30 dark:bg-slate-400/10 dark:text-slate-300',
                'text' => 'text-slate-600 dark:text-slate-300',
                'ring' => 'from-slate-400 to-slate-600',
                'bar' => 'from-slate-400 to-slate-600',
                // قاب آواتار در دیدگاه‌ها و پروفایل؛ سطح بالاتر = قاب پررنگ‌تر
                'frame' => 'ring-2 ring-slate-400 ring-offset-2 ring-offset-white dark:ring-offset-slate-900',
            ],
            'emerald' => [
                'chip' => 'border-emerald-300/60 bg-emerald-100/70 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300',
                'text' => 'text-emerald-600 dark:text-emerald-300',
                'ring' => 'from-emerald-400 to-emerald-600',
                'bar' => 'from-emerald-400 to-emerald-600',
                // قاب آواتار در دیدگاه‌ها و پروفایل؛ سطح بالاتر = قاب پررنگ‌تر
                'frame' => 'ring-2 ring-emerald-400 ring-offset-2 ring-offset-white dark:ring-offset-slate-900',
            ],
            'cyan' => [
                'chip' => 'border-cyan-300/60 bg-cyan-100/70 text-cyan-800 dark:border-cyan-400/30 dark:bg-cyan-500/10 dark:text-cyan-300',
                'text' => 'text-cyan-600 dark:text-cyan-300',
                'ring' => 'from-cyan-300 to-sky-500',
                'bar' => 'from-cyan-300 to-sky-500',
                // قاب آواتار در دیدگاه‌ها و پروفایل؛ سطح بالاتر = قاب پررنگ‌تر
                'frame' => 'ring-2 ring-cyan-400 ring-offset-2 ring-offset-white dark:ring-offset-slate-900',
            ],
            'amber' => [
                'chip' => 'border-amber-300/60 bg-amber-100/70 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300',
                'text' => 'text-amber-600 dark:text-amber-300',
                'ring' => 'from-amber-400 to-amber-600',
                'bar' => 'from-amber-400 to-amber-600',
                // قاب آواتار در دیدگاه‌ها و پروفایل؛ سطح بالاتر = قاب پررنگ‌تر
                'frame' => 'ring-2 ring-amber-400 ring-offset-2 ring-offset-white dark:ring-offset-slate-900',
            ],
            'violet' => [
                'chip' => 'border-violet-300/60 bg-violet-100/70 text-violet-800 dark:border-violet-500/30 dark:bg-violet-500/10 dark:text-violet-300',
                'text' => 'text-violet-600 dark:text-violet-300',
                'ring' => 'from-violet-400 to-purple-600',
                'bar' => 'from-violet-400 to-purple-600',
                // قاب آواتار در دیدگاه‌ها و پروفایل؛ سطح بالاتر = قاب پررنگ‌تر
                'frame' => 'ring-2 ring-violet-500 ring-offset-2 ring-offset-white shadow-[0_0_12px_rgba(139,92,246,0.35)] dark:ring-offset-slate-900',
            ],
            'rose' => [
                'chip' => 'border-rose-300/60 bg-rose-100/70 text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300',
                'text' => 'text-rose-600 dark:text-rose-300',
                'ring' => 'from-rose-400 to-rose-600',
                'bar' => 'from-rose-400 to-rose-600',
                // قاب آواتار در دیدگاه‌ها و پروفایل؛ سطح بالاتر = قاب پررنگ‌تر
                'frame' => 'ring-2 ring-rose-500 ring-offset-2 ring-offset-white shadow-[0_0_14px_rgba(244,63,94,0.4)] dark:ring-offset-slate-900',
            ],
            default => [
                'chip' => 'border-brand-300/60 bg-brand-100/70 text-brand-800 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300',
                'text' => 'text-brand-600 dark:text-brand-300',
                'ring' => 'from-brand-400 to-brand-600',
                'bar' => 'from-brand-400 to-brand-600',
                // قاب آواتار در دیدگاه‌ها و پروفایل؛ سطح بالاتر = قاب پررنگ‌تر
                'frame' => 'ring-2 ring-brand-400 ring-offset-2 ring-offset-white dark:ring-offset-slate-900',
            ],
        };
    }
}
