<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace BIS\IPAddr;

class v4 extends BaseAddress
{
    const REGEXP_IP = '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){0,3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/i';
    const REGEXP_CIDR = '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){0,3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\/([0-9]|1[0-9]|2[0-9]|3[0-2])$/i';

    public static $privateNetworks = ['10/8', '172.16/12', '192.168/16']; // rfc1918
    public static $multicastNetworks = ['224/4']; // rfc3171
    public static $reservedNetworks = ['240/4']; // rfc1112
    public static $networkTypes = [ // rfc5735
        [
            'AddressBlock' => '0.0.0.0/8',
            'PresentUse' => '"This" Network',
            'Reference' => 'RFC 1122, Section 3.2.1.3',
        ], [
            'AddressBlock' =>'10.0.0.0/8',
            'PresentUse' =>'Private-Use Networks',
            'Reference' =>'RFC 1918'
        ], [
            'AddressBlock' =>'127.0.0.0/8',
            'PresentUse' =>'Loopback',
            'Reference' =>'RFC 1122, Section 3.2.1.3'
        ], [
            'AddressBlock' =>'169.254.0.0/16',
            'PresentUse' =>'Link Local',
            'Reference' =>'RFC 3927'
        ], [
            'AddressBlock' =>'172.16.0.0/12',
            'PresentUse' =>'Private-Use Networks',
            'Reference' =>'RFC 1918'
        ], [
            'AddressBlock' =>'192.0.0.0/24',
            'PresentUse' =>'IETF Protocol Assignments',
            'Reference' =>'RFC 5736'
        ], [
            'AddressBlock' =>'192.0.2.0/24',
            'PresentUse' =>'TEST-NET-1',
            'Reference' =>'RFC 5737'
        ], [
            'AddressBlock' =>'192.88.99.0/24',
            'PresentUse' =>'6to4 Relay Anycast',
            'Reference' =>'RFC 3068'
        ], [
            'AddressBlock' =>'192.168.0.0/16',
            'PresentUse' =>'Private-Use Networks',
            'Reference' =>'RFC 1918'
        ], [
            'AddressBlock' =>'198.18.0.0/15',
            'PresentUse' =>'Network Interconnect, Device Benchmark Testing',
            'Reference' =>'RFC 2544'
        ], [
            'AddressBlock' =>'198.51.100.0/24',
            'PresentUse' =>'TEST-NET-2',
            'Reference' =>'RFC 5737'
        ], [
            'AddressBlock' =>'203.0.113.0/24',
            'PresentUse' =>'TEST-NET-3',
            'Reference' =>'RFC 5737'
        ], [
            'AddressBlock' =>'224.0.0.0/4',
            'PresentUse' =>'Multicast',
            'Reference' =>'RFC 3171'
        ], [
            'AddressBlock' =>'240.0.0.0/4',
            'PresentUse' =>'Reserved for Future Use',
            'Reference' =>'RFC 1112, Section 4'
        ], [
            'AddressBlock' =>'255.255.255.255/32',
            'PresentUse' =>'Limited Broadcast',
            'Reference' =>'RFC 919, Section 7, RFC 922, Section 7'
        ]
    ];

    protected static $version = 4;
    protected static $maxPrefixLength = 32;

    protected static $octetCount = 4;
    protected static $octetOffsets = [24, 16, 8, 0];
    protected static $octetMasks = [0xFF000000, 0x00FF0000, 0x0000FF00, 0x000000FF];

    public function __construct($anyFormat = null, $mask = null)
    {
        $this->addr = 0;
        $this->mask = 0xFFFFFFFF;

        if ($anyFormat !== null) {
            $this->assign($anyFormat, $mask);
        }
    }

    /**
     * @param mixed $long
     * @return bool
     */
    public static function isNumeric($long)
    {
        return is_int($long) && ($long >= 0 && $long <= 0xFFFFFFFF);
    }

    /**
     * @param mixed $addr
     * @return bool
     */
    public static function isTextual($addr)
    {
        return is_string($addr) && (bool)preg_match(self::REGEXP_IP, $addr);
    }

    /**
     * @param mixed $cidr
     * @return bool
     */
    public static function isCIDR($cidr)
    {
        return is_string($cidr) && (bool)preg_match(self::REGEXP_CIDR, $cidr);
    }

    /**
     * @return string
     */
    public function binary()
    {
        return '0b' . str_pad(decbin($this->addr), 32, '0', STR_PAD_LEFT);
    }

    public function decimal()
    {
        return $this->addr;
    }

    public function hexadecimal()
    {
        return '0x' . str_pad(dechex($this->addr), 8, '0', STR_PAD_LEFT);
    }

    public function netmask()
    {
        return $this->mask;
    }

    public function prefixLength()
    {
        $bitsCount = 0;

        for ($i=0; $i<self::$maxPrefixLength; $i++) {
            $bitsCount += ($this->not($this->mask) >> $i) & 1;
        }

        return self::$maxPrefixLength - $bitsCount;
    }

    /**
     * @return Address|v4
     */
    public function first()
    {
        return new self($this->internalFirstAddr());
    }

