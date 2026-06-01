<?php

namespace App\Controllers\Tenant\Website;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;
use InvalidArgumentException;

/** Supports usable server forms and a Vue-enhanced JSON reorder island. */
class MenuController extends BaseController
{
    use ApiResponseTrait;
    protected $helpers = ['form'];

    public function index(): string
    {
        $id = (int) $this->request->getGet('id');

        return view('tenant/website/menu/index', service('websiteMenuManagement')->formData($id > 0 ? $id : null));
    }

    public function save()
    {
        $rules = ['id' => 'permit_empty|integer', 'parent_id' => 'permit_empty|integer', 'label' => 'required|max_length[120]', 'link_type' => 'required|in_list[route,external]', 'route_name' => 'permit_empty|max_length[120]', 'url' => 'permit_empty|max_length[500]', 'target' => 'required|in_list[_self,_blank]', 'sort_order' => 'required|integer', 'status' => 'required|in_list[active,archived]', 'is_visible' => 'permit_empty|in_list[0,1]'];
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        return $this->command(fn () => service('websiteMenuManagement')->save($this->validator->getValidated()), 'Menu item saved.');
    }

    public function reorder()
    {
        $ids = $this->request->isAJAX() ? ($this->request->getJSON(true)['item_ids'] ?? []) : (array) $this->request->getPost('item_ids');
        try {
            service('websiteMenuManagement')->reorder((array) $ids);
        } catch (InvalidArgumentException $exception) {
            return $this->request->isAJAX() ? $this->fail('Menu reorder failed.', ['menu' => $exception->getMessage()], 422) : redirect()->back()->with('errors', ['menu' => $exception->getMessage()]);
        }

        return $this->request->isAJAX() ? $this->ok('Menu reordered.') : redirect()->back()->with('message', 'Menu reordered.');
    }

    public function archive(int $id)
    {
        return $this->command(fn () => service('websiteMenuManagement')->archive($id), 'Menu item archived.');
    }

    private function command(callable $action, string $message)
    {
        try {
            $action();
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->with('errors', ['menu' => $exception->getMessage()]);
        }

        return redirect()->back()->with('message', $message);
    }
}
