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

use \Exception as Ex;

/**
 * Class IllegalStateException
 * @author Artur Augustyniak <artur@aaugustyniak.pl>
 * @package Aaugustyniak\SfTalendRunnerBundle\Toolset
 */
class IllegalStateException extends Ex
{

    const POST_EXTRACT_CODE = 9;
    const POST_EXTRACT_MSG = "You can't call any setter method after extractJob()";

    const PRE_CLEANUP_CODE = 10;
    const PRE_CLEANUP_MSG = "You can't call cleanup() before extractJob()";

    const POST_CLEANUP_CODE = 11;
    const POST_CLEANUP_MSG = "You can't call any other method after cleanup()";

    const JOB_FOLDER_EXIST_CODE = 12;
    const JOB_FOLDER_EXIST_MSG = "Job folder %s exists. Something went wrong at last run or " .
                                 "You have some namespacing issues if other system is using " .
                                 "this bundle and same Talend jobs at this machine.";

    const JOB_ZIP_CORRUPTED_CODE = 13;
    const JOB_ZIP_CORRUPTED_MSG = "%s %s.";


    public function __construct($message = "", $code = 0, Ex $previous = null)
    {
        Ex::__construct($message, $code, $previous);
    }


}