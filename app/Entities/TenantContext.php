<?php

namespace App\Entities;

class TenantContext
{
    public function __construct(
        public readonly ?int $tenantId,
        public readonly ?string $slug,
        public readonly ?string $source,
    ) {
    }

    public function isResolved(): bool
    {
        return $this->tenantId !== null;
    }
}
