<?php

namespace EzVpc\Commands\Vpc;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

use EzVpc\Commands\Traits\AwsGetters;
use EzVpc\Tools\Helpers\Arr;
use EzVpc\Tools\Helpers\Str;

class TagDefaultsCommand extends Command
{
    /**
     * Custom traits
     */
    use AwsGetters;

    /**
     * @var string|null The default command name
     */
    protected static $defaultName = 'vpc:tag-defaults';

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Tag default VPCs and sub-elements')
            ->setHelp('Tag default VPCs, Subnets, IGWs, and other elements under VPC.');
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
        $vpcIds = [];

        $resources = [
            'dopt' => [
                'label' => 'DHCP Options',
                'idKey' => 'DhcpOptionsId',
                'items' => [],
                'getMethod' => 'getDhcpOptions',
                'filters' => [],
            ],
            'vpc' => [
                'label' => 'VPC',
                'idKey' => 'VpcId',
                'items' => [],
                'getMethod' => 'getVpcs',
                'filters' => [
                    'Filters' => [
                        [
                            'Name' => 'isDefault',
                            'Values' => ['true'],
                        ],
                    ],
                ],
            ],
            'subnet' => [
                'label' => 'Subnet',
                'idKey' => 'SubnetId',
                'suffixKey' => 'AvailabilityZone',
                'items' => [],
                'getMethod' => 'getSubnets',
                'filters' => [
                    'Filters' => [
                        [
                            'Name' => 'defaultForAz',
                            'Values' => ['true'],
                        ],
                        [
                            'Name' => 'vpc-id',
                            'Values' => &$vpcIds,
                        ],
                    ],
                ],
            ],
            'rtb' => [
                'label' => 'Route Table',
                'idKey' => 'RouteTableId',
                'items' => [],
                'getMethod' => 'getRouteTables',
                'filters' => [
                    'Filters' => [
                        [
                            'Name' => 'association.main',
                            'Values' => ['true'],
                        ],
                        [
                            'Name' => 'vpc-id',
                            'Values' => &$vpcIds,
                        ],
                    ],
                ],
            ],
            'igw' => [
                'label' => 'Internet Gateway',
                'idKey' => 'InternetGatewayId',
                'items' => [],
                'getMethod' => 'getInternetGateways',
                'filters' => [
                    'Filters' => [
                        [
                            'Name' => 'attachment.vpc-id',
                            'Values' => &$vpcIds,
                        ],
                    ],
                ],
            ],
            'acl' => [
                'label' => 'Network Acl',
                'idKey' => 'NetworkAclId',
                'items' => [],
                'getMethod' => 'getNetworkAcls',
                'filters' => [
                    'Filters' => [
                        [
                            'Name' => 'default',
                            'Values' => ['true'],
                        ],
                        [
                            'Name' => 'vpc-id',
                            'Values' => &$vpcIds,
                        ],
                    ],
                ],
            ],
            'sg' => [
                'label' => 'Security Group',
                'idKey' => 'GroupId',
                'items' => [],
                'getMethod' => 'getSecurityGroups',
                'filters' => [
                    'Filters' => [
                        [
                            'Name' => 'group-name',
                            'Values' => ['default'],
                        ],
                        [
                            'Name' => 'vpc-id',
                            'Values' => &$vpcIds,
                        ],
                    ],
                ],
            ],
        ];

        $ids = [];
        $suffixedPrefixes = [];
        $rows = [];
        foreach ($resources as $resourcePrefix => $resource) {
            $resources[$resourcePrefix]['items'] = Arr::reindexArrayByKey(
                $resource['idKey'],
                $this->{$resource['getMethod']}($resource['filters'])
            );

            if ($resourcePrefix === 'vpc') {
                $vpcIds = array_keys($resources['vpc']['items']);
            }

            if (!empty($resource['suffixKey'])) {
                $suffixedPrefixes[] = $resourcePrefix;
            }

            foreach ($resources[$resourcePrefix]['items'] as $itemId => $item) {
                $ids[] = $itemId;

                $rows[] = [
                    $resources[$resourcePrefix]['label'],
                    $itemId,
                    $this->getTagValueByKey('Name', $item) ?: '',
                ];
            }
        }

        $sectionTable = $output->section();
        $table = new Table($sectionTable);
        $table
            ->setHeaders(['Resource Type', 'Resource ID', 'Current Name Tag Value'])
            ->setRows($rows)
        ;
        $table->render();
        $sectionTable->writeln('');

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion('Please select the resources to tag (defaults to all, type numbers separated by comma)', $ids, implode(',', array_keys($ids)));
        $question->setMultiselect(true);
        $selectedIds = $helper->ask($input, $output, $question);

        $output->writeln('');
        $output->writeln("The following resources were selected:\n- " . implode("\n- ", $selectedIds) . "\n");

        $question = new Question('Please enter the Name for tagging all these sources (default value is "default")', 'default');
        $tagValue = $helper->ask($input, $output, $question);

        $bulkableIds = array_filter($selectedIds, function($id) use ($resources, $suffixedPrefixes) {
            foreach ($suffixedPrefixes as $prefix) {
                if (Str::startsWith($id, $prefix)) {
                    return false;
                }
            }

            return true;
        });

        if (count($bulkableIds)) {
            $this->createDefaultNameTagForIds($bulkableIds, $tagValue);
        }

        $suffixedIds = array_diff($selectedIds, $bulkableIds);
        foreach ($suffixedIds as $resourceId) {
            $resourceParts = explode('-', $resourceId, 2);

            if (count($resourceParts) !== 2) {
                continue;
            }

            $suffixKey = $resources[$resourceParts[0]]['suffixKey'];
            $suffix = $resources[$resourceParts[0]]['items'][$resourceId][$suffixKey];

            $this->createDefaultNameTagForIds([$resourceId], $tagValue, "-{$suffix}");
        }
    }

    protected function createDefaultNameTagForIds(array $resourceIds, string $tagValue = 'default', string $tagValueSuffix = '')
    {
        return $this->getApplication()->aws->ec2->createTags([
            'DryRun' => $this->getApplication()->aws->_testMode,
            'Resources' => $resourceIds,
            'Tags' => [
                [
                    'Key' => 'Name',
                    'Value' => "{$tagValue}{$tagValueSuffix}",
                ],
            ],
        ]);
    }
}
