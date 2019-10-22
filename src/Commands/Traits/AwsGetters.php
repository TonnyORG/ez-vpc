<?php

namespace EzVpc\Commands\Traits;

trait AwsGetters {
    /**
     * Returns the "test mode" filder for AWS API calls.
     *
     * @return void
     */
    public function getTestingFilter()
    {
        return [
            'DryRun' => $this->getApplication()->aws->_testMode,
        ];
    }

    /**
     * Returns the value for the tag that match with the given $key.
     *
     * @param string $key
     * @param array $item
     * @param string $tagsKey
     * @return string|null
     */
    public function getTagValueByKey(string $key, array $item, string $tagsKey = 'Tags')
    {
        if (!isset($item[$tagsKey])) {
            return null;
        }

        foreach ($item[$tagsKey] as $tag) {
            if ($tag['Key'] === $key) {
                return $tag['Value'];
            }
        }

        return null;
    }

    /**
     * Wrapper for "describeAvailabilityZones".
     *
     * @param array $params
     * @return array
     */
    public function getAvailabilityZones(array $params = [])
    {
        $params = array_merge($this->getTestingFilter(), $params);

        return $this->getApplication()->aws->ec2
            ->describeAvailabilityZones($params)
            ->get('AvailabilityZones');
    }

    /**
     * Wrapper for "describeDhcpOptions".
     *
     * @param array $params
     * @return array
     */
    public function getDhcpOptions(array $params = [])
    {
        $params = array_merge($this->getTestingFilter(), $params);

        return $this->getApplication()->aws->ec2
            ->describeDhcpOptions($params)
            ->get('DhcpOptions');
    }

    /**
     * Wrapper for "describeInternetGateways".
     *
     * @param array $params
     * @return array
     */
    public function getInternetGateways(array $params = [])
    {
        $params = array_merge($this->getTestingFilter(), $params);

        return $this->getApplication()->aws->ec2
            ->describeInternetGateways($params)
            ->get('InternetGateways');
    }

    /**
     * Wrapper for "describeNetworkAcls".
     *
     * @param array $params
     * @return array
     */
    public function getNetworkAcls(array $params = [])
    {
        $params = array_merge($this->getTestingFilter(), $params);

        return $this->getApplication()->aws->ec2
            ->describeNetworkAcls($params)
            ->get('NetworkAcls');
    }

    /**
     * Wrapper for "describeRouteTables".
     *
     * @param array $params
     * @return array
     */
    public function getRouteTables(array $params = [])
    {
        $params = array_merge($this->getTestingFilter(), $params);

        return $this->getApplication()->aws->ec2
            ->describeRouteTables($params)
            ->get('RouteTables');
    }

    /**
     * Wrapper for "describeSecurityGroups".
     *
     * @param array $params
     * @return array
     */
    public function getSecurityGroups(array $params = [])
    {
        $params = array_merge($this->getTestingFilter(), $params);

        return $this->getApplication()->aws->ec2
            ->describeSecurityGroups($params)
            ->get('SecurityGroups');
    }

    /**
     * Wrapper for "describeSubnets".
     *
     * @param array $params
     * @return array
     */
    public function getSubnets(array $params = [])
    {
        $params = array_merge($this->getTestingFilter(), $params);

        return $this->getApplication()->aws->ec2
            ->describeSubnets($params)
            ->get('Subnets');
    }

    /**
     * Wrapper for "describeVpcs".
     *
     * @param array $params
     * @return array
     */
    public function getVpcs(array $params = [])
    {
        $params = array_merge($this->getTestingFilter(), $params);

        return $this->getApplication()->aws->ec2
            ->describeVpcs($params)
            ->get('Vpcs');
    }
}
