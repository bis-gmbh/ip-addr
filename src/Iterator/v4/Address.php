<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace BIS\IPAddr\Iterator\v4;

use \BIS\IPAddr\Address as IPAddr;

class Address implements \Iterator
{
    /**
     * @var int
     */
    protected $index;

    /**
     * @var IPAddr
     */
    protected $subnet;

    public function __construct(IPAddr $subnet)
    {
        $this->index = 0;
        $this->subnet = $subnet;
    }

    /**
     * @return IPAddr
     */
    public function current()
    {
        return $this->subnet[$this->index];
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->subnet[$this->index]);
    }

    public function next()
    {
        ++$this->index;
    }

    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->index;
    }
}
