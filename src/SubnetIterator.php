<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace BIS\IPAddr;

/**
 * @property \BIS\IPAddr\Iterator\v4\Subnet|\BIS\IPAddr\Iterator\v6\Subnet $iterator
 */
class SubnetIterator extends AddressIterator
{
    /**
     * @param Address $divisibleSubnet
     * @param int $dividerPrefixLength
     */
    public function __construct(Address $divisibleSubnet, $dividerPrefixLength)
    {
        parent::__construct($divisibleSubnet);

        $iteratorClassName = sprintf('\BIS\IPAddr\Iterator\v%d\Subnet', $divisibleSubnet->version());
        if (class_exists($iteratorClassName)) {
            $this->iterator = new $iteratorClassName($divisibleSubnet, $dividerPrefixLength);
        } else {
            throw new \InvalidArgumentException('Unimplemented iterator for given version');
        }

        $this->iterator->setDividerPrefixLength(intval($dividerPrefixLength));
    }
}