    /**
     * @return Address|v4
     */
    public function last()
    {
        return new self($this->internalLastAddr());
    }

    public function numAddrs()
    {
        $prefixLength = $this->prefixLength();

        if ($prefixLength === self::$maxPrefixLength) {
            return 1;
        } else if ($prefixLength === 0) {
            return $this->not($this->mask);
        } else {
            return $this->not($this->mask) + 1;
        }
    }

    public function numHosts()
    {
        $num = $this->numAddrs();
        return ($num > 2) ? ($num - 2) : 1;
    }

    public function ltEq(Address $addr)
    {
        return $this->addr <= $addr->decimal();
    }

    public function gtEq(Address $addr)
    {
        return $this->addr >= $addr->decimal();
    }

    public function addr()
    {
        return $this->toTextual($this->addr);
    }

    public function mask()
    {
        return $this->toTextual($this->mask);
    }

    public function cidr()
    {
        return $this->toTextual($this->addr) . '/' . $this->prefixLength();
    }

    public function range()
    {
        return $this->toTextual($this->internalFirstAddr()) . ' - ' . $this->toTextual($this->internalLastAddr());
    }

    public function reverse()
    {
        $octets = explode('.', $this->toTextual($this->addr));
        return implode('.', array_reverse($octets)) . '.in-addr.arpa.';
    }

    public function reverseMask()
    {
        $octets = explode('.', $this->toTextual($this->mask));
        return implode('.', array_reverse($octets));
    }

    public function netType()
    {
        for ($i=0; $i<count(self::$networkTypes); $i++) {
            if ($this->within(self::create(self::$networkTypes[$i]['AddressBlock']))) {
                return self::$networkTypes[$i]['PresentUse'];
            }
        }

        return 'Public';
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return (($this->internalFirstAddr() + intval($offset)) <= $this->internalLastAddr());
    }

    /**
     * @param int $offset
     * @return v4
     */
    public function offsetGet($offset)
    {
        return new self($this->internalFirstAddr() + intval($offset));
    }

    /**
     * @return Address|v4
     */
    public function network()
    {
        return $this->first();
    }

    /**
     * @return Address|v4
     */
    public function broadcast()
    {
        return $this->last();
    }

    /**
     * @return string
     */
    public function netClass()
    {
        if ($this->within(self::$privateNetworks)) {
            return 'E';
        } else if ($this->within(self::$multicastNetworks)) {
            return 'D';
        } else if ($this->mask >= 0xFFFFFF00) {
            return 'C';
        } else if ($this->mask >= 0xFFFF0000) {
            return 'B';
        } else if ($this->mask >= 0xFF000000) {
            return 'A';
        }
        return '-';
    }

    /**
     * @param string $addr
     * @return int
     */
    protected function fromTextual($addr)
    {
        if (self::isTextual($addr) === false) {
            throw new \InvalidArgumentException('Wrong addr format');
        }

        $num = 0;
        $octets = explode('.', $addr, self::$octetCount);
        $restoredZeroOctetsCount = self::$octetCount - count($octets);

        for ($i=0; $i<$restoredZeroOctetsCount; $i++) {
            array_push($octets, "0");
        }

        for ($i=0; $i<self::$octetCount; $i++) {
            $num |= intval($octets[$i]) << self::$octetOffsets[$i];
        }

        return $num;
    }

    /**
     * @param int $addr
     * @return string
     */
    private function toTextual($addr)
    {
        if (self::isNumeric($addr) === false) {
            throw new \InvalidArgumentException('Wrong addr format');
        }

        $octets = [];

        for ($i=0; $i<self::$octetCount; $i++) {
            array_push($octets, ($addr & self::$octetMasks[$i]) >> self::$octetOffsets[$i]);
        }

        return implode('.', $octets);
    }

    /**
     * @param int $prefixLength
     * @return int
     */
    protected function maskFromPrefixLength($prefixLength)
    {
        $mask = 0xFFFFFFFF;

        if ($prefixLength < self::$maxPrefixLength) {
            $mask <<= (self::$maxPrefixLength - $prefixLength);
        }

        return (PHP_INT_SIZE == 8 ? $mask & 0x00000000FFFFFFFF : $mask);
    }

    protected function findCommonMask($firstAddr, $secondAddr)
    {
        $commonMask = 0;

        for ($i = self::$maxPrefixLength; $i >= 0; $i--) {
            $commonMask = $this->maskFromPrefixLength($i);
            $newNetworkAddr = $firstAddr & $commonMask;
            $newBroadcastAddr = $newNetworkAddr + $this->not($commonMask);
            if ($secondAddr >= $newNetworkAddr && $secondAddr <= $newBroadcastAddr) {
                break;
            }
        }

        return $commonMask;
    }

    /**
     * @return int
     */
    private function internalFirstAddr()
    {
        return ($this->addr & $this->mask);
    }

    /**
     * @return int
     */
    private function internalLastAddr()
    {
        return (($this->addr & $this->mask) + $this->not($this->mask));
    }

    private function not($value)
    {
        return (PHP_INT_SIZE == 8 ? (~$value) & 0x00000000FFFFFFFF : ~$value);
    }
}
