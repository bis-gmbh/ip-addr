<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

use \BIS\IPAddr\v6 as IPv6;
use \BIS\IPAddr\HostIterator;

class IPv6Test extends PHPUnit_Framework_TestCase
{
    public $invalidTextualAddresses = [];

    public function setup()
    {
        $this->invalidTextualAddresses = require 'data/ipv6-invalid.php';
    }

    public function testIsNumeric()
    {
        $this->assertFalse(IPv6::isNumeric(null));
        $this->assertFalse(IPv6::isNumeric(false));
        $this->assertFalse(IPv6::isNumeric(true));
        $this->assertFalse(IPv6::isNumeric('127.0.0.1'));
        $this->assertFalse(IPv6::isNumeric(-1));
        $this->assertTrue(IPv6::isNumeric(0));
        $this->assertTrue(IPv6::isNumeric(4294967296));
    }

    public function testIsTextual()
    {
        foreach ($this->invalidTextualAddresses as $invalidAddr) {
            $this->assertFalse(IPv6::isTextual($invalidAddr));
        }
        $this->assertTrue(IPv6::isTextual('1111:2222::5555:6666:7777:8888'));
        $this->assertTrue(IPv6::isTextual('0:0:0:0:0:FFFF:129.144.52.38'));
        $this->assertTrue(IPv6::isTextual('0:1:2:3:4:5:6:7'));
        $this->assertTrue(IPv6::isTextual('1111:2222::'));
        $this->assertTrue(IPv6::isTextual('::ffff:2.3.4.0'));
        $this->assertTrue(IPv6::isTextual('a:aaaa::'));
        $this->assertTrue(IPv6::isTextual('a::f'));
    }

    public function testIsCIDR()
    {
        foreach ($this->invalidTextualAddresses as $invalidAddr) {
            $this->assertFalse(IPv6::isCIDR($invalidAddr));
        }
        $this->assertTrue(IPv6::isCIDR('1111:2222::5555:6666:7777:8888/0'));
        $this->assertTrue(IPv6::isCIDR('0:0:0:0:0:FFFF:129.144.52.38/64'));
        $this->assertTrue(IPv6::isCIDR('0:1:2:3:4:5:6:7/128'));
        $this->assertTrue(IPv6::isCIDR('1111:2222::/74'));
        $this->assertTrue(IPv6::isCIDR('::ffff:2.3.4.0/109'));
        $this->assertTrue(IPv6::isCIDR('a:aaaa::/4'));
        $this->assertTrue(IPv6::isCIDR('a::f/117'));
    }

    public function testIsRange()
    {
        $this->assertTrue(IPv6::isRange(':: - ::'));
        $this->assertTrue(IPv6::isRange('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff-  ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'));
        $this->assertTrue(IPv6::isRange('ffff:ffff:ffff:ffff::    - ffff:ffff:ffff:ffff:eeee:dddd:cccc:bbbb'));
        $this->assertTrue(IPv6::isRange('2a02:6b8::2:242 - 2a02:6b8::2:247'));
        $this->assertTrue(IPv6::isRange('::4.3.2.1-::1.2.3.4'));
        $this->assertTrue(IPv6::isRange('4a00::64 - 2a00:1450:4010:c0f::64'));
        $this->assertFalse(IPv6::isRange(null));
        $this->assertFalse(IPv6::isRange('10:10::/10'));
        $this->assertFalse(IPv6::isRange('0'));
        $this->assertFalse(IPv6::isRange('anything-anything else'));
        $this->assertFalse(IPv6::isRange('-::ffff:2.3.4.0'));
        $this->assertFalse(IPv6::isRange('a:aaaa::   -  '));
        $this->assertFalse(IPv6::isRange('   -  '));
        $this->assertFalse(IPv6::isRange('b::b - 127.0.0.1'));
    }

    public function testAssign()
    {
        $ip = new IPv6;

        $ip->assign(0);
        $ip->assign(0xFFFFFFFF);
        $ip->assign('::192.168.0.1', '::255.255.255.0');
        $ip->assign('::10/8');

        $this->expectException('InvalidArgumentException');
        $ip->assign(-1);
        $ip->assign('192.168.0.1', 0xFFFFFF00);
        $ip->assign('240/4', '255');
    }

