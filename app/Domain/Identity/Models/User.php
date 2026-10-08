<?php

namespace App\Domain\Identity\Models;

use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Content\Models\Post;
use App\Domain\Discussion\Contracts\HasDiscussionOwner;
use App\Domain\Discussion\Enums\CommentAuthorBadge;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletTransfer;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\PresetAvatars;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Notification\Models\UserNotification;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Support\Models\Ticket;
use App\Domain\Vip\Models\VipSubscription;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements HasDiscussionOwner
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'first_name',
        'last_name',
        'display_name',
        'role_id',
        'mobile',
        'email',
        'status',
        'password',
        'mobile_verified_at',
        'email_verified_at',
        'avatar_id',
        'preset_avatar',
        'first_device_public_key',
        'first_device_fingerprint',
        'second_device_public_key',
        'second_device_fingerprint',
        'active_device_slot',
        'device_transfer_used',
        'first_activated_at',
        'transferred_at',
        'launcher_suspended_at',
        'launcher_suspended_until',
        'launcher_suspension_reason',
        'launcher_suspended_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'first_device_public_key',
        'first_device_fingerprint',
        'second_device_public_key',
        'second_device_fingerprint',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatusEnum::class,
            'preset_avatar' => 'integer',
            'active_device_slot' => 'integer',
            'device_transfer_used' => 'boolean',
            'first_activated_at' => 'datetime',
            'transferred_at' => 'datetime',
            'launcher_suspended_at' => 'datetime',
            'launcher_suspended_until' => 'datetime',
            'launcher_suspended_by' => 'integer',
        ];
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * Role relationship
     *
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_users')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * @return Attribute
     */
    public function fullName(): Attribute
    {
        return Attribute::make(
            fn() => $this->first_name . ' ' . $this->last_name
        );
    }

    /**
     * @return BelongsTo
     */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }

    public function profileAvatarUrl(): ?string
    {
        return ($this->preset_avatar !== null ? PresetAvatars::url($this->preset_avatar) : null)
            ?? $this->avatar?->url;
    }

    /**
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get user purchase accesses.
     */
    public function purchaseAccesses(): HasMany
    {
        return $this->hasMany(PurchaseAccess::class);
    }

    public function vipSubscriptions(): HasMany
    {
        return $this->hasMany(VipSubscription::class);
    }

    public function vipSaleItemClaims(): HasMany
    {
        return $this->hasMany(\App\Domain\Vip\Models\VipSaleItemClaim::class);
    }

    public function hasActiveVipSubscription(): bool
    {
        $now = now();

        return $this->vipSubscriptions()
            ->whereNull('revoked_at')
            ->where('starts_at', '<=', $now)
            ->where('expires_at', '>', $now)
            ->exists();
    }

    public function vipExpiresAt(): ?\Illuminate\Support\Carbon
    {
        if (!$this->hasActiveVipSubscription()) {
            return null;
        }

        $expiresAt = $this->vipSubscriptions()
            ->whereNull('revoked_at')
            ->max('expires_at');

        return $expiresAt ? \Illuminate\Support\Carbon::parse($expiresAt) : null;
    }

    public function launcherDevices(): HasMany
    {
        return $this->hasMany(LauncherDevice::class);
    }

    public function isLauncherSuspended(): bool
    {
        if ($this->launcher_suspended_at === null) {
            return false;
        }

        return $this->launcher_suspended_until === null
            || $this->launcher_suspended_until->isFuture();
    }

    public function launcherSuspensionIsPermanent(): bool
    {
        return $this->launcher_suspended_at !== null
            && $this->launcher_suspended_until === null;
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function favoriteProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Domain\Catalog\Models\Product::class,
            'product_favorites',
            'user_id',
            'product_id'
        )->withTimestamps();
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }


    public function pointAccount(): HasOne
    {
        return $this->hasOne(PointAccount::class);
    }

    /**
     * Wallet transfers sent by the user.
     */
    public function sentWalletTransfers(): HasMany
    {
        return $this->hasMany(WalletTransfer::class, 'sender_user_id');
    }

    /**
     * Wallet transfers received by the user.
     */
    public function receivedWalletTransfers(): HasMany
    {
        return $this->hasMany(WalletTransfer::class, 'recipient_user_id');
    }

    public function isDiscussionAdmin(): bool
    {
        return (bool)$this->role?->is_super_admin;
    }

    public function discussionOwnerUserId(): ?int
    {
        return $this->user_id ? (int)$this->user_id : null;
    }

    public function discussionOwnerBadge(): CommentAuthorBadge
    {
        return CommentAuthorBadge::POST_AUTHOR;
    }
}
