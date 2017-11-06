<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace BIS\IPAddr;

class AddressIterator implements \Iterator
{
    /**
     * @var \Iterator
     */
    protected $iterator;

    public function __construct(Address $subnet)
    {
        $iteratorClassName = sprintf(
            '\BIS\IPAddr\Iterator\v%d\Address', $subnet->version()
        );
        if (class_exists($iteratorClassName)) {
            $this->iterator = new $iteratorClassName($subnet);
        } else {
            throw new \InvalidArgumentException('Unimlemented iterator for this subnet type');
        }
    }

    /**
     * @return Address
     */
    public function current()
    {
        return $this->iterator->current();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    public function next()
    {
        $this->iterator->next();
    }

    public function rewind()
    {
        $this->iterator->rewind();
    }

    /**
     * @return int|string
     */
    public function key()
    {
        return $this->iterator->key();
    }
}
