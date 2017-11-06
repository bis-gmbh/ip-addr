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
    public function __construct(Address $subnet)
    {
        parent::__construct($subnet);

        // overwrite iterator object
        $iteratorClassName = sprintf('\BIS\IPAddr\Iterator\v%d\Host', $subnet->version());
        if (class_exists($iteratorClassName)) {
            $this->iterator = new $iteratorClassName($subnet);
        } else {
            throw new \InvalidArgumentException('Unimplemented iterator for given version');
        }
    }
}
