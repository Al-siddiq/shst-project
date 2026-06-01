<?php

namespace App\Controllers\Tenant\Website;

use App\Controllers\BaseController;
use InvalidArgumentException;

/**
 * Server-rendered editorial CMS endpoints.
 *
 * Draft editing and lifecycle commands are separate HTTP actions so authority
 * filters and service checks remain explicit and easy to audit.
 */
class EditorialController extends BaseController
{
    protected $helpers = ['form'];

    public function index(string $type)
    {
        return $this->render('tenant/website/editorial/index', $type);
    }

    public function form(string $type)
    {
        return $this->render('tenant/website/editorial/form', $type);
    }

    public function saveDraft(string $type)
    {
        if (! $this->validateData($this->request->getPost(), $this->rules($type))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            service('editorialManagement')->saveDraft($type, $this->validator->getValidated(), $this->itemId());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->with('errors', ['editorial' => $exception->getMessage()]);
        }

        return redirect()->to(site_url('tenant/website/editorial/' . $type))->with('message', 'Editorial draft saved.');
    }

    public function moveToDraft(string $type, int $id)
    {
        return $this->command(fn () => service('editorialManagement')->moveToDraft($id, $type), 'Editorial content moved to draft.');
    }

    public function schedule(string $type, int $id)
    {
        $scheduledFor = (string) $this->request->getPost('scheduled_for');

        return $this->command(fn () => service('editorialManagement')->schedule($id, $type, $scheduledFor), 'Editorial content scheduled.');
    }

    public function publish(string $type, int $id)
    {
        return $this->command(fn () => service('editorialManagement')->publish($id, $type), 'Editorial content published.');
    }

    public function archive(string $type, int $id)
    {
        return $this->command(fn () => service('editorialManagement')->archive($id, $type), 'Editorial content archived.');
    }

    public function delete(string $type, int $id)
    {
        return $this->command(fn () => service('editorialManagement')->delete($id, $type), 'Editorial content deleted.');
    }

    /** @return array<string, mixed> */
    private function data(string $type): array
    {
        return array_merge(service('editorialManagement')->formData($type, $this->itemId()), ['contentType' => $type]);
    }

    private function itemId(): ?int
    {
        $id = (int) ($this->request->getPost('id') ?: $this->request->getGet('item'));

        return $id > 0 ? $id : null;
    }

    /** @return array<string, string> */
    private function rules(string $type): array
    {
        $rules = [
            'title' => 'required|max_length[255]',
            'slug' => 'required|regex_match[/^[a-z0-9-]+$/]|max_length[180]',
            'summary' => 'permit_empty|max_length[10000]',
            'body' => 'required|max_length[30000]',
            'featured_media_id' => 'permit_empty|integer',
            'category' => 'permit_empty|max_length[120]',
            'related_department_id' => 'permit_empty|integer',
            'related_programme_id' => 'permit_empty|integer',
            'author_display_name' => 'permit_empty|max_length[160]',
            'announcement_type' => 'permit_empty|max_length[80]',
            'priority' => 'permit_empty|in_list[low,normal,high,urgent]',
            'audience' => 'permit_empty|in_list[public,applicants,students,staff,department,programme]',
            'event_start_at' => 'permit_empty|valid_date[Y-m-d\\TH:i]',
            'event_end_at' => 'permit_empty|valid_date[Y-m-d\\TH:i]',
            'academic_session_id' => 'permit_empty|integer',
            'semester_id' => 'permit_empty|integer',
            'visibility' => 'permit_empty|in_list[public,private]',
            'seo_title' => 'permit_empty|max_length[255]',
            'seo_description' => 'permit_empty|max_length[320]',
        ];

        return array_merge($rules, match ($type) {
            'news' => [
                'summary' => 'required|max_length[10000]',
                'featured_media_id' => 'required|integer',
                'category' => 'required|max_length[120]',
                'author_display_name' => 'required|max_length[160]',
            ],
            'announcement' => [
                'announcement_type' => 'required|max_length[80]',
                'priority' => 'required|in_list[low,normal,high,urgent]',
                'audience' => 'required|in_list[public,applicants,students,staff,department,programme]',
                'event_start_at' => 'required|valid_date[Y-m-d\\TH:i]',
                'event_end_at' => 'required|valid_date[Y-m-d\\TH:i]',
            ],
            'calendar_notice' => [
                'event_start_at' => 'required|valid_date[Y-m-d\\TH:i]',
                'event_end_at' => 'required|valid_date[Y-m-d\\TH:i]',
                'visibility' => 'required|in_list[public,private]',
            ],
            default => [],
        });
    }

    private function render(string $view, string $type)
    {
        try {
            return view($view, $this->data($type));
        } catch (InvalidArgumentException) {
            return $this->response->setStatusCode(404)->setBody('Editorial section was not found.');
        }
    }

    private function command(callable $action, string $message)
    {
        try {
            $action();
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('errors', ['editorial' => $exception->getMessage()]);
        }

        return redirect()->back()->with('message', $message);
    }
}
