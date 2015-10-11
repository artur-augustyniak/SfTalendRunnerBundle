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
    const EXTRACTED_JOB_FOLDER_NAME = "NonContextSuccessfullJob_0.1";
    const TEST_JOB_ZIP_PATH = __DIR__ . "/../sample_jobs/NonContextSuccessfullJob_0.1.zip";
    const TEST_NONEXISTENT_JOB_ZIP_PATH = __DIR__ . "/../sample_jobs/BlahJob_0.1.zip";
    const TEST_CORRUPTED_JOB_ZIP_PATH = __DIR__ . "/../sample_jobs/NonContextSuccessfullJob_0_BLAH_.zip";

    /**
     * @var JobExtractor
     */
    private $jex;

    private static $testWorkspace;


    /**
     * @before
     */
    public function beforeEachTest()
    {
        self::prepareCleanWorkspace();
        $this->createExtractorInstance(self::TEST_USAGE_NAMESPACE,
            self::TEST_JOB_ZIP_PATH,
            self::$testWorkspace
        );
    }

    /**
     * @param $namespace
     * @param $zipPath
     * @param $workspace
     */
    private function createExtractorInstance($namespace, $zipPath, $workspace)
    {
        $this->jex = new JobExtractor(
            $namespace,
            $zipPath
        );
        $this->jex->setWorkspacePath($workspace);
    }

    /**
     * @afterClass
     */
    public function afterAllTests()
    {
        self::delTree(self::$testWorkspace);
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


        $filter = function ($rm) {
            $excludes = array('__construct', 'cleanup');
            return !in_array($rm->name, $excludes);
        };
        $this->assertExceptions($filter, IllegalStateException::POST_CLEANUP_CODE);
    }

    /**
     * @test
     */
    public function after_extract_setters_are_illegal()
    {
        $extractedJobPath = $this->jex->extractJob();

        $filter = function ($reflectMet) {
            $excludes = array('__construct', 'cleanup');
            return "set" === substr($reflectMet->name, 0, 3) && !in_array($reflectMet->name, $excludes);
        };

        $this->assertExceptions($filter, IllegalStateException::POST_EXTRACT_CODE);
        $this->assertNotNull($extractedJobPath);
    }


    private function assertExceptions($methodFilter, $expectedCode)
    {
        $class = new RC('Aaugustyniak\SfTalendRunnerBundle\Toolset\JobExtractor');
        $methods = array_filter($class->getMethods(RM::IS_PUBLIC), $methodFilter);
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
            $this->assertEquals($expectedCode, $ex->getCode());
        }
    }

    /**
     * @test
     */
    public function extract_creates_and_returns_existing_directory_with_name_pattern()
    {
        $extractedJobPath = $this->jex->extractJob();
        $jobFolderName = basename($extractedJobPath);
        $this->assertFileExists($extractedJobPath);
        $this->assertTrue(is_dir($extractedJobPath));
        $this->assertRegExp(JobExtractor::EXTRACTED_JOB_FOLDER_PATTERN, $jobFolderName);
    }


    /**
     * @test
     * @expectedException \Aaugustyniak\SfTalendRunnerBundle\Toolset\IllegalStateException
     * @expectedExceptionCode \Aaugustyniak\SfTalendRunnerBundle\Toolset\IllegalStateException::JOB_ZIP_CORRUPTED_CODE
     */
    public function extract_fails_when_job_zip_name_do_not_match_pattern()
    {
        $this->createExtractorInstance(self::TEST_USAGE_NAMESPACE,
            self::TEST_CORRUPTED_JOB_ZIP_PATH,
            self::$testWorkspace
        );
        $this->jex->extractJob();
    }


    /**
     * @test
     * @expectedException \Aaugustyniak\SfTalendRunnerBundle\Toolset\IllegalStateException
     * @expectedExceptionCode \Aaugustyniak\SfTalendRunnerBundle\Toolset\IllegalStateException::JOB_ZIP_CORRUPTED_CODE
     */
    public function extract_fails_when_job_zip_name_do_not_exist()
    {
        $this->createExtractorInstance(self::TEST_USAGE_NAMESPACE,
            self::TEST_NONEXISTENT_JOB_ZIP_PATH,
            self::$testWorkspace
        );
        $this->jex->extractJob();
    }



    /**
     * @test
     * @expectedException \Aaugustyniak\SfTalendRunnerBundle\Toolset\IllegalStateException
     * @expectedExceptionCode \Aaugustyniak\SfTalendRunnerBundle\Toolset\IllegalStateException::JOB_FOLDER_EXIST_CODE
     */
    public function extract_fails_when_job_path_exists()
    {
        mkdir(self::$testWorkspace . DIRECTORY_SEPARATOR . self::EXTRACTED_JOB_FOLDER_NAME);
        $this->jex->extractJob();
    }

    private static function prepareCleanWorkspace()
    {
        self::$testWorkspace = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "job_extractor_test";
        self::delTree(self::$testWorkspace);
        mkdir(self::$testWorkspace, 0700);
    }

    private static function delTree($path)
    {
        if (file_exists($path)) {
            $dir = opendir($path);
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    $full = $path . '/' . $file;
                    if (is_dir($full)) {
                        self::delTree($full);
                    } else {
                        unlink($full);
                    }
                }
            }
            closedir($dir);
            rmdir($path);
        }
    }

}
