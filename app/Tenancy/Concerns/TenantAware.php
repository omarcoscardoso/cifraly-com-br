<?php

declare(strict_types=1);

namespace App\Tenancy\Concerns;

use App\Models\Organization;
use App\Tenancy\TenancyContext;

trait TenantAware
{
    public Organization $tenant;

    public function setTenant(Organization $tenant): self
    {
        $this->tenant = $tenant;

        return $this;
    }

    /**
     * Wrap execution in the job's tenant context.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function withTenantContext(callable $callback): mixed
    {
        return TenancyContext::run($this->tenant, $callback);
    }
}