    public function testBinary()
    {
        $this->assertEquals(IPv6::create(0)->binary(), "0b00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000");
        $this->assertEquals(IPv6::create(3325256815)->binary(), '0b00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000011000110001100110110010001101111');
        $this->assertEquals(IPv6::create('2a02:6b8:f::/96')->binary(), '0b00101010000000100000011010111000000000000000111100000000000000000000000000000000000000000000000000000000000000000000000000000000');
        $this->assertNotEquals(IPv6::create(3325256815)->binary(), '0b00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000001000110001100110110010001101111');
        $this->assertEquals(IPv6::create('4294967295')->binary(), '0b00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000011111111111111111111111111111111');
        $this->assertNotEquals(IPv6::create(4294967294)->binary(), '0b00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000011111111111111111111111111111111');
    }

    public function testDecimal()
    {
        $this->assertEquals(IPv6::create(0)->decimal(), '0');
        $this->assertEquals(IPv6::create(0xFFFFFFFF)->decimal(), '4294967295');
        $this->assertEquals(IPv6::create('::0.0.0.0')->decimal(), '0');
        $this->assertEquals(IPv6::create('::255.255.255.255')->decimal(), '4294967295');
        $this->assertEquals(IPv6::create('::127.0.0.1')->decimal(), '2130706433');
        $this->assertEquals(IPv6::create('0:ffff::')->decimal(), '5192217630372313364192902785269760');
        $this->assertEquals(IPv6::create('ffff::')->decimal(), '340277174624079928635746076935438991360');
    }

    public function testHexadecimal()
    {
        $this->assertEquals(IPv6::create(0)->hexadecimal(), '0x00000000000000000000000000000000');
        $this->assertEquals(IPv6::create(0xFFFFFFFF)->hexadecimal(), '0x000000000000000000000000ffffffff');
        $this->assertEquals(IPv6::create('::0.0.0.0')->hexadecimal(), '0x00000000000000000000000000000000');
        $this->assertEquals(IPv6::create('::255.255.255.255')->hexadecimal(), '0x000000000000000000000000ffffffff');
        $this->assertEquals(IPv6::create('::127.0.0.1')->hexadecimal(), '0x0000000000000000000000007f000001');
        $this->assertEquals(IPv6::create('0:ffff::')->hexadecimal(), '0x0000ffff000000000000000000000000');
        $this->assertEquals(IPv6::create('ffff::')->hexadecimal(), '0xffff0000000000000000000000000000');
    }

