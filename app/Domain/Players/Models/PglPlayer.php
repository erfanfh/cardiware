<?php

namespace App\Domain\Players\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PglPlayer extends Model
{
    protected $table = 'pgl_players';

    protected $primaryKey = 'pid';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'pid', 'import_id', 'name', 'name_fa', 'shirt', 'height', 'weight', 'age', 'pos',
        'pos_fa', 'overall', 'playing_style', 'stats', 'pos_grades', 'team_ids',
        'is_primary', 'primary_pid',
    ];

    protected $casts = [
        'stats' => 'array',
        'pos_grades' => 'array',
        'team_ids' => 'array',
        'height' => 'integer',
        'weight' => 'integer',
        'age' => 'integer',
        'overall' => 'integer',
        'playing_style' => 'integer',
        'is_primary' => 'boolean',
        'primary_pid' => 'integer',
    ];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(PglTeam::class, 'pgl_player_team', 'player_pid', 'team_id');
    }

    /** نام نمایشی دوزبانه — fa locale نام فارسی را ترجیح می‌دهد */
    public function displayName(string $locale = 'fa'): string
    {
        return ($locale === 'fa' && $this->name_fa) ? $this->name_fa : $this->name;
    }

    public function changes(): HasMany
    {
        return $this->hasMany(PglPlayerChange::class, 'pid', 'pid')
            ->orderByDesc('created_at');
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(PglImport::class);
    }

    /** نمونه‌ی اصلی برای کارت‌های چندگانه (کلاسیک/ویژه) */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function statsByGroup(): array
    {
        $groups = [
            'هجومی' => ['offensive_awareness', 'finishing', 'heading', 'place_kicking', 'curl', 'kicking_power'],
            'فنی' => ['ball_control', 'dribbling', 'tight_possession', 'low_pass', 'lofted_pass'],
            'حرکتی' => ['speed', 'acceleration', 'stamina', 'jump', 'balance', 'physical_contact'],
            'دفاعی' => ['defensive_awareness', 'ball_winning', 'aggression'],
            'دروازه‌بان' => ['gk_awareness', 'gk_catching', 'gk_clearing', 'gk_reflexes', 'gk_reach'],
        ];
        $stats = $this->stats ?? [];
        $fa = [
            'offensive_awareness' => 'آگاهی هجومی', 'finishing' => 'پایان‌کاری', 'heading' => 'هد',
            'place_kicking' => 'پنالتی/ضربه ایستگاهی', 'curl' => 'چرخش توپ', 'kicking_power' => 'قدرت شوت',
            'ball_control' => 'کنترل توپ', 'dribbling' => 'دریبل', 'tight_possession' => 'حفظ توپ',
            'low_pass' => 'پاس کوتاه', 'lofted_pass' => 'پاس بلند', 'speed' => 'سرعت',
            'acceleration' => 'شتاب', 'stamina' => 'استقامت', 'jump' => 'پرش', 'balance' => 'تعادل',
            'physical_contact' => 'برخورد فیزیکی', 'defensive_awareness' => 'آگاهی دفاعی',
            'ball_winning' => 'توپ‌ربایی', 'aggression' => 'تهاجم',
            'gk_awareness' => 'آگاهی دروازه‌بان', 'gk_catching' => 'مهار توپ',
            'gk_clearing' => 'زدودن توپ', 'gk_reflexes' => 'واکنش', 'gk_reach' => 'دامنه دست',
        ];
        $out = [];
        foreach ($groups as $group => $keys) {
            $rows = [];
            foreach ($keys as $k) {
                if (isset($stats[$k]) && $stats[$k] > 40) {
                    $rows[] = ['key' => $k, 'label' => $fa[$k] ?? $k, 'value' => (int) $stats[$k]];
                }
            }
            if ($rows) {
                $out[$group] = $rows;
            }
        }
        return $out;
    }
}
