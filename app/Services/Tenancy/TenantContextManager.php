<?php

namespace App\Services\Tenancy;

use App\Entities\TenantContext;

class TenantContextManager
{
    private TenantContext $context;

    public function __construct()
    {
        $this->context = new TenantContext(null, null, null);
    }

    public function set(TenantContext $context): void
    {
        $this->context = $context;
    }

    public function current(): TenantContext
    {
        return $this->context;
    }
}