    public function testNetmask()
    {
        $this->assertEquals(IPv6::create(0)->netmask(), '0xffffffffffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('::0.0.0.0', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff')->netmask(), '0xffffffffffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff:ffff:ffff::')->netmask(), '0xffffffffffffffffffffffff00000000');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff::')->netmask(), '0xffffffffffffffff0000000000000000');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff::')->netmask(), '0xffffffff000000000000000000000000');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff::')->netmask(), '0xffffffffffffffffffffffffffff0000');
        $this->assertEquals(IPv6::create('::0.0.0.0/126')->netmask(), '0xfffffffffffffffffffffffffffffffc');

        // arbitrary masks are allowed, but their text representations will be incorrect
        $this->assertEquals(IPv6::create('::0.0.0.0', '0:ffff:0:0:f::')->netmask(), '0x0000ffff00000000000f000000000000');

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPv6::create('::0.0.0.0', 0)->netmask(), '0x00000000000000000000000000000000');
    }

    public function testReverseMask()
    {
        $this->assertEquals(IPv6::create(0)->reverseMask(), 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff');
        $this->assertEquals(IPv6::create('::0.0.0.0', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff')->reverseMask(), 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff:ffff:ffff::')->reverseMask(), '0000:0000:ffff:ffff:ffff:ffff:ffff:ffff');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff::')->reverseMask(), '0000:0000:0000:0000:ffff:ffff:ffff:ffff');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff::')->reverseMask(), '0000:0000:0000:0000:0000:0000:ffff:ffff');
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff::')->reverseMask(), '0000:ffff:ffff:ffff:ffff:ffff:ffff:ffff');
        $this->assertEquals(IPv6::create('::0.0.0.0/126')->reverseMask(), 'fffc:ffff:ffff:ffff:ffff:ffff:ffff:ffff');

        $this->assertEquals(IPv6::create('::0.0.0.0', '0:ffff:0:0:f::')->reverseMask(), '0000:0000:0000:000f:0000:0000:ffff:0000');

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPv6::create('::0.0.0.0', 0)->reverseMask(), 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff');
    }

    public function testPrefixLength()
    {
        $this->assertEquals(IPv6::create(0)->prefixLength(), 128);
        $this->assertEquals(IPv6::create('::0.0.0.0', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff')->prefixLength(), 128);
        $this->assertEquals(IPv6::create('::0.0.0.0', '::')->prefixLength(), 0);
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff:ffff:ffff::')->prefixLength(), 96);
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff::')->prefixLength(), 64);
        $this->assertEquals(IPv6::create('::', 'ffff:ffff::')->prefixLength(), 32);
        $this->assertEquals(IPv6::create('::', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff::')->prefixLength(), 112);
        $this->assertEquals(IPv6::create('::0.0.0.0/126')->prefixLength(), 126);

        $this->assertEquals(IPv6::create('::0.0.0.0', '0:ffff:0:0:f::')->prefixLength(), 20);
    }

    public function testFirst()
    {
        $this->assertEquals(IPv6::create('::/0')->first()->hexadecimal(), '0x00000000000000000000000000000000');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->first()->hexadecimal(), '0xffffffffffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->first()->hexadecimal(), '0xffffffffffffffff0000000000000000');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->first()->hexadecimal(), '0x2a0206b8000000000000000000000000');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->first()->hexadecimal(), '0x20000000000000000000000000000000');
    }

    public function testLast()
    {
        $this->assertEquals(IPv6::create('::/0')->last()->hexadecimal(), '0xffffffffffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->last()->hexadecimal(), '0xffffffffffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->last()->hexadecimal(), '0xffffffffffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->last()->hexadecimal(), '0x2a0206bbffffffffffffffffffffffff');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->last()->hexadecimal(), '0x2fffffffffffffffffffffffffffffff');
    }

    public function testNumAddrs()
    {
        $this->assertEquals(IPv6::create('::/0')->numAddrs(), '340282366920938463463374607431768211455');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->numAddrs(), '1');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->numAddrs(), '18446744073709551616');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->numAddrs(), '316912650057057350374175801344');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->numAddrs(), '21267647932558653966460912964485513216');
    }

    public function testNumHosts()
    {
        $this->assertEquals(IPv6::create('::/0')->numHosts(), '340282366920938463463374607431768211453');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->numHosts(), '1');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->numHosts(), '18446744073709551614');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->numHosts(), '316912650057057350374175801342');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->numHosts(), '21267647932558653966460912964485513214');
    }

    public function testHostBits()
    {
        $this->assertEquals(IPv6::create('::/0')->hostBits(), 128);
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->hostBits(), 0);
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->hostBits(), 64);
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->hostBits(), 98);
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->hostBits(), 124);
    }

    public function testAddr()
    {
        $this->assertEquals(IPv6::create('::/0')->addr(), '::');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->addr(), 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->addr(), 'ffff:ffff:ffff:ffff::');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->addr(), '2a02:6b8::2:242');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->addr(), '2a00:1450:4010:c0f::64');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64:1.2.3.4/4')->addr(), '2a00:1450:4010:c0f::64:102:304');
    }

    public function testMask()
    {
        $this->assertEquals(IPv6::create('::/0')->mask(), '::');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->mask(), 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->mask(), 'ffff:ffff:ffff:ffff::');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->mask(), 'ffff:fffc::');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->mask(), 'f000::');
    }

    public function testCidr()
    {
        $this->assertEquals(IPv6::create('::/0')->cidr(), '::/0');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->cidr(), 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->cidr(), 'ffff:ffff:ffff:ffff::/64');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->cidr(), '2a02:6b8::2:242/30');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->cidr(), '2a00:1450:4010:c0f::64/4');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242 - 2a02:06bb:ffff:ffff:ffff:ffff:ffff:ffff')->cidr(), '2a02:6b8::2:242/30');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64 - 2fff::')->cidr(), '2a00:1450:4010:c0f::64/5');
    }

    public function testReverse()
    {
        $this->assertEquals(IPv6::create('::/0')->reverse(), '0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.ip6.arpa.');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->reverse(), 'f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.ip6.arpa.');
        $this->assertEquals(IPv6::create('ffff:ffff:ffff:ffff::/64')->reverse(), '0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.f.ip6.arpa.');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242/30')->reverse(), '2.4.2.0.2.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.0.8.b.6.0.2.0.a.2.ip6.arpa.');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64/4')->reverse(), '4.6.0.0.0.0.0.0.0.0.0.0.0.0.0.0.f.0.c.0.0.1.0.4.0.5.4.1.0.0.a.2.ip6.arpa.');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64:1.2.3.4/4')->reverse(), '4.0.3.0.2.0.1.0.4.6.0.0.0.0.0.0.f.0.c.0.0.1.0.4.0.5.4.1.0.0.a.2.ip6.arpa.');
    }

    public function testNetType()
    {
        $this->assertEquals(IPv6::create()->netType(), 'Unspecified');
        $this->assertEquals(IPv6::create('::1')->netType(), 'Loopback');
        $this->assertEquals(IPv6::create('::192.17.56.8')->netType(), 'IPv4-Mapped IPv6 Address');
        $this->assertEquals(IPv6::create('FC01::1')->netType(), 'Unique Site-Local');
        $this->assertEquals(IPv6::create('FF00::1')->netType(), 'Multicast');
        $this->assertEquals(IPv6::create('FE80::1')->netType(), 'Link-Local unicast');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242')->netType(), 'Global Unicast');
    }

    public function testFull()
    {
        $this->assertEquals(IPv6::create('::')->full(), '0000:0000:0000:0000:0000:0000:0000:0000');
        $this->assertEquals(IPv6::create('::1')->full(), '0000:0000:0000:0000:0000:0000:0000:0001');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242')->full(), '2a02:06b8:0000:0000:0000:0000:0002:0242');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64:1.2.3.4')->full(), '2a00:1450:4010:0c0f:0000:0064:0102:0304');
    }

    public function testFull4()
    {
        $this->assertEquals(IPv6::create('::')->full4(), '0000:0000:0000:0000:0000:0000:0.0.0.0');
        $this->assertEquals(IPv6::create('::1')->full4(), '0000:0000:0000:0000:0000:0000:0.0.0.1');
        $this->assertEquals(IPv6::create('2a02:6b8::2:242')->full4(), '2a02:06b8:0000:0000:0000:0000:0.2.2.66');
        $this->assertEquals(IPv6::create('2a00:1450:4010:c0f::64:1.2.3.4')->full4(), '2a00:1450:4010:0c0f:0000:0064:1.2.3.4');
    }

    public function testCompressed()
    {
        $this->assertEquals(IPv6::create('0000:0000:0000:0000:0000:0000:0000:0000')->compressed(), '::');
        $this->assertEquals(IPv6::create('0000:0000:0000:0000:0000:0000:0000:0001')->compressed(), '::1');
        $this->assertEquals(IPv6::create('0000:0000:000f:000f:0000:0000:000f:0001')->compressed(), '::f:f:0:0:f:1');
        $this->assertEquals(IPv6::create('000f:0000:000f:000f:0000:0000:0000:0000')->compressed(), 'f:0:f:f::');
        $this->assertEquals(IPv6::create('000f:0000:000f:000f:0000:0000:000f:0001')->compressed(), 'f:0:f:f::f:1');
        $this->assertEquals(IPv6::create('2a02:06b8:0000:0000:0000:0000:0002:0242')->compressed(), '2a02:6b8::2:242');
        $this->assertEquals(IPv6::create('2a00:1450:4010:0c0f:0000:0064:0102:0304')->compressed(), '2a00:1450:4010:c0f::64:102:304');
    }

    public function testCompressed4()
    {
        $this->assertEquals(IPv6::create('0000:0000:0000:0000:0000:0000:0000:0000')->compressed4(), '::0.0.0.0');
        $this->assertEquals(IPv6::create('0000:0000:0000:0000:0000:0000:0000:0001')->compressed4(), '::0.0.0.1');
        $this->assertEquals(IPv6::create('000f:0000:000f:000f:0000:0000:000f:0001')->compressed4(), 'f:0:f:f::0.15.0.1');
        $this->assertEquals(IPv6::create('2a02:06b8:0000:0000:0000:0000:0002:0242')->compressed4(), '2a02:6b8::0.2.2.66');
        $this->assertEquals(IPv6::create('2a00:1450:4010:0c0f:0000:0064:0102:0304')->compressed4(), '2a00:1450:4010:c0f::64:1.2.3.4');
    }

    public function testWithin()
    {
        $this->assertTrue(IPv6::create()->within(IPv6::create()));
        $this->assertTrue(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff')->within(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')));
        $this->assertTrue(IPv6::create('2a02:6b8:f::')->within(IPv6::create('2a02:6b8::2:242/4')));
        $this->assertFalse(IPv6::create('2a02:6b7::')->within(IPv6::create('2a02:6b8::2:242/32')));
    }

    public function testContains()
    {
        $this->assertTrue(IPv6::create()->contains(IPv6::create()));
        $this->assertTrue(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff/128')->contains(IPv6::create('ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff')));
        $this->assertTrue(IPv6::create('2a02:6b8::2:242/4')->contains(IPv6::create('2a02:6b8:f::')));
        $this->assertFalse(IPv6::create('2a02:6b8::2:242/32')->contains(IPv6::create('2a02:6b7::')));
    }

    public function testToString()
    {
        $ip = new IPv6('::/0');

        $this->assertEquals(sprintf("%s", $ip), '::/0');

        $ip->assign('2a02:06b8:0000:0000:0000:0000:0002:0242/4');

        $this->assertEquals(sprintf("%s", $ip), '2a02:6b8::2:242/4');
    }

    public function testIteration()
    {
        $expectedData = [
            '0: a:b:c:d:e:f::/128',
            '1: a:b:c:d:e:f::1/128',
            '2: a:b:c:d:e:f::2/128',
            '3: a:b:c:d:e:f::3/128',
        ];
        $actualData = [];

        $subnet = IPv6::create('a:b:c:d:e:f::1 - a:b:c:d:e:f::2'); // a:b:c:d:e:f::/126

        foreach ($subnet as $index => $address) {
            $actualData[] = sprintf('%d: %s', $index, $address);
        }

        $this->assertArraySubset($expectedData, $actualData);
    }

    public function testHostIteration()
    {
        $expectedData = [
            'a:b:c:d:e:f::/128' => [
                '0: a:b:c:d:e:f::/128',
            ],
            'a:b:c:d:e:f::/127' => [
                '0: a:b:c:d:e:f::/128',
                '1: a:b:c:d:e:f::1/128',
            ],
            'a:b:c:d:e:f::/126' => [
                '0: a:b:c:d:e:f::/128',
                '1: a:b:c:d:e:f::1/128',
                '2: a:b:c:d:e:f::2/128',
                '3: a:b:c:d:e:f::3/128',
            ],
        ];

        foreach ($expectedData as $ip => $data) {
            $actualData = [];
            $subnet = IPv6::create($ip);

            foreach (new HostIterator($subnet) as $index => $address) {
                $actualData[] = sprintf('%d: %s', $index, $address);
            }

            $this->assertArraySubset($data, $actualData);
        }
    }
}
