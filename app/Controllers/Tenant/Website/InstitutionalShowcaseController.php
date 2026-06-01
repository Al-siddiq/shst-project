<?php

namespace App\Controllers\Tenant\Website;

use App\Controllers\BaseController;
use InvalidArgumentException;

/** Thin server-rendered CMS endpoints; services own tenant-safe mutation policy. */
class InstitutionalShowcaseController extends BaseController
{
    protected $helpers = ['form'];

    public function management(): string
    {
        return view('tenant/website/institutional/management', service('institutionalShowcaseManagement')->managementData($this->id()));
    }

    public function saveManagement()
    {
        return $this->validated([
            'id' => 'permit_empty|integer', 'full_name' => 'required|max_length[180]', 'title' => 'required|max_length[180]',
            'bio' => 'required|max_length[20000]', 'photo_media_id' => 'required|integer', 'sort_order' => 'required|integer',
            'status' => 'required|in_list[draft,published,archived]',
        ], 'saveManagement', 'Management profile saved.');
    }

    public function archiveManagement(int $id)
    {
        return $this->command(fn () => service('institutionalShowcaseManagement')->archiveManagement($id), 'Management profile archived.');
    }

    public function gallery(): string
    {
        return view('tenant/website/institutional/gallery', service('institutionalShowcaseManagement')->galleryData($this->id()));
    }

    public function saveAlbum()
    {
        return $this->validated([
            'id' => 'permit_empty|integer', 'title' => 'required|max_length[180]',
            'slug' => 'required|regex_match[/^[a-z0-9-]+$/]|max_length[180]', 'description' => 'required|max_length[20000]',
            'category' => 'required|max_length[120]', 'cover_media_id' => 'required|integer', 'sort_order' => 'required|integer',
            'status' => 'required|in_list[draft,published,archived]',
        ], 'saveAlbum', 'Gallery album saved.');
    }

    public function archiveAlbum(int $id)
    {
        return $this->command(fn () => service('institutionalShowcaseManagement')->archiveAlbum($id), 'Gallery album archived.');
    }

    public function addItem()
    {
        return $this->validated([
            'gallery_album_id' => 'required|integer', 'media_file_id' => 'required|integer', 'caption' => 'required|max_length[500]',
            'alt_text' => 'required|max_length[255]', 'sort_order' => 'required|integer', 'status' => 'required|in_list[published,archived]',
        ], 'addItem', 'Gallery image added.');
    }

    public function reorderItems(int $albumId)
    {
        return $this->command(fn () => service('institutionalShowcaseManagement')->reorderItems($albumId, (array) $this->request->getPost('item_ids')), 'Gallery images reordered.');
    }

    public function removeItem(int $id)
    {
        return $this->command(fn () => service('institutionalShowcaseManagement')->removeItem($id), 'Gallery image removed.');
    }

    /** Converts optional selectors to null so models never receive a sentinel zero ID. */
    private function id(): ?int
    {
        $id = (int) $this->request->getGet('id');

        return $id > 0 ? $id : null;
    }

    /** @param array<string, string> $rules */
    private function validated(array $rules, string $method, string $message)
    {
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        return $this->command(fn () => service('institutionalShowcaseManagement')->{$method}($this->validator->getValidated()), $message);
    }

    private function command(callable $action, string $message)
    {
        try {
            $action();
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->with('errors', ['showcase' => $exception->getMessage()]);
        }

        return redirect()->back()->with('message', $message);
    }
}
