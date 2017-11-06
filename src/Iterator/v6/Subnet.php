<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace BIS\IPAddr\Iterator\v6;

use \BIS\IPAddr\Iterator\SubnetTrait;

class Subnet extends Address
{
    use SubnetTrait;

    /**
     * @return string
     */
    protected function subnetIndex()
    {
        return gmp_strval(gmp_mul($this->index, $this->numAddrs), 10);
    }
}
