<?php
namespace Aaugustyniak\SfTalendRunnerBundle\Command;

use Aaugustyniak\SemiThread\ConfinedEnvelope;
use Aaugustyniak\SemiThread\ExampleImpl\StringPayload;
use Aaugustyniak\SemiThread\ExampleImpl\WriterThread;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputOption;


class RunTalendJobCommand extends ContainerAwareCommand
{

    const TALEND_JOBS_DIR = "talend_jobs";

    /**
     * @var array
     */
    private $jobs = array();

    /**
     * @var Container
     */
    private $container;


    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;


    /**
     * @var array
     */
    private $synchroConnections = array();


    /**
     * @var array
     */
    private $talendConfig = array();


    /**
     * @var array
     */
    private $mailerConfig = array();


    /**
     * @var array
     */
    private $intravelDbConfig = array();


    /**
     * @var array
     */
    private $tetaDbConfig = array();


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName($this->getCommandName())
            ->setDescription($this->getCommandDescription())
            ->addOption(
                'list',
                null,
                InputOption::VALUE_NONE,
                'List available Talend jobs.'
            )
            ->addOption(
                'run',
                null,
                InputOption::VALUE_REQUIRED,
                'List available Talend jobs.'
            );
    }


    private function dumpConfig()
    {
        var_dump(
            $this->synchroConnections,
            $this->talendConfig,
            $this->mailerConfig,
            $this->intravelDbConfig,
            $this->tetaDbConfig);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initializeProperties($input, $output);
        if ($input->getOption('list')) {
            $output->writeln("Jobs list");
            foreach ($this->jobs as $name => $job) {
                $line = sprintf("script name: <info>%s</info>", $name);
                $output->writeln($line);
            };
            return;
        }
        if ($input->getOption('run')) {
            $this->runJob($this->jobs[$input->getOption('run')]);
            return;
        }
        $command = $this->getApplication()->find($this->getCommandName());

        $arguments = array(
            'command' => $command,
            '--list' => true,
        );

        $input = new ArrayInput($arguments);
        $command->run($input, $output);
    }


    private function runJob($execPath)
    {
        $builder = new ProcessBuilder();
        $builder->setPrefix('sh');
        $command = $builder
            ->setArguments($this->makeShellArguments($execPath))
            ->getProcess()
            ->getCommandLine();
        //$this->output->writeln(sprintf('Executing command: <info> %s</info>', $command));
        $process = new Process($command);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
        echo $process->getOutput();

    }

    private function makeShellArguments($scriptPath)
    {
        $params = array($scriptPath);
        $params = array_merge($this->parseContextParams($this->talendConfig), $params);
        $params = array_merge($this->parseContextParams($this->mailerConfig), $params);
        $params = array_merge($this->parseContextParams($this->intravelDbConfig), $params);
        $params = array_merge($this->parseContextParams($this->tetaDbConfig), $params);

        foreach ($this->synchroConnections as $c) {
            $params = array_merge($this->parseContextParams($c), $params);

        }
        return array_reverse($params);
    }


    private function parseContextParams(array $confPart)
    {
        $params = array();
        foreach ($confPart as $k => $v) {
            $params[] = sprintf("--context_param %s=%s", $k, $v);
        }
        return $params;
    }


    /**
     * @return string
     */
    protected function getCommandDescription()
    {
        return 'Run talend job.';
    }

    /**
     * @return string
     */
    protected function getCommandName()
    {
        return 'talend-runner:talend:run';
    }

    /**
     * {@inheritdoc}
     */
    protected function initializeProperties(InputInterface $input, OutputInterface $output)
    {
        $this->detectJRE();
        $this->container = $this->getContainer();
        $this->input = $input;
        $this->output = $output;
        $this->setUpTasksList();
        $this->synchroConnections = $this->container->getParameter('synchro_connections');
        $this->talendConfig = $this->container->getParameter('talend');
        $this->mailerConfig = $this->getMailerConfigArray();
        $this->intravelDbConfig = $this->getIntravelDbConfigArray();
        $this->tetaDbConfig = $this->getTetaDbConfigArray();

    }

    /**
     * @return array
     */
    private function getMailerConfigArray()
    {

        $params = array();
        $params['mailer_transport'] = $this->container->getParameter('mailer_transport');
        $params['mailer_host'] = $this->container->getParameter('mailer_host');
        $params['mailer_user'] = $this->container->getParameter('mailer_user');
        $params['mailer_password'] = $this->container->getParameter('mailer_password');
        $params['mailer_port'] = $this->container->getParameter('mailer_port');
        $params['mailer_content_type'] = $this->container->getParameter('mailer_content_type');
        $params['mailer_from_name'] = $this->container->getParameter('mailer_from_name');
        $params['mailer_from_address'] = $this->container->getParameter('mailer_from_address');
        return $params;
    }


    /**
     * @return array
     */
    private function getIntravelDbConfigArray()
    {

        $params = array();
        $params['database_driver'] = $this->container->getParameter('database_driver');
        $params['database_host'] = $this->container->getParameter('database_host');
        $params['database_port'] = $this->container->getParameter('database_port');
        $params['database_name'] = $this->container->getParameter('database_name');
        $params['database_user'] = $this->container->getParameter('database_user');
        $params['database_password'] = $this->container->getParameter('database_password');
        return $params;
    }


    /**
     * @return array
     */
    private function getTetaDbConfigArray()
    {
        $params = array();
        $params['teta_database_host'] = $this->container->getParameter('teta_database_host');
        $params['teta_database_port'] = $this->container->getParameter('teta_database_port');
        $params['teta_database_password'] = $this->container->getParameter('teta_database_password');
        $params['teta_database_user'] = $this->container->getParameter('teta_database_user');
        $params['teta_database_name'] = $this->container->getParameter('teta_database_name');
        $params['teta_database_schema'] = $this->container->getParameter('teta_database_schema');
        return $params;
    }


    private function detectJRE()
    {
        $jrePresent = ('yes' === exec('command -v java >/dev/null && echo "yes" || echo "no"')) ? true : false;
        if (!$jrePresent) {
            throw new Exception("You must install JRE to use this feature.");
        }
    }

    private function setUpTasksList()
    {
        $scriptPath = __DIR__;
        $binFolderRealPath = realpath($scriptPath . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . self::TALEND_JOBS_DIR);

        $finder = new Finder();
        $iterator = $finder
            ->files()
            ->name("*.sh")
            ->depth(2)
            ->in($binFolderRealPath);

        foreach ($iterator as $file) {
            $this->jobs[basename($file->getRealpath())] = $file->getRealpath();
        }
    }

}
