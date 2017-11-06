<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace BIS\IPAddr\Iterator\v4;

use \BIS\IPAddr\Iterator\SubnetTrait;

class Subnet extends Address
{
    use SubnetTrait;

    /**
     * @return int
     */
    protected function subnetIndex()
    {
        return $this->index * $this->numAddrs;
    }
}
