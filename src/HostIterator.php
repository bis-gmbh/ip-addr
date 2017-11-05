<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace IPAddr;

class HostIterator extends AddressIterator
{
    /**
     * @return Address
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

        // ignore broadcast address for v4
        if (
            $this->subnet->version() == 4
            && $this->subnet->numHosts() > 1
            && ($index > $this->subnet->numHosts())
        ) {
            return false;
        }

        return isset($this->subnet[$index]);
    }

    protected function hostIndex()
    {
        // exclude network address for v4
        return ($this->subnet->version() == 4 && $this->subnet->numAddrs() > 1)
            ? $this->index + 1
            : $this->index;
    }
}
