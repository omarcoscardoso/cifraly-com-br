<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use BelongsToOrganization, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'name',
        'description',
    ];

    /**
     * Get the users that are members of this team.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->using(TeamMember::class)
            ->withPivot(['id', 'organization_id', 'default_role_id'])
            ->withTimestamps();
    }

    /**
     * Get the team member pivot records.
     */
    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }
}
