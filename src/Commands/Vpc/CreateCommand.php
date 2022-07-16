<?php

namespace EzVpc\Commands\Vpc;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

use EzVpc\Commands\Traits\AwsGetters;
use EzVpc\Tools\Helpers\Arr;
use EzVpc\Tools\Helpers\Str;

class CreateCommand extends Command
{
    /**
     * Custom traits
     */
    use AwsGetters;

    /**
     * @var string|null The default command name
     */
    protected static $defaultName = 'vpc:create';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Create a VPC')
            ->setHelp('Create a new VPC.');
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
        $questionHelper = $this->getHelper('question');
        $formatterHelper = $this->getHelper('formatter');
        $opts = [];

        $sectionTable = $output->section();
        $sectionQuestions = $output;
        $sectionProgressBar = $output->section();

        $progressBar = new ProgressBar($sectionProgressBar);
        $progressBar->setFormat('%message%');
        $progressBar->setMessage('- - -');
        $progressBar->start();



        $progressBar->setMessage("Pulling VPCs from AWS...\n");
        $progressBar->advance();
        $vpcs = Arr::reindexArrayByKey('VpcId', $this->getVpcs());



        $progressBar->setMessage("Pulling Availability Zones from AWS...\n");
        $progressBar->advance();
        $availabilityZones = Arr::reindexArrayByKey('ZoneId', $this->getAvailabilityZones([
            'Filters' => [
                [
                    'Name' => 'state',
                    'Values' => ['available',],
                ],
            ],
        ]));
        foreach ($availabilityZones as $index => $availabilityZone) {
            $explodedZoneName = explode('-', $availabilityZone['ZoneName']);
            $availabilityZones[$index]['EzShortestZoneName'] = end($explodedZoneName);
            unset($explodedZoneName);
        }
    
        $progressBar->setMessage("Parsing AWS data...\n");
        $progressBar->advance();
        $vpcsTableHeaders = ['VpcId', 'IsDefault', 'CidrBlock'];
        $sectionTable->writeln("\n- REGISTERED VPCS");
        $table = new Table($sectionTable);
        $table->setHeaders($vpcsTableHeaders)
        ->setRows(Arr::prepareDataForTable($vpcsTableHeaders, $vpcs));
        $table->render();
        $sectionTable->writeln('');



        $progressBar->setMessage("Configure your new VPC...\n");
        $progressBar->advance();
        $question = new Question('Please enter the name for your new VPC: ');
        while (empty($opts['name'] = Str::slugify($questionHelper->ask($input, $sectionQuestions, $question)))) {
            $error = $formatterHelper->formatSection('ERROR', 'Invalid name', 'error');

            $sectionQuestions->writeln("{$error}\n");
        }

        $question = new Question('Please enter the desired CIDR Block (e.g. 10.10.0.0/16): ');
        while (
            !($opts['cidr'] = $questionHelper->ask($input, $sectionQuestions, $question)) ||
            !Str::validCidr($opts['cidr'], 'ipv4')
        ) {
            $error = $formatterHelper->formatSection('ERROR', 'Invalid CIDR Block', 'error');

            $sectionQuestions->writeln("{$error}\n");
        }
        $opts['cidr'] = Str::formatIpv4Cidr($opts['cidr'], 'vpc');

        $opts['subnets'] = [];
        do {
            $addMoreSubnets = false;
            $newSubnet = [];
            $newSubnetNumber = count($opts['subnets']) + 1;

            $question = new Question("Please enter the name for your subnet #{$newSubnetNumber}: ");
            while (empty($newSubnet['name'] = Str::slugify($questionHelper->ask($input, $sectionQuestions, $question)))) {
                $error = $formatterHelper->formatSection('ERROR', 'Invalid subnet name', 'error');

                $sectionQuestions->writeln("{$error}\n");
            }

            $question = new Question('Please enter the desired CIDR Block (e.g. 10.10.10.0/24): ');
            while (
                !($newSubnet['cidr'] = $questionHelper->ask($input, $sectionQuestions, $question)) ||
                !Str::validCidr($newSubnet['cidr'], 'ipv4')
            ) {
                $error = $formatterHelper->formatSection('ERROR', 'Invalid CIDR Block', 'error');

                $sectionQuestions->writeln("{$error}\n");
            }
            $newSubnet['cidr'] = Str::formatIpv4Cidr($newSubnet['cidr'], 'subnet');

            $question = new ChoiceQuestion(
                'Please select the availability zones that you would like to configure for this subnet (type the numbers separated by comma, e.g. 0,1,N)',
                array_keys($availabilityZones));
            $question->setMultiselect(true);
            $newSubnet['zonesIds'] = $questionHelper->ask($input, $sectionQuestions, $question);
            foreach ($newSubnet['zonesIds'] as $index => $zoneId) {
                $newSubnet['zonesIds'][$index] = [
                    'AvailabilityZoneId' => $zoneId,
                    'CidrBlock' => '',
                ];
            }

            $opts['subnets'][] = $newSubnet;
        } while ($addMoreSubnets);





var_dump($opts);
die();



#x
#$question = new ChoiceQuestion('Please select the resources to tag (defaults to all, type numbers separated by comma)', $ids, implode(',', array_keys($ids)));
#$question->setMultiselect(true);
#$selectedIds = $helper->ask($input, $sectionQuestions, $question);
#
#$sectionQuestions->writeln('');
#$sectionQuestions->writeln("The following resources were selected:\n- " . implode("\n- ", $selectedIds) . "\n");
#
#$question = new Question('Please enter the Name for tagging all these sources (default value is "default")', 'default');
#$tagValue = $helper->ask($input, $sectionQuestions, $question);

        $progressBar->setMessage('Parsing data...');
        $headers = ['VpcId', 'IsDefault', 'CidrBlock'];
        $vpcs = Arr::filterSelectedProperties($headers, $vpcs);
        $progressBar->advance();

        $table = new Table($sectionTable);
        $table
            ->setHeaders($headers)
            ->setRows(Arr::prepareDataForTable($headers, $vpcs));

        $progressBar->finish();
        $progressBar->clear();

        $table->render();
        $sectionTable->writeln('');

        /*$vpcs = $this->getApplication()->aws->ec2->createVpc([
            'AmazonProvidedIpv6CidrBlock' => true || false,
            'CidrBlock' => '10.100.100.0/16', // REQUIRED
            'DryRun' => true,
            'InstanceTenancy' => 'default',
        ]);*/
        $result = $this->getApplication()->aws->ec2->describeTags([
            'Filters' => [
                [
                    'Name' => 'resource-id',
                    'Values' => [
                        'vpc-b9d167c1',
                    ],
                ],
            ],
        ]);
var_dump($result->toArray());
die();
$result = $this->getApplication()->aws->ec2->createTags([
    'Resources' => [
        'vpc-b9d167c1',
    ],
    'Tags' => [
        [
            'Key' => 'Name',
            'Value' => 'defaults',
        ],
    ],
]);
var_dump($result->toArray());
    }
}
