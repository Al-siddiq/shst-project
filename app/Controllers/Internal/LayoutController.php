<?php

namespace App\Controllers\Internal;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;

class LayoutController extends BaseController
{
    use ApiResponseTrait;

    public function show(string $portal = 'tenant')
    {
        return $this->ok('Layout resolved.', service('layoutResolver')->payload($portal));
    }
}
