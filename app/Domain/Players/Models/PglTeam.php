<?php

namespace App\Domain\Players\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PglTeam extends Model
{
    protected $table = 'pgl_teams';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'name', 'name_fa', 'code'];

    /** نام نمایشی دوزبانه — fa locale نام فارسی را ترجیح می‌دهد */
    public function displayName(string $locale = 'fa'): string
    {
        return ($locale === 'fa' && $this->name_fa) ? $this->name_fa : $this->name;
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(PglPlayer::class, 'pgl_player_team', 'team_id', 'player_pid');
    }

    public function primaryPlayers(): BelongsToMany
    {
        return $this->players()->where('is_primary', true);
    }
}
