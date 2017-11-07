<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

use \BIS\IPAddr\Utils as IP;

class IPUtilsTest extends PHPUnit_Framework_TestCase
{
    public $v4data = [ // 127.0.0.1/8
        'ver' => 4,
        'host' => [
            'addr' => '127.0.0.1',
            'bin' => '0b01111111000000000000000000000001',
            'dec' => 2130706433,
            'hex' => '0x7f000001',
            'raddr' => '1.0.0.127.in-addr.arpa.',
            'type' => 'Loopback',
        ],
        'net' => [
            'cidr' => '127.0.0.1/8',
            'range' => '127.0.0.0 - 127.255.255.255',
            'masklen' => 8,
            'hostbits' => 24,
            'mask' => '255.0.0.0',
            'rmask' => '0.0.0.255',
            'addrs' => 16777216,
            'hosts' => 16777214,
            'network' => '127.0.0.0',
            'broadcast' => '127.255.255.255',
            'class' => 'A',
        ],
    ];

    public $v6data = [ // 2a02:6b8:f::/96
        'ver' => 6,
        'host' => [
            'addr' => '2a02:6b8:f::',
            'bin' => '0b00101010000000100000011010111000000000000000111100000000000000000000000000000000000000000000000000000000000000000000000000000000',
            'dec' => '55838096689141194739171837857648082944',
            'hex' => '0x2a0206b8000f00000000000000000000',
            'raddr' => '0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.f.0.0.0.8.b.6.0.2.0.a.2.ip6.arpa.',
            'type' => 'Global Unicast',
        ],
        'net' => [
            'cidr' => '2a02:6b8:f::/96',
            'range' => '2a02:6b8:f:: - 2a02:6b8:f::ffff:ffff',
            'masklen' => 96,
            'hostbits' => 32,
            'mask' => 'ffff:ffff:ffff:ffff:ffff:ffff:0000:0000',
            'rmask' => '0000:0000:ffff:ffff:ffff:ffff:ffff:ffff',
            'addrs' => '4294967296',
            'hosts' => '4294967294',
            'first' => '0x2a0206b8000f00000000000000000000',
            'last' => '0x2a0206b8000f000000000000ffffffff',
        ],
    ];

    public function testMake()
    {
        $this->assertEquals(IP::make('192.168.0.255 - 192.168.1.255'), '192.168.0.255/23');
        $this->assertEquals(IP::make('2a00:1450:4010:c0f::64 - 2fff::'), '2a00:1450:4010:c0f::64/5');

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IP::make(-1), '');
    }

    public function testInfo()
    {
        $this->assertArraySubset(IP::info(IP::make('127.0.0.1/8')), $this->v4data);
        $this->assertArraySubset(IP::info(IP::make('2a02:6b8:f::/96')), $this->v6data);
    }
}
