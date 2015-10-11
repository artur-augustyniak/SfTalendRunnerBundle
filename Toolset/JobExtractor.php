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

namespace Aaugustyniak\SfTalendRunnerBundle\Toolset;

use Aaugustyniak\SfTalendRunnerBundle\Toolset\IllegalStateException as ISE;
use \ZipArchive as Zip;

/**
 * Class JobExtractor
 * @author Artur Augustyniak <artur@aaugustyniak.pl>
 * @package Aaugustyniak\SfTalendRunnerBundle\Toolset
 */
class JobExtractor
{
    const JOB_ZIP_PATTERN = '/^[A-Za-z]+_\d+\.\d+\.zip$/';
    const EXTRACTED_JOB_FOLDER_PATTERN = '/^[A-Za-z]+_\d+\.\d+$/';

    private $extracted = false;

    private $cleanedUp = false;

    private $workspacePath;

    private $usageNamespace;

    private $jobZipPath;

    /**
     * Usage namespace ma zapobiegać rzucaniu wyjatku
     * przy napotkaniu rozpakowanego folderu o nazwie joba
     * czyli sytuacja kiedy coś przerwalo joba, rozpakowany nie
     * został sprzątnięty a inny system używa tego bundle'a
     *
     * JobExtractor constructor.
     * @param string $usageNamespace prefix for target job folder name
     * @param string $jobZipPath path to zipped Talend job
     */
    public function __construct($usageNamespace, $jobZipPath)
    {
        $this->usageNamespace = $usageNamespace;
        $this->jobZipPath = $jobZipPath;
        $this->workspacePath = sys_get_temp_dir();
    }

    /**
     * @return string
     * @throws IllegalStateException
     */
    public function getWorkspacePath()
    {
        $this->checkCleanupValidity();
        return $this->workspacePath;
    }

    /**
     * @param $workspacePath
     * @throws IllegalStateException
     */
    public function setWorkspacePath($workspacePath)
    {
        $this->checkCleanupValidity();
        $this->checkExtractValidity();
        $this->workspacePath = $workspacePath;
    }

    /**
     * @return string path to extracted job
     * @throws IllegalStateException
     */
    public function extractJob()
    {
        $this->checkCleanupValidity();
        $this->validateJobZip();


        $this->extracted = true;

        $jobName = "NonContextSuccessfullJob_0.1";

        $dirPath = $this->workspacePath . DIRECTORY_SEPARATOR . $jobName;
        if (file_exists($dirPath)) {
            $msg = sprintf(ISE::JOB_FOLDER_EXIST_MSG, $jobName);
            throw new ISE($msg, ISE::JOB_FOLDER_EXIST_CODE);
        }
        mkdir($dirPath, 0700);
        return $dirPath;

    }

    /**
     * This method should be called after the completion
     * of all activities related to the currently
     * processed Talend job.
     *
     * @return null
     * @throws IllegalStateException
     */
    public function cleanup()
    {
        if (!$this->extracted) {
            throw new ISE(ISE::PRE_CLEANUP_MSG, ISE::PRE_CLEANUP_CODE);
        }
        $this->cleanedUp = true;
        return null;
    }

    /**
     * @return string
     * @throws IllegalStateException
     */
    public function getUsageNamespace()
    {
        $this->checkCleanupValidity();
        return $this->usageNamespace;
    }

    /**
     * @return string
     * @throws IllegalStateException
     */
    public function getJobZipPath()
    {
        $this->checkCleanupValidity();
        return $this->jobZipPath;
    }

    /**
     * @throws IllegalStateException
     */
    private function checkCleanupValidity()
    {
        if ($this->cleanedUp) {
            throw new ISE(ISE::POST_CLEANUP_MSG, ISE::POST_CLEANUP_CODE);
        }
    }


    /**
     * @throws IllegalStateException
     */
    private function checkExtractValidity()
    {
        if ($this->extracted) {
            throw new ISE(ISE::POST_EXTRACT_MSG, ISE::POST_EXTRACT_CODE);
        }
    }

    /**
     * @throws IllegalStateException
     */
    private function validateJobZip()
    {
        $inputJobName = basename($this->jobZipPath);
        if (!preg_match(self::JOB_ZIP_PATTERN, $inputJobName) > 0) {
            $msg = sprintf(ISE::JOB_ZIP_CORRUPTED_MSG,
                $inputJobName,
                "do not match Talend jobs naming convention"
            );
            throw new ISE($msg, ISE::JOB_ZIP_CORRUPTED_CODE);
        }

        if (!file_exists($this->jobZipPath)) {
            $msg = sprintf(ISE::JOB_ZIP_CORRUPTED_MSG,
                $this->jobZipPath,
                "do not exist"
            );
            throw new ISE($msg, ISE::JOB_ZIP_CORRUPTED_CODE);
        }

//        $zip = new Zip();
//
//        // ZipArchive::CHECKCONS will enforce additional consistency checks
//        $res = $zip->open($this->jobZipPath, \ZipArchive::CHECKCONS);
//        switch ($res) {
//
//            case Zip::ER_NOZIP :
//                die('not a zip archive');
//            case Zip::ER_INCONS :
//                die('consistency check failed');
//            case Zip::ER_CRC :
//                die('checksum failed');
//
//            // ... check for the other types of errors listed in the manual
//        }
    }


}