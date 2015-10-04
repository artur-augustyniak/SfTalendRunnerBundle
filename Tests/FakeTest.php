<?php
namespace Aaugustyniak\SfTalendRunnerBundle\Tests;

use AppserverIo\Properties\Properties;
use PHPUnit_Framework_TestCase as TestCase;
use Aaugustyniak\SfTalendRunnerBundle\Model\JobFactory;
use Aaugustyniak\SfTalendRunnerBundle\Model\Impl\HostedJobFactory;

class FakeTest extends TestCase
{
    /**
     * @Todo test w. Symfony 2.* - each
     */
    public function testPhpUnitConfig()
    {
        var_dump("simple runner");

//        /**
//         * @var JobFactory
//         */
//        $jobFactory = new HostedJobFactory();
//
//        $job = $jobFactory->prepareJobBy('test_hosted_job');

        $properties = Properties::create();
        $properties->load(
            __DIR__ . '/../Resources/sample/test.no.sections.properties'
        );


    }

}
