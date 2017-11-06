<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace BIS\IPAddr;

class HostIterator extends AddressIterator
{
    /**
     * @param int $version
     * @return string
     */
    protected function iteratorClassName($version)
    {
        return sprintf('\BIS\IPAddr\Iterator\v%d\Host', $version);
    }
}
