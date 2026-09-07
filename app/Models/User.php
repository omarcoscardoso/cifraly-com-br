<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'email', 'phone', 'password', 'google_id', 'avatar', 'is_super_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    /**
     * Determine if the user is a super administrator.
     */
    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    /**
     * Determine if the user is an admin of the specified (or currently active) organization.
     */
    public function isOrgAdmin(?Organization $organization = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! $organization) {
            $organization = Filament::getTenant();
        }

        if (! $organization) {
            return false;
        }

        return $this->organizations()
            ->where('organizations.id', $organization->id)
            ->wherePivot('role', Organization::ROLE_ADMIN)
            ->exists();
    }

    /**
     * Determine if the user can access the given panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Get the organizations associated with the user.
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class)
            ->withPivot(['role'])
            ->withTimestamps();
    }

    /**
     * Get the teams that the user is a member of.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->using(TeamMember::class)
            ->withPivot(['id', 'organization_id', 'default_role_id'])
            ->withTimestamps();
    }

    /**
     * Get the team member pivot records for this user.
     */
    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    /**
     * Get the tenants that the user can access.
     *
     * @return array<Model> | Collection
     */
    public function getTenants(Panel $panel): array|Collection
    {
        return $this->organizations;
    }

    /**
     * Determine if the user can access the specified tenant.
     */
    public function canAccessTenant(Model $tenant): bool
    {
        return $this->organizations()->whereKey($tenant)->exists();
    }
}
