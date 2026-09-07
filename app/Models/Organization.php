<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\OrganizationObserver;
use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[ObservedBy([OrganizationObserver::class])]
class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_MEMBER = 'member';

    public const ROLE_OPTIONS = [
        self::ROLE_ADMIN => 'Administrador da Org',
        self::ROLE_MEMBER => 'Membro',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'invite_code',
    ];

    /**
     * Generate a unique alphanumeric invite code (e.g. 8 chars uppercase).
     */
    public static function generateUniqueInviteCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('invite_code', $code)->exists());

        return $code;
    }

    /**
     * Regenerate a new invite code for this organization.
     */
    public function regenerateInviteCode(): string
    {
        $newCode = static::generateUniqueInviteCode();
        $this->update(['invite_code' => $newCode]);

        return $newCode;
    }

    /**
     * Get the users associated with this organization.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role'])
            ->withTimestamps();
    }

    /**
     * Get the songs belonging to this organization.
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class);
    }

    /**
     * Get the teams belonging to this organization.
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Get the roles belonging to this organization.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Get the events belonging to this organization.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
