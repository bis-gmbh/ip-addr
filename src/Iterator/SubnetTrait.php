<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace BIS\IPAddr\Iterator;

use \BIS\IPAddr\Address;

/**
 * @property Address $subnet
 */
trait SubnetTrait
{
    /**
     * @var int
     */
    protected $dividerPrefixLength;

    /**
     * @var int|string
     */
    protected $numAddrs;

    /**
     * @param int $value
     */
    public function setDividerPrefixLength($value)
    {
        $this->dividerPrefixLength = $value;
        $this->numAddrs = $this->subnet->create(
            sprintf('%s/%d', $this->subnet[0]->addr(), $this->dividerPrefixLength)
        )->numAddrs();
    }

    /**
     * @return Address
     */
    public function current()
    {
        $currentFirstAddr = $this->subnet[$this->subnetIndex()]->addr();
        $currentSubnet = $this->subnet->create(sprintf(
            '%s/%d', $currentFirstAddr, $this->dividerPrefixLength
        ));

        if ( ! $this->subnet->contains($currentSubnet)) {
            $currentSubnet->assign(sprintf('%s - %s', $currentFirstAddr, $this->subnet->last()->addr()));
        }

        return $currentSubnet;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->subnet[$this->subnetIndex()]);
    }

    /**
     * @return int|string
     */
    abstract protected function subnetIndex();
}
