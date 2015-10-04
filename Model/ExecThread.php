<?php
namespace Aaugustyniak\SfTalendRunnerBundle\Model;

use Aaugustyniak\SemiThread\SemiThread;

class ExecThread extends SemiThread
{

    /**
     * User defined method.
     * Job to be done in SemiThread
     *
     * @return void
     */
    public function run()
    {
        echo shell_exec($this->payload->getText()) . "\n";
    }
}