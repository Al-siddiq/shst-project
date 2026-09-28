<?php

namespace App\Controllers\Platform;

use App\Controllers\BaseController;
use App\Models\TenantModel;
use InvalidArgumentException;

class SupportAccessController extends BaseController
{
    protected $helpers = ['form'];

    public function index(): string
    {
        $context = service('platformSupportAccess')->current();
        $tenant = $context === null ? null : (new TenantModel())->find((int) $context['tenant_id']);

        return view('platform/support', [
            'supportContext' => $context,
            'tenant' => $tenant,
            'tenants' => (new TenantModel())->orderBy('school_name')->findAll(100),
        ]);
    }

    public function start()
    {
        try {
            service('platformSupportAccess')->start(
                (int) $this->request->getPost('tenant_id'),
                (string) $this->request->getPost('reason')
            );
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->withInput()->with('errors', ['support' => $exception->getMessage()]);
        }

        return redirect()->to(site_url('platform/support'))->with('message', 'Read-only support context started.');
    }

    public function end()
    {
        service('platformSupportAccess')->endCurrent();

        return redirect()->to(site_url('platform/support'))->with('message', 'Support context ended.');
    }
}
