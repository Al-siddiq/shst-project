<?php
namespace Tests\Unit;
use App\Services\Admissions\AdmissionReportService;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionMethod;
final class Phase4CsvSafetyTest extends CIUnitTestCase
{
    public function testSpreadsheetFormulaPrefixesAreNeutralized():void
    {
        $method=new ReflectionMethod(AdmissionReportService::class,'csvCell'); $method->setAccessible(true); $service=new AdmissionReportService();
        foreach(['=1+1','+SUM(A:A)','-2+3','@cmd'] as $unsafe)$this->assertStringStartsWith("'",$method->invoke($service,$unsafe));
        $this->assertSame('ordinary',$method->invoke($service,'ordinary'));
    }
}
