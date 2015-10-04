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

/**
 * Class JobExtractor
 * @author Artur Augustyniak <artur@aaugustyniak.pl>
 * @package Aaugustyniak\SfTalendRunnerBundle\Toolset
 */
class JobExtractor
{

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
        $this->checkValidity();
        return $this->workspacePath;
    }

    /**
     * @param $workspacePath
     * @throws IllegalStateException
     */
    public function setWorkspacePath($workspacePath)
    {
        $this->checkValidity();
        $this->workspacePath = $workspacePath;
    }

    /**
     * @return string path to extracted job
     * @throws IllegalStateException
     */
    public function extractJob()
    {
        $this->checkValidity();
        $this->extracted = true;
        return "path to extracted job";

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
        $this->checkValidity();
        return $this->usageNamespace;
    }

    /**
     * @return string
     * @throws IllegalStateException
     */
    public function getJobZipPath()
    {
        $this->checkValidity();
        return $this->jobZipPath;
    }

    /**
     * @throws IllegalStateException
     */
    private function checkValidity()
    {
        if ($this->cleanedUp) {
            throw new ISE(ISE::POST_CLEANUP_MSG, ISE::POST_CLEANUP_CODE);
        }
    }


}