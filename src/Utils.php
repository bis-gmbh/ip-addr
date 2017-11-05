<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace BIS\IPAddr;

class Utils
{
    /**
     * @param mixed $anyFormat
     * @param string|null $maskString
     * @param int|null $version
     * @return false|v4|v6
     */
    public static function make($anyFormat, $maskString = null, $version = null)
    {
        $addr = false;

        switch ($version) {
            case 4:
                $addr = v4::create($anyFormat, $maskString);
                break;
            case 6:
                $addr = v6::create($anyFormat, $maskString);
                break;
            default: // let's try to guess
                if (
                    v6::isRange($anyFormat) || v6::isCIDR($anyFormat)
                    || v6::isTextual($anyFormat) || v6::isNumeric($anyFormat)
                ) {
                    $addr = v6::create($anyFormat, $maskString);
                } else if (
                    v4::isRange($anyFormat) || v4::isCIDR($anyFormat)
                    || v4::isTextual($anyFormat) || v4::isNumeric($anyFormat)
                ) {
                    $addr = v4::create($anyFormat, $maskString);
                }
        }

        return $addr;
    }

    public static function info(Address $addr)
    {
        $data = [
            'ver' => $addr->version(),
            'host' => [
                'addr' => $addr->addr(),
                'bin' => $addr->binary(),
                'dec' => $addr->decimal(),
                'hex' => $addr->hexadecimal(),
                'raddr' => $addr->reverse(),
                'type' => $addr->netType(),
            ],
            'net' => [
                'cidr' => $addr->cidr(),
                'range' => $addr->range(),
                'masklen' => $addr->prefixLength(),
                'hostbits' => $addr->hostBits(),
                'mask' => $addr->mask(),
                'rmask' => $addr->reverseMask(),
                'addrs' => $addr->numAddrs(),
                'hosts' => $addr->numHosts(),
            ],
        ];

        if ($addr instanceof v4) {
            $data['net']['network'] = $addr->network()->addr();
            $data['net']['broadcast'] = $addr->broadcast()->addr();
            $data['net']['class'] = $addr->netClass();
        }

        if ($addr instanceof v6) {
            $data['net']['mask'] = $addr->fullMask();
            $data['net']['first'] = $addr->first()->hexadecimal();
            $data['net']['last'] = $addr->last()->hexadecimal();
        }

        return $data;
    }
}
