<?php

namespace App\Controllers\PublicSite;

use App\Controllers\BaseController;
use InvalidArgumentException;

/** Renders published or due-scheduled tenant editorial content. */
class ContentController extends BaseController
{
    protected $helpers = ['public_website'];

    public function index(string $section): string
    {
        try {
            $listing = service('publicEditorial')->listing($section, (int) ($this->request->getGet('page') ?: 1));
        } catch (InvalidArgumentException) {
            return $this->response->setStatusCode(404)->setBody(view('public_site/unavailable'));
        }

        $data = service('publicWebsite')->page($listing['section']['listingRoute'], $listing['section']['label']);
        $data['listing'] = $listing;

        return view('public_site/content/index', $data);
    }

    public function show(string $section, string $slug)
    {
        try {
            $item = service('publicEditorial')->item($section, $slug);
        } catch (InvalidArgumentException) {
            return $this->response->setStatusCode(404)->setBody(view('public_site/unavailable'));
        }

        $data = service('publicWebsite')->page($this->detailRoute($section), $item['title'], [$item['slug']]);
        $data['item'] = $item;
        $data['metaTitle'] = ($item['seo_title'] ?: $item['title']) . ' | ' . $data['settings']['site_title'];
        $data['metaDescription'] = $item['seo_description'] ?: ($item['summary'] ?? '');

        return view('public_site/content/show', $data);
    }

    private function detailRoute(string $section): string
    {
        return match ($section) {
            'news' => 'news_item',
            'announcements' => 'announcement',
            'calendar' => 'calendar_notice',
            default => throw new InvalidArgumentException('Unsupported editorial section.'),
        };
    }
}
