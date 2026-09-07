<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Organization;
use App\Tenancy\TenancyContext;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganization
{
    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('organization', function (Builder $builder): void {
            $tenantId = TenancyContext::getId() ?? Filament::getTenant()?->id;

            if ($tenantId) {
                $builder->where($builder->getModel()->getTable().'.organization_id', $tenantId);
            }
        });

        static::creating(function (Model $model): void {
            $tenantId = TenancyContext::getId() ?? Filament::getTenant()?->id;

            if (! $model->organization_id && $tenantId) {
                $model->organization_id = $tenantId;
            }
        });
    }

    /**
     * Get the organization that the model belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
