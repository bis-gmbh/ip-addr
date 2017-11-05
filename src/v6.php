<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace BIS\IPAddr;

class v6 extends BaseAddress
{
    const REGEXP_PREFIX_LENGTH = '/^([0-9]|[1-9][0-9]|1[0-1][0-9]|1[0-9][0-8])$/i'; // 0-128

    public static $addressTypes = [ // rfc4291
        [
            'IPv6Notation' => '::/128',
            'AddressType' => 'Unspecified',
        ], [
            'IPv6Notation' => '::1/128',
            'AddressType' => 'Loopback',
        ], [ // rfc4038
            'IPv6Notation' => '::FFFF/96',
            'AddressType' => 'IPv4-Mapped IPv6 Address',
        ], [ // rfc4193
            'IPv6Notation' => 'FC00::/7',
            'AddressType' => 'Unique Site-Local',
        ], [
            'IPv6Notation' => 'FF00::/8',
            'AddressType' => 'Multicast',
        ], [
            'IPv6Notation' => 'FE80::/10',
            'AddressType' => 'Link-Local unicast',
        ]
    ];

    public function __construct($anyFormat = null, $mask = null)
    {
        if ( ! defined('AF_INET6')) {
            throw new \RuntimeException('PHP must be compiled with --enable-ipv6 option');
        }
        if ( ! extension_loaded('gmp')) {
            throw new \RuntimeException('Extension GMP not loaded');
        }

        $this->version = 6;
        $this->addr = gmp_init(0);
        $this->mask = gmp_init('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF');
        $this->maxPrefixLength = 128;

        if ($anyFormat !== null) {
            $this->assign($anyFormat, $mask);
        }
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isNumeric($value)
    {
        return (
            (is_string($value) || is_numeric($value))
            && preg_match('/^(0|0x)?[0-9a-f]+$/i', $value)
        );
    }

    /**
     * @param mixed $addr
     * @return bool
     */
    public static function isTextual($addr)
    {
        return filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * @param mixed $cidr
     * @return bool
     */
    public static function isCIDR($cidr)
    {
        if (false === is_string($cidr)) {
            return false;
        }

        $cidrParts = explode('/', $cidr);

        if (
            count($cidrParts) === 2
            && self::isTextual($cidrParts[0])
            && preg_match(self::REGEXP_PREFIX_LENGTH, $cidrParts[1])
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function binary()
    {
        return '0b' . str_pad(gmp_strval($this->addr, 2), 128, '0', STR_PAD_LEFT);
    }

    public function decimal()
    {
        return gmp_strval($this->addr, 10);
    }

    public function hexadecimal()
    {
        return '0x' . str_pad(gmp_strval($this->addr, 16), 32, '0', STR_PAD_LEFT);
    }

    /**
     * @return string hexadecimal netmask representation
     */
    public function netmask()
    {
        return '0x' . str_pad(gmp_strval($this->mask, 16), 32, '0', STR_PAD_LEFT);
    }

    public function prefixLength()
    {
        for (
            $i = 0, $bitsCount = 0;
            $i < $this->maxPrefixLength;
            gmp_testbit($this->mask, $i) ? $bitsCount++ : null, $i++
        );

        return $bitsCount;
    }

    /**
     * @return v6
     */
    public function first()
    {
        return new self(gmp_strval($this->internalFirstAddr(), 10));
    }

    /**
     * @return v6
     */
    public function last()
    {
        return new self(gmp_strval($this->internalLastAddr(), 10));
    }

    /**
     * @return string
     */
    public function numAddrs()
    {
        $prefixLength = $this->prefixLength();

        if ($prefixLength === $this->maxPrefixLength) {
            $num = gmp_init(1);
        } else if ($prefixLength === 0) {
            $num = $this->gmp_not($this->mask);
        } else {
            $num = gmp_add($this->gmp_not($this->mask), gmp_init(1));
        }
        return gmp_strval($num, 10);
    }

    /**
     * @return string
     */
    public function numHosts()
    {
        $num = gmp_init($this->numAddrs());

        if (gmp_cmp($num, gmp_init(2)) > 0) {
            return gmp_strval(gmp_sub($num, gmp_init(2)), 10);
        }

        return '1';
    }

    public function ltEq(Address $addr)
    {
        $beginScopeCmp = gmp_cmp($this->addr, gmp_init($addr->decimal()));

        return $beginScopeCmp === 0 || $beginScopeCmp < 0;
    }

    public function gtEq(Address $addr)
    {
        $endScopeCmp = gmp_cmp($this->addr, gmp_init($addr->decimal()));

        return $endScopeCmp === 0 || $endScopeCmp > 0;
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
        return sprintf('%s/%d', $this->toTextual($this->addr), $this->prefixLength());
    }

    public function range()
    {
        return $this->toTextual($this->internalFirstAddr()) . ' - ' . self::toTextual($this->internalLastAddr());
    }

    /**
     * @see https://www.ripe.net/manage-ips-and-asns/db/support/configuring-reverse-dns
     * @return string
     */
    public function reverse()
    {
        $reversedHex = strrev(str_pad(gmp_strval($this->addr, 16), 32, '0', STR_PAD_LEFT));

        return implode('.', str_split($reversedHex)) . '.ip6.arpa.';
    }

    public function reverseMask()
    {
        $groups = str_split(str_pad(gmp_strval($this->mask, 16), 32, '0', STR_PAD_LEFT), 4);
        return implode(':', array_reverse($groups));
    }

    public function netType()
    {
        for ($i=0; $i<count(self::$addressTypes); $i++) {
            if ($this->within(self::create(self::$addressTypes[$i]['IPv6Notation']))) {
                return self::$addressTypes[$i]['AddressType'];
            }
        }

        return 'Global Unicast';
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return gmp_cmp(gmp_add($this->internalFirstAddr(), gmp_init($offset)), $this->internalLastAddr()) <= 0;
    }

    /**
     * @param int $offset
     * @return v6
     */
    public function offsetGet($offset)
    {
        return new self(gmp_strval(gmp_add($this->internalFirstAddr(), gmp_init($offset)), 10));
    }

    /**
     * @return string full textual address
     */
    public function full()
    {
        return $this->internalFull($this->addr);
    }

    /**
     * @return string full textual mask
     */
    public function fullMask()
    {
        return $this->internalFull($this->mask);
    }

    /**
     * @return string full textual address mixed with v4 address
     */
    public function full4()
    {
        $unpaddedHex = gmp_strval($this->addr, 16);
        $solidFullHex = str_pad($unpaddedHex, 32, '0', STR_PAD_LEFT);
        $groups = str_split($solidFullHex, 4);

        $octets = array_merge(str_split($groups[6], 2), str_split($groups[7], 2));
        $octets = array_map('hexdec', $octets);
        $v4 = implode('.', $octets);
        unset($groups[7]);
        $groups[6] = $v4;

        return implode(':', $groups);
    }

    /**
     * @return string compressed textual address
     */
    public function compressed()
    {
        $unpaddedHex = gmp_strval($this->addr, 16);
        $solidFullHex = str_pad($unpaddedHex, 32, '0', STR_PAD_LEFT);
        $groups = str_split($solidFullHex, 4);
        $groups = array_map(function ($a) { return preg_replace('/^0{1,3}/i', '', $a); }, $groups);
        $compactFullAddr = implode(':', $groups);

        return $this->compress($compactFullAddr);
    }

    /**
     * @return string compressed textual address mixed with v4 address
     */
    public function compressed4()
    {
        $unpaddedHex = gmp_strval($this->addr, 16);
        $solidFullHex = str_pad($unpaddedHex, 32, '0', STR_PAD_LEFT);
        $groups = str_split($solidFullHex, 4);

        $v6Part = array_slice($groups, 0, 6);
        $v6Part = array_map(function ($a) { return preg_replace('/^0{1,3}/i', '', $a); }, $v6Part);
        $full_addr = implode(':', $v6Part);

        $v4Part = array_slice($groups, 6, 2);
        $octets = array_merge(str_split($v4Part[0], 2), str_split($v4Part[1], 2));
        $octets = array_map('hexdec', $octets);
        $v4 = implode('.', $octets);

        $compressed = $this->compress($full_addr . ':' . $v4);

        // special case, when v4 part starts from ':0.', then we get ':.'. Revert it back:
        $compressed = str_replace(':.', ':0.', $compressed);

        // special case, when v6 part is unspecified (::)
        $compressed = preg_replace('/^:(\d+)/i', '::$1', $compressed, 1);

        return $compressed;
    }

    /**
     * @param resource|\GMP
     * @return string full textual address
     */
    private function internalFull($addr)
    {
        $unpaddedHex = gmp_strval($addr, 16);
        $solidFullHex = str_pad($unpaddedHex, 32, '0', STR_PAD_LEFT);
        $groups = str_split($solidFullHex, 4);

        return implode(':', $groups);
    }

    /**
     * @param string $hexAddr textual address in hexadecimal format
     * @return string
     */
    private function compress($hexAddr)
    {
        $zeroChains = $leadingZeroChain = $trailingZeroChain = $allZeroChain = $maxChains = [];

        // find max sequence of zero groups for replacement
        $cmpZeroCount = function ($a, $b) {
            $za = $zb = 0;

            for ($i=0; $i<strlen($a); $i++) {
                if ($a[$i] == '0') {
                    $za++;
                }
            }
            for ($i=0; $i<strlen($b); $i++) {
                if ($b[$i] == '0') {
                    $zb++;
                }
            }

            if ($za == $zb) {
                return 0;
            }

            return ($za < $zb) ? -1 : 1;
        };

        preg_match_all('/:0{1,4}(:0{1,4}){0,5}:/i', $hexAddr, $zeroChains);
        preg_match('/^0{1,4}:(0{1,4}:){0,6}/i', $hexAddr, $leadingZeroChain);
        preg_match('/(:0{1,4}){0,6}:0{1,4}$/i', $hexAddr, $trailingZeroChain);
        preg_match('/^0{1,4}(:0{1,4}){7}$/i', $hexAddr, $allZeroChain);

        if (isset($zeroChains[0])) {
            usort($zeroChains[0], $cmpZeroCount);
            $maxChain = end($zeroChains[0]);
            if ($maxChain) {
                $maxChains[] = $maxChain;
            }
        }
        if (isset($leadingZeroChain[0])) {
            $maxChains[] = $leadingZeroChain[0];
        }
        if (isset($trailingZeroChain[0])) {
            $maxChains[] = $trailingZeroChain[0];
        }
        if (isset($allZeroChain[0])) {
            $maxChains[] = $allZeroChain[0];
        }

        if (count($maxChains) > 0) {
            usort($maxChains, $cmpZeroCount);
            $maxChain = end($maxChains);
            if ($maxChain) {
                return preg_replace(sprintf('/%s/i', $maxChain), '::', $hexAddr, 1);
            }
        }

        return $hexAddr;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function fromNumeric($value)
    {
        return gmp_init($value);
    }

    /**
     * @param string $addr
     * @return resource|\GMP
     */
    protected function fromTextual($addr)
    {
        if (self::isTextual($addr) === false) {
            throw new \InvalidArgumentException('Wrong addr format');
        }

        return gmp_init(current(unpack('H32', inet_pton($addr))), 16);
    }

    /**
     * @param resource|\GMP $addr internal address value
     * @return string
     */
    private function toTextual($addr)
    {
        $hex = str_pad(gmp_strval($addr, 16), 32, '0', STR_PAD_LEFT);
        $packed = hex2bin($hex);

        return inet_ntop($packed);
    }

    /**
     * @param int $prefixLength
     * @return resource|object
     */
    protected function maskFromPrefixLength($prefixLength)
    {
        for (
            $i = 0, $mask = gmp_init('0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF');
            $i < ($this->maxPrefixLength - $prefixLength);
            gmp_clrbit($mask, $i), $i++
        );

        return $mask;
    }

    protected function findCommonMask($firstAddr, $secondAddr)
    {
        $commonMask = gmp_init(0);

        for ($i = $this->maxPrefixLength; $i >= 0; $i--) {
            $commonMask = $this->maskFromPrefixLength($i);
            $newNetworkAddr = gmp_and($firstAddr, $commonMask);
            $newBroadcastAddr = gmp_add($newNetworkAddr, $this->gmp_not($commonMask));
            if (
                gmp_cmp($secondAddr, $newNetworkAddr) >= 0
                && gmp_cmp($secondAddr, $newBroadcastAddr) <= 0
            ) {
                break;
            }
        }

        return $commonMask;
    }

    /**
     * @return resource|\GMP
     */
    private function internalFirstAddr()
    {
        return gmp_and($this->addr, $this->mask);
    }

    /**
     * @return resource|\GMP
     */
    private function internalLastAddr()
    {
        return gmp_add(gmp_and($this->addr, $this->mask), $this->gmp_not($this->mask));
    }

    /**
     * @param resource|\GMP $value
     * @return resource|\GMP
     */
    private function gmp_not($value)
    {
        for (
            $i = 0, $notValue = gmp_init(0);
            $i < $this->maxPrefixLength;
            gmp_testbit($value, $i) ? gmp_clrbit($notValue, $i) : gmp_setbit($notValue, $i), $i++
        );

        return $notValue;
    }
}
