<?php

namespace App\Controllers\PublicSite;

use App\Controllers\BaseController;
use InvalidArgumentException;

/** Renders published public gallery albums and optimized derivative links. */
class GalleryController extends BaseController
{
    protected $helpers = ['public_website'];

    public function index(): string
    {
        $data = service('publicWebsite')->page('gallery', 'Gallery');
        $data['gallery'] = service('publicInstitutionalShowcase')->gallery((int) ($this->request->getGet('page') ?: 1));

        return view('public_site/gallery/index', $data);
    }

    public function show(string $slug)
    {
        try {
            $gallery = service('publicInstitutionalShowcase')->album($slug);
        } catch (InvalidArgumentException) {
            return $this->response->setStatusCode(404)->setBody(view('public_site/unavailable'));
        }
        $data = service('publicWebsite')->page('gallery_album', $gallery['album']['title'], [$slug]);
        $data['gallery'] = $gallery;

        return view('public_site/gallery/show', $data);
    }
}
