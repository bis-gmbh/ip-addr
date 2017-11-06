<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace BIS\IPAddr\Iterator\v4;

use \BIS\IPAddr\Address as IPAddr;

class Host extends Address
{
    /**
     * @return IPAddr
     */
    public function current()
    {
        return $this->subnet[$this->hostIndex()];
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $index = $this->hostIndex();

        // ignore broadcast address
        if (($this->subnet->numHosts() > 1) && ($index > $this->subnet->numHosts())) {
            return false;
        }

        return isset($this->subnet[$index]);
    }

    protected function hostIndex()
    {
        // exclude network address except /32 subnets
        return ($this->subnet->numAddrs() > 1) ? $this->index + 1 : $this->index;
    }
}
