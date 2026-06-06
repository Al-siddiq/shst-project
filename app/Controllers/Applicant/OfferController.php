<?php

namespace App\Controllers\Applicant;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;
use CodeIgniter\Exceptions\PageNotFoundException;
use InvalidArgumentException;

/** Applicant-facing offer page and Phase 7 accept/decline commands. */
class OfferController extends BaseController
{
    use ApiResponseTrait;

    protected $helpers = ['form'];

    public function index(): string
    {
        try {
            $state = service('admissionAcceptance')->applicantOffer();
        } catch (InvalidArgumentException) {
            throw PageNotFoundException::forPageNotFound('No active admission offer was found for this applicant.');
        }

        return view('applicant/offer', array_merge(service('publicWebsite')->page('apply', 'Admission offer'), $state));
    }

    public function accept()
    {
        return $this->persist(static fn (array $payload): array => service('admissionAcceptance')->acceptOwnOffer($payload), 'Admission offer accepted.');
    }

    public function decline()
    {
        return $this->persist(static fn (array $payload): array => service('admissionAcceptance')->declineOwnOffer($payload), 'Admission offer declined.');
    }

    /** @param callable(array<string,mixed>):array<string,mixed> $callback */
    private function persist(callable $callback, string $message)
    {
        try {
            $data = $callback($this->request->getPost());
        } catch (InvalidArgumentException $exception) {
            return $this->request->isAJAX()
                ? $this->fail('Validation failed.', ['offer' => $exception->getMessage()], 422)
                : redirect()->back()->withInput()->with('errors', ['offer' => $exception->getMessage()]);
        }

        return $this->request->isAJAX()
            ? $this->ok($message, $data)
            : redirect()->back()->with('message', $message);
    }
}
