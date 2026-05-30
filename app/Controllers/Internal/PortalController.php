<?php

namespace App\Controllers\Internal;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;

class PortalController extends BaseController
{
    use ApiResponseTrait;

    public function dashboard()
    {
        return $this->ok('Protected portal baseline is active for Block 1 Phase 1.', [
            'phase' => 'block1-phase1',
            'authenticated' => true,
        ]);
    }
}
