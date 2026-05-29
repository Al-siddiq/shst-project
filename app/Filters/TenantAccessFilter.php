<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TenantAccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $context = service('tenantContextManager')->current();
        $access = service('tenantAccess');

        if (! $access->isMember($context)) {
            return service('response')
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Active tenant membership is required.',
                    'data' => [],
                    'errors' => ['membership' => 'You are not an active member of this tenant.'],
                ]);
        }

        $requirements = $this->parseRequirements($arguments ?? []);

        if ($requirements['groups'] !== [] && array_intersect($requirements['groups'], $access->groups($context)) === []) {
            return $this->deny('Required IAM group is missing.', 'group');
        }

        if ($requirements['authorities'] !== [] && array_intersect($requirements['authorities'], $access->authorities($context)) === []) {
            return $this->deny('Required operational authority is missing.', 'authority');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    /**
     * Supports both CI filter argument styles: ["authority", "code"] and ["authority:code"].
     */
    private function parseRequirements(array $arguments): array
    {
        $requirements = ['groups' => [], 'authorities' => []];

        for ($index = 0; $index < count($arguments); $index++) {
            $argument = $arguments[$index];

            if ($argument === 'group' && isset($arguments[$index + 1])) {
                $requirements['groups'] = array_merge($requirements['groups'], explode('|', $arguments[++$index]));
                continue;
            }

            if ($argument === 'authority' && isset($arguments[$index + 1])) {
                $requirements['authorities'] = array_merge($requirements['authorities'], explode('|', $arguments[++$index]));
                continue;
            }

            if (str_starts_with($argument, 'group:')) {
                $requirements['groups'] = array_merge($requirements['groups'], explode('|', substr($argument, 6)));
                continue;
            }

            if (str_starts_with($argument, 'authority:')) {
                $requirements['authorities'] = array_merge($requirements['authorities'], explode('|', substr($argument, 10)));
            }
        }

        return [
            'groups' => array_values(array_filter($requirements['groups'])),
            'authorities' => array_values(array_filter($requirements['authorities'])),
        ];
    }

    private function deny(string $message, string $key): ResponseInterface
    {
        return service('response')
            ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
            ->setJSON([
                'status' => 'error',
                'message' => $message,
                'data' => [],
                'errors' => [$key => $message],
            ]);
    }
}
