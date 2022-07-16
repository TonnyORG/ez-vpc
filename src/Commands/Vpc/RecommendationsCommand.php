<?php

namespace EzVpc\Commands\Vpc;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecommendationsCommand extends Command
{
    /**
     * @var string|null The default command name
     */
    protected static $defaultName = 'vpc:recommendations';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Print a recommended VCP schema')
            ->setHelp('Prints a suggested VCP and sub-networks schema.');
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
    /** */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $formatter = $this->getHelper('formatter');
//$errorMessages = ['Error!', 'Something went wrong'];
//$formattedBlock = $formatter->formatBlock($errorMessages, 'info');
//$output->writeln($formattedBlock);
        $sectionIntroduction = $output->section();
        $sectionTable = $output->section();

        $sectionIntroduction->writeln('');

        $formattedLine = $formatter->formatSection('Info', 'Recommendations');
        $sectionIntroduction->writeln($formattedLine);
        $sectionIntroduction->writeln('');

        $sectionIntroduction->writeln("VPC\n - 10.100.0.0/16\n");
        $sectionIntroduction->writeln("SUBNETS:\n - 10.100.111.0/24\n - 10.100.112.0/24\n - 10.100.113.0/24\n - 10.100.114.0/24\n");

        $sectionIntroduction->writeln('');
        $sectionIntroduction->writeln('- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -');
        $sectionIntroduction->writeln('');
die();
        $vpcs = $this->getApplication()->aws->ec2->describeVpcs()->get('Vpcs');

        $headers = ['VpcId', 'IsDefault', 'CidrBlock'];
        $vpcs = Arr::filterSelectedProperties($headers, $vpcs);

        $table = new Table($sectionTable);
        $table
            ->setHeaders($headers)
            ->setRows(Arr::prepareDataForTable($headers, $vpcs));
        $table->render();

        $sectionTable->writeln('');
    }
}
