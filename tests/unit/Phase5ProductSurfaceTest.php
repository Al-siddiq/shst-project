<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

final class Phase5ProductSurfaceTest extends CIUnitTestCase
{
    public function testInternalLayoutsUseSharedDesignSystemAndAccessibleNavigation(): void
    {
        foreach (['tenant_admin.php', 'platform_admin.php'] as $layout) {
            $source = file_get_contents(APPPATH . 'Views/layouts/' . $layout);
            $this->assertStringContainsString('application-ui.css', $source);
            $this->assertStringContainsString('Skip to content', $source);
            $this->assertStringContainsString('data-app-nav', $source);
            $this->assertStringContainsString('aria-current', $source);
        }
    }

    public function testFutureModuleLayoutsAreNotExposedInProductionNavigation(): void
    {
        $navigation = file_get_contents(APPPATH . 'Config/Navigation.php');
        $this->assertStringNotContainsString("'label' => 'Lecturer", $navigation);
        $this->assertStringNotContainsString("'label' => 'Student", $navigation);
        $this->assertStringNotContainsString("'label' => 'Payments", $navigation);
    }

    public function testViteCannotRecursivelyCopyItsOutput(): void
    {
        $config = file_get_contents(ROOTPATH . 'vite.config.js');
        $this->assertStringContainsString('publicDir:false', $config);
    }
}
