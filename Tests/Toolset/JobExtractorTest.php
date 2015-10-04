<?php
/*
* The MIT License (MIT)
*
* Copyright (c) 2015 Artur Augustyniak
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace Aaugustyniak\SfTalendRunnerBundle\Tests\Toolset;

use Aaugustyniak\SfTalendRunnerBundle\Toolset\IllegalStateException;
use Aaugustyniak\SfTalendRunnerBundle\Toolset\JobExtractor;
use PHPUnit_Framework_TestCase as TestCase;
use \ReflectionClass as RC;
use \ReflectionMethod as RM;

/**
 * Class JobExtractorTest
 * @author Artur Augustyniak <artur@aaugustyniak.pl>
 * @package Aaugustyniak\SfTalendRunnerBundle\Tests\Toolset
 */
class JobExtractorTest extends TestCase
{

    const TEST_USAGE_NAMESPACE = "some_namespace";
    const TEST_JOB_ZIP_PATH = __DIR__ . "../sample_jobs/NonContextSuccessfullJob_0.1.zip";

    /**
     * @var JobExtractor
     */
    private $jex;

    /**
     * @before
     */
    protected function beforeEachTest()
    {
        $this->jex = new JobExtractor(
            self::TEST_USAGE_NAMESPACE,
            self::TEST_JOB_ZIP_PATH
        );
    }

    /**
     * @test
     */
    public function cannot_create_if_folder_exists()
    {
        $this->markTestSkipped("stub");
    }

    /**
     * @test
     * @expectedException \Aaugustyniak\SfTalendRunnerBundle\Toolset\IllegalStateException
     * @expectedExceptionCode \Aaugustyniak\SfTalendRunnerBundle\Toolset\IllegalStateException::PRE_CLEANUP_CODE
     */
    public function cannot_call_cleanup_before_extract()
    {
        $this->jex->cleanup();
    }

    /**
     * @test
     * @throws IllegalStateException
     */
    public function after_cleanup_each_other_call_is_illegal()
    {
        $this->jex->extractJob();
        $this->jex->cleanup();

        $class = new RC('Aaugustyniak\SfTalendRunnerBundle\Toolset\JobExtractor');
        $methods = array_filter($class->getMethods(RM::IS_PUBLIC), function ($rm) {
            $excludes = array('__construct', 'cleanup');
            return !in_array($rm->name, $excludes);
        });
        $exceptions = array();
        foreach ($methods as $m) {
            /** @var $m \ReflectionMethod */
            try {
                $m->invoke($this->jex, null);
            } catch (\Exception $e) {
                $exceptions[] = $e;
                //var_dump($e->getMessage(), $e->getCode());
            }
        }

        $expectedExceptionCount = count($methods);
        $actualExceptionCount = count($exceptions);

        $this->assertEquals($expectedExceptionCount, $actualExceptionCount);

        foreach ($exceptions as $ex) {
            $this->assertEquals(IllegalStateException::POST_CLEANUP_CODE, $ex->getCode());
        }


    }

    /**
     * @test
     * @expectedException \Aaugustyniak\SfTalendRunnerBundle\Toolset\IllegalStateException
     */
    public function after_extract_setters_are_illegal()
    {
        $this->markTestSkipped("stub");
    }

}
