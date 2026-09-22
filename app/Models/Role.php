<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToOrganization;
use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use BelongsToOrganization, HasFactory;

    public const CATEGORY_MUSICIAN = 'musician';

    public const CATEGORY_VOCAL = 'vocal';

    public const CATEGORY_TECHNICIAN = 'technician';

    public const CATEGORY_OPTIONS = [
        self::CATEGORY_MUSICIAN => 'Músico / Instrumentista',
        self::CATEGORY_VOCAL => 'Vocal / Voz',
        self::CATEGORY_TECHNICIAN => 'Equipe Técnica',
    ];

    public const CATEGORY_COLORS = [
        self::CATEGORY_MUSICIAN => 'success',
        self::CATEGORY_VOCAL => 'info',
        self::CATEGORY_TECHNICIAN => 'warning',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'name',
        'category',
        'description',
    ];

    /**
     * Get the team members with this default role.
     */
    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class, 'default_role_id');
    }
}
