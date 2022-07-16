<?php

namespace EzVpc\Tools\Helpers;

class Str
{
    /**
     * Determines if the given $haystack ends with $needle.
     *
     * @param string $haystack
     * @param string $needles
     * @return boolean
     */
    public static function endsWith(string $haystack, string $needle)
    {
        $length = strlen($needle);

        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    public static function formatIpv4Cidr(string $cidr, string $formatFor = 'vpc')
    {
        if (!self::validCidr($cidr, 'ipv4')) {
            throw new \Exception('Invalid IPv4 CIDR block.');
        }

        $cidr = self::splitCidr($cidr);
        switch ($formatFor) {
            case 'vpc':
                $cidr['ip'] = explode('.', $cidr['ip'], 3);
                $cidr['ip'][2] = '0';
                $cidr['ip'][3] = '0';
                $cidr['ip'] = implode('.', $cidr['ip']);
                break;
            case 'subnet':
                $cidr['ip'] = explode('.', $cidr['ip'], 4);
                $cidr['ip'][3] = '0';
                $cidr['ip'] = implode('.', $cidr['ip']);
                break;
        }

        return implode('/', $cidr);
    }

    public static function slugify(string $text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        return $text;
    }

    public static function splitCidr(string $cidr)
    {
        $parts = explode('/', $cidr);
        if (count($parts) != 2) {
            throw new \Exception('Invalid CIDR block.');
        }

        return [
            'ip' => $parts[0],
            'netmask' => intval($parts[1]),
        ];
    }

    /**
     * Determines if the given $haystack starts with $needle.
     *
     * @param string $haystack
     * @param string $needle
     * @return boolean
     */
    public static function startsWith(string $haystack, string $needle)
    {
        return (substr($haystack, 0, strlen($needle)) === $needle);
    }

    /**
     * Determines if the given $cidr is a valid IP CIDR block or not.
     *
     * @param string $cidr
     * @param string $type
     * @return void
     */
    public static function validCidr(string $cidr, string $type = 'ipv4')
    {
        try {
            $cidr = self::splitCidr($cidr);
        } catch (\Exception $e) {
            return false;
        }

        if ($cidr['netmask'] < 0) {
            return false;
        }

        if ($type === 'ipv4') {
            return filter_var($cidr['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
                && $cidr['netmask'] <= 32;
        }

        if ($type === 'ipv6') {
            return filter_var($cidr['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
                && $cidr['netmask'] <= 128;
        }

        return false;
    }
}
