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
use App\Domain\Media\Models\MediaFile;
use App\Domain\Support\Models\Ticket;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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

    /**
     * @return HasMany
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
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

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
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
