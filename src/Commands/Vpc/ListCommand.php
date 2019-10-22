<?php

namespace EzVpc\Commands\Vpc;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use EzVpc\Tools\Helpers\Arr;

class ListCommand extends Command
{
    /**
     * @var string|null The default command name
     */
    protected static $defaultName = 'vpc:list';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('List all the VPCs')
            ->setHelp('Return the the VPCs available for your configured zone.');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sectionProgressBar = $output->section();
        $sectionTable = $output->section();

        $sectionProgressBar->writeln('');
        $progressBar = new ProgressBar($sectionProgressBar);
        $progressBar->setFormat('[%bar%] %percent:3s%% %elapsed:6s% %memory:6s% -- %message%');
        $progressBar->setMessage('Pulling data from AWS...');
        $progressBar->start();

        $vpcs = $this->getApplication()->aws->ec2->describeVpcs()->get('Vpcs');
        $progressBar->advance();

        $progressBar->setMessage('Parsing data...');
        $headers = ['VpcId', 'IsDefault', 'CidrBlock'];
        $vpcs = Arr::filterSelectedProperties($headers, $vpcs);
        $progressBar->advance();

        $table = new Table($sectionTable);
        $table
            ->setHeaders($headers)
            ->setRows(Arr::prepareDataForTable($headers, $vpcs));
        $table->render();
        $progressBar->setMessage('Done!');
        $progressBar->finish();

        $sectionProgressBar->writeln('');
        $sectionTable->writeln('');
    }
}
