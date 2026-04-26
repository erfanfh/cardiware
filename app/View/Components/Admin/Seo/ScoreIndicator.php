<?php

namespace App\View\Components\Admin\Seo;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ScoreIndicator extends Component
{
    public function __construct(
        public ?int $score = null,
    )
    {
    }

    public function viewData(): array
    {
        $score = $this->score;

        if ($score === null) {
            return [
                'label' => 'تحلیل نشده',
                'color' => 'bg-slate-400',
                'ring' => 'ring-slate-200',
                'pulse' => false,
            ];
        }

        return match (true) {
            $score >= 80 => [
                'label' => 'عالی',
                'color' => 'bg-emerald-500',
                'ring' => 'ring-emerald-100',
                'pulse' => true,
            ],

            $score >= 60 => [
                'label' => 'خوب',
                'color' => 'bg-yellow-400',
                'ring' => 'ring-yellow-100',
                'pulse' => false,
            ],

            $score >= 40 => [
                'label' => 'نیازمند بهبود',
                'color' => 'bg-orange-400',
                'ring' => 'ring-orange-100',
                'pulse' => false,
            ],

            default => [
                'label' => 'ضعیف',
                'color' => 'bg-rose-500',
                'ring' => 'ring-rose-100',
                'pulse' => false,
            ],
        };
    }

    public function render(): View|Closure|string
    {
        return view('components.admin.seo.score-indicator', $this->viewData());
    }
}
