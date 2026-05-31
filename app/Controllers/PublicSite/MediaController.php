<?php

namespace App\Controllers\PublicSite;

use App\Controllers\BaseController;
use InvalidArgumentException;
use RuntimeException;

/**
 * Streams public derivatives only after tenant and visibility filters run.
 *
 * Keeping public delivery behind a controller avoids exposing writable storage
 * directly and ensures a guessed ID cannot bypass tenant-scoped lookup rules.
 */
class MediaController extends BaseController
{
    public function show(int $id, string $variant = 'medium')
    {
        try {
            $file = service('websiteMedia')->publicDerivative($id, $variant);
        } catch (InvalidArgumentException | RuntimeException) {
            return $this->response->setStatusCode(404)->setBody(view('public_site/unavailable'));
        }

        return $this->response
            ->setHeader('Cache-Control', 'public, max-age=86400')
            ->setContentType($file['media']['mime_type'])
            ->setBody(file_get_contents($file['path']));
    }
}
