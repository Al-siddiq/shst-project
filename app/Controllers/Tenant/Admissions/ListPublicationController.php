<?php

namespace App\Controllers\Tenant\Admissions;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;
use InvalidArgumentException;

/** Tenant staff Phase 6 admission-list preview and publication workspace. */
class ListPublicationController extends BaseController
{
    use ApiResponseTrait;

    protected $helpers = ['form'];

    public function index(): string
    {
        return view('tenant/admissions/lists/index', service('admissionListPublication')->workspace());
    }

    public function show(int $id): string
    {
        return view('tenant/admissions/lists/show', service('admissionListPublication')->preview($id));
    }

    public function create()
    {
        return $this->persist(static fn (array $payload): int => service('admissionListPublication')->create($payload), 'Admission list draft created.');
    }

    public function addEntry(int $id)
    {
        $offerId = (int) $this->request->getPost('admission_offer_id');
        $position = $this->request->getPost('entry_position') === '' ? null : (int) $this->request->getPost('entry_position');

        return $this->persist(static fn (): int => service('admissionListPublication')->addOfferedApplication($id, $offerId, $position), 'Offered applicant added to list draft.');
    }

    public function publish(int $id)
    {
        return $this->persist(static fn (): int => service('admissionListPublication')->publish($id), 'Admission list published.');
    }

    /** @param callable(array<string,mixed>):int|callable():int $callback */
    private function persist(callable $callback, string $message)
    {
        try {
            $id = $callback($this->request->getPost());
        } catch (InvalidArgumentException $exception) {
            return $this->request->isAJAX()
                ? $this->fail('Validation failed.', ['admissions' => $exception->getMessage()], 422)
                : redirect()->back()->withInput()->with('errors', ['admissions' => $exception->getMessage()]);
        }

        return $this->request->isAJAX()
            ? $this->ok($message, ['id' => $id])
            : redirect()->back()->with('message', $message);
    }
}
