<?php

declare(strict_types=1);

namespace App\Tenancy;

use App\Models\Organization;

class TenancyContext
{
    protected static ?Organization $tenant = null;

    /**
     * Set the current active tenant in context.
     */
    public static function set(?Organization $tenant): void
    {
        static::$tenant = $tenant;
    }

    /**
     * Get the current active tenant from context.
     */
    public static function get(): ?Organization
    {
        return static::$tenant;
    }

    /**
     * Get the ID of the current active tenant.
     */
    public static function getId(): ?int
    {
        return static::$tenant?->id;
    }

    /**
     * Clear the current active tenant.
     */
    public static function clear(): void
    {
        static::$tenant = null;
    }

    /**
     * Execute a callback in the context of a specific tenant.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function run(Organization $tenant, callable $callback): mixed
    {
        $previous = static::$tenant;
        static::$tenant = $tenant;

        try {
            return $callback();
        } finally {
            static::$tenant = $previous;
        }
    }
}
