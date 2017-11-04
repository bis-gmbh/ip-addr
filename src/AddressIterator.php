<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace IPAddr;

class AddressIterator implements \Iterator
{
    /**
     * @var int
     */
    private $index;

    /**
     * @var Address
     */
    private $subnet;

    public function __construct(Address $subnet)
    {
        $this->index = 0;
        $this->subnet = $subnet;
    }

    /**
     * @return Address
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
