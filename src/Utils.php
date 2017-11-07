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
     * @return v4|v6
     * @throws \InvalidArgumentException
     */
    public static function make($anyFormat, $maskString = null, $version = null)
    {
        if ($version == 4) {
            return v4::create($anyFormat, $maskString);
        } else if ($version == 6) {
            return v6::create($anyFormat, $maskString);
        } else { // let's try to guess
            if (
                v6::isRange($anyFormat) || v6::isCIDR($anyFormat)
                || v6::isTextual($anyFormat) || v6::isNumeric($anyFormat)
            ) {
                return v6::create($anyFormat, $maskString);
            } else if (
                v4::isRange($anyFormat) || v4::isCIDR($anyFormat)
                || v4::isTextual($anyFormat) || v4::isNumeric($anyFormat)
            ) {
                return v4::create($anyFormat, $maskString);
            }
        }

        throw new \InvalidArgumentException('Wrong arguments');
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
