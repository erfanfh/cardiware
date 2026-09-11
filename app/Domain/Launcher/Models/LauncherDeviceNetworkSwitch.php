<?php

namespace App\Domain\Launcher\Models;

use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LauncherDeviceNetworkSwitch extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'launcher_device_network_switches';

    protected $fillable = [
        'launcher_device_id',
        'user_id',
        'network',
        'previous_network',
        'seconds_since_previous',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'seconds_since_previous' => 'integer',
    ];

    /**
     * A switch this fast is the interesting one. One machine changing networks takes
     * long enough to reconnect that a handoff shows a gap of minutes; two machines
     * taking turns produce gaps this short, over and over.
     */
    public const RapidSwitchSeconds = 300;

    public function device(): BelongsTo
    {
        return $this->belongsTo(LauncherDevice::class, 'launcher_device_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Reduces an address to the part that identifies a network rather than a person:
     * the first two octets of an IPv4 address (a /16) or the first three hextets of an
     * IPv6 address (a /48).
     *
     * Deliberately coarse. A customer whose ISP rotates their address within the same
     * pool must not look like a network change, or every dynamic-IP user in the country
     * would be flagged. Returns null for anything unparseable, which the caller treats
     * as "do not record" -- a missing sample is always better than a wrong one.
     */
    public static function networkOf(?string $ip): ?string
    {
        $ip = trim((string)$ip);
        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return null;
        }

        if (str_contains($ip, ':')) {
            $expanded = bin2hex((string)inet_pton($ip));
            if ($expanded === '') {
                return null;
            }
            // 3 hextets = 12 hex characters = /48.
            return implode(':', str_split(substr($expanded, 0, 12), 4));
        }

        $octets = explode('.', $ip);
        return count($octets) === 4 ? $octets[0] . '.' . $octets[1] : null;
    }
}
