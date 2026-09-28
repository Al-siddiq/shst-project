<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

final class RuntimeConfigurationRegressionTest extends CIUnitTestCase
{
    public function testMatchMethodsAreUppercase(): void
    {
        $source = file_get_contents(APPPATH . 'Config/Routes.php');
        preg_match_all('/->match\(\[([^\]]+)\]/', $source, $calls);
        foreach ($calls[1] as $call) {
            preg_match_all("/'([^']+)'/", $call, $methods);
            foreach ($methods[1] as $method) {
                $this->assertSame(strtoupper($method), $method);
            }
        }
        $this->assertStringContainsString("match(['POST', 'PUT']", $source);
    }

    public function testCombinedFiltersUseFrameworkArraySyntax(): void
    {
        $source = file_get_contents(APPPATH . 'Config/Routes.php');
        $this->assertDoesNotMatchRegularExpression("/'filter'\\s*=>\\s*'[^']*,[^']*'/", $source);
        $this->assertStringContainsString("['filter' => ['tenantContext', 'publicTenant']]", $source);
    }

    public function testEveryConfiguredRouteFilterHasAnAlias(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');
        preg_match_all("/'filter'\\s*=>\\s*(?:'([^']+)'|\\[([^\\]]+)\\])/", $routes, $matches, PREG_SET_ORDER);
        $referenced = [];
        foreach ($matches as $match) {
            $values = $match[1] !== '' ? [$match[1]] : (preg_match_all("/'([^']+)'/", $match[2], $items) ? $items[1] : []);
            foreach ($values as $value) $referenced[] = explode(':', $value, 2)[0];
        }
        $aliases = array_keys(config('Filters')->aliases);
        $this->assertSame([], array_values(array_diff(array_unique($referenced), $aliases)));
    }

    public function testOlevelGradeBatchRowsHaveIdenticalExplicitColumns(): void
    {
        $source = file_get_contents(APPPATH . 'Database/Migrations/2026-06-03-120000_CreateApplicantSubmissionTables.php');
        preg_match("/table\('olevel_grades'\)->insertBatch\(\[(.*?)\]\);/s", $source, $batch);
        preg_match_all('/\[(.*?)\]/s', $batch[1] ?? '', $rows);
        $this->assertCount(9, $rows[1]);
        foreach ($rows[1] as $row) {
            preg_match_all("/'([^']+)'\\s*=>/", $row, $keys);
            $this->assertSame(['code', 'label', 'rank_value', 'is_passing', 'status', 'sort_order'], $keys[1]);
        }
    }
}
