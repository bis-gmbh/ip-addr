<?php
/**
 * IP-Addr library
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

use IPAddr\v4 as IPv4;

class IPv4Test extends PHPUnit_Framework_TestCase
{
    public $invalidStringAddresses = [];

    public function setup()
    {
        $this->invalidStringAddresses = require 'data/ipv4-invalid.php';
    }

    public function testAssign()
    {
        $ip = new IPv4;

        $ip->assign(0);
        $ip->assign(0xFFFFFFFF);
        $ip->assign('192.168.0.1', '255.255.255.0');
        $ip->assign('10/8');

        $this->assertNotEquals(
            IPv4::create('10')->hexadecimal(), // 0x0a000000
            IPv4::create(10)->hexadecimal() // 0x0000000a
        );

        $this->expectException('InvalidArgumentException');
        $ip->assign(-1);
        $ip->assign('192.168.0.1', 0xFFFFFF00);
        $ip->assign('240/4', '255');
    }

    public function testIsNumeric()
    {
        $this->assertFalse(IPv4::isNumeric(null));
        $this->assertFalse(IPv4::isNumeric(false));
        $this->assertFalse(IPv4::isNumeric(true));
        $this->assertFalse(IPv4::isNumeric('127.0.0.1'));
        $this->assertFalse(IPv4::isNumeric(-1));
        $this->assertFalse(IPv4::isNumeric(4294967296));
        $this->assertTrue(IPv4::isNumeric(0));
        $this->assertTrue(IPv4::isNumeric(2130706433));
    }

    public function testIsTextual()
    {
        foreach ($this->invalidStringAddresses as $invalidAddr) {
            $this->assertFalse(IPv4::isTextual($invalidAddr));
        }
        $this->assertTrue(IPv4::isTextual('0.0.0.0'));
        $this->assertTrue(IPv4::isTextual('127.0.0.1'));
        $this->assertTrue(IPv4::isTextual('10.0.100.1'));
        $this->assertTrue(IPv4::isTextual('224.1.0.0'));
        $this->assertTrue(IPv4::isTextual('255.255.255.255'));
        $this->assertTrue(IPv4::isTextual('169.255.255'));
        $this->assertTrue(IPv4::isTextual('169.255'));
        $this->assertTrue(IPv4::isTextual('169'));
    }

    public function testIsCIDR()
    {
        foreach ($this->invalidStringAddresses as $invalidAddr) {
            $this->assertFalse(IPv4::isCIDR($invalidAddr));
        }
        $this->assertTrue(IPv4::isCIDR('0.0.0.0/0'));
        $this->assertTrue(IPv4::isCIDR('192.168.100.2/30'));
        $this->assertTrue(IPv4::isCIDR('192.168.100/24'));
        $this->assertTrue(IPv4::isCIDR('192.168/16'));
        $this->assertTrue(IPv4::isCIDR('10/8'));
        $this->assertTrue(IPv4::isCIDR('255.255.255.255/32'));
    }

    public function testBinary()
    {
        $this->assertEquals(IPv4::create(0)->binary(), '0b00000000000000000000000000000000');
        $this->assertEquals(IPv4::create(3325256815)->binary(), '0b11000110001100110110010001101111');
        $this->assertNotEquals(IPv4::create(3325256815)->binary(), '0b01000110001100110110010001101111');
        $this->assertEquals(IPv4::create(4294967295)->binary(), '0b11111111111111111111111111111111');
        $this->assertNotEquals(IPv4::create(4294967294)->binary(), '0b11111111111111111111111111111111');

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPv4::create(-1)->binary(), '');
        $this->assertEquals(IPv4::create(4294967296)->binary(), '');
    }

    public function testDecimal()
    {
        $this->assertEquals(IPv4::create(0)->decimal(), 0);
        $this->assertEquals(IPv4::create(0xFFFFFFFF)->decimal(), 4294967295);
        $this->assertEquals(IPv4::create('0.0.0.0')->decimal(), 0);
        $this->assertEquals(IPv4::create('255.255.255.255')->decimal(), 4294967295);
        $this->assertEquals(IPv4::create('127.0.0.1')->decimal(), 2130706433);
    }

    public function testHexaecimal()
    {
        $this->assertEquals(IPv4::create(0)->hexadecimal(), '0x00000000');
        $this->assertEquals(IPv4::create(0xFFFFFFFF)->hexadecimal(), '0xffffffff');
        $this->assertEquals(IPv4::create('0.0.0.0')->hexadecimal(), '0x00000000');
        $this->assertEquals(IPv4::create('255.255.255.255')->hexadecimal(), '0xffffffff');
        $this->assertEquals(IPv4::create('127.0.0.1')->hexadecimal(), '0x7f000001');
    }

    public function testNetmask()
    {
        $this->assertEquals(IPv4::create(0)->netmask(), 0xFFFFFFFF);
        $this->assertEquals(IPv4::create('0.0.0.0', '255.255.255.255')->netmask(), 0xFFFFFFFF);
        $this->assertEquals(IPv4::create('0.0.0.0', '0.0.0.0')->netmask(), 0);
        $this->assertEquals(IPv4::create('0', '255.255.255.0')->netmask(), 0xFFFFFF00);
        $this->assertEquals(IPv4::create('0', '255.255.0.0')->netmask(), 0xFFFF0000);
        $this->assertEquals(IPv4::create('0', '255')->netmask(), 0xFF000000);
        $this->assertEquals(IPv4::create('0', '255.255.255.252')->netmask(), 0xFFFFFFFC);
        $this->assertEquals(IPv4::create('0.0.0.0/30')->netmask(), 0xFFFFFFFC);

        // arbitrary masks are allowed, but their text representations will be incorrect
        $this->assertEquals(IPv4::create('0.0.0.0', '255.0.13.187')->netmask(), 0xFF000DBB);

        $this->expectException('InvalidArgumentException');
        $this->assertEquals(IPv4::create('0', 0xFFFFFF00)->netmask(), 0xFFFFFF00);
    }

    public function testReverseMask()
    {
        $this->assertEquals(IPv4::create(0)->reverseMask(), '255.255.255.255');
        $this->assertEquals(IPv4::create('0.0.0.0', '255.255.255.255')->reverseMask(), '255.255.255.255');
        $this->assertEquals(IPv4::create('0.0.0.0', '0.0.0.0')->reverseMask(), '0.0.0.0');
        $this->assertEquals(IPv4::create('0', '255.255.255.0')->reverseMask(), '0.255.255.255');
        $this->assertEquals(IPv4::create('0', '255.255.0.0')->reverseMask(), '0.0.255.255');
        $this->assertEquals(IPv4::create('0', '255')->reverseMask(), '0.0.0.255');
        $this->assertEquals(IPv4::create('0', '255.255.255.252')->reverseMask(), '252.255.255.255');
        $this->assertEquals(IPv4::create('0.0.0.0/30')->reverseMask(), '252.255.255.255');

        $this->assertEquals(IPv4::create('0.0.0.0', '255.0.13.187')->reverseMask(), '187.13.0.255');
    }

    public function testPrefixLength()
    {
        $this->assertEquals(IPv4::create(0)->prefixLength(), 32);
        $this->assertEquals(IPv4::create('0.0.0.0', '255.255.255.255')->prefixLength(), 32);
        $this->assertEquals(IPv4::create('0.0.0.0', '0.0.0.0')->prefixLength(), 0);
        $this->assertEquals(IPv4::create('0', '255.255.255.0')->prefixLength(), 24);
        $this->assertEquals(IPv4::create('0', '255.255.0.0')->prefixLength(), 16);
        $this->assertEquals(IPv4::create('0', '255')->prefixLength(), 8);
        $this->assertEquals(IPv4::create('0', '255.255.255.252')->prefixLength(), 30);
        $this->assertEquals(IPv4::create('0.0.0.0/30')->prefixLength(), 30);

        $this->assertEquals(IPv4::create('0.0.0.0', '255.0.13.187')->prefixLength(), 17);
    }

    public function testFirst()
    {
        $this->assertEquals(IPv4::create('0/0')->first()->decimal(), 0);
        $this->assertEquals(IPv4::create('255.255.255.255/32')->first()->decimal(), 4294967295);
        $this->assertEquals(IPv4::create('192.168/16')->first()->decimal(), 3232235520); // 192.168.0.0
        $this->assertEquals(IPv4::create('192.168.100.15/30')->first()->decimal(), 3232261132); // 192.168.100.12
        $this->assertEquals(IPv4::create('192.168.100.15/4')->first()->decimal(), 3221225472); // 192.0.0.0
    }

    public function testLast()
    {
        $this->assertEquals(IPv4::create('0/0')->last()->decimal(), 4294967295);
        $this->assertEquals(IPv4::create('255.255.255.255/32')->last()->decimal(), 4294967295);
        $this->assertEquals(IPv4::create('192.168/16')->last()->decimal(), 3232301055); // 192.168.255.255
        $this->assertEquals(IPv4::create('192.168.100.15/30')->last()->decimal(), 3232261135); // 192.168.100.15
        $this->assertEquals(IPv4::create('192.168.100.15/4')->last()->decimal(), 3489660927); // 207.255.255.255
    }

    public function testNumAddrs()
    {
        $this->assertEquals(IPv4::create('0/0')->numAddrs(), 0xFFFFFFFF);
        $this->assertEquals(IPv4::create('255.255.255.255/32')->numAddrs(), 1);
        $this->assertEquals(IPv4::create('192.168/16')->numAddrs(), 0x00010000);
        $this->assertEquals(IPv4::create('192.168.100.15/30')->numAddrs(), 4);
        $this->assertEquals(IPv4::create('192.168.100.15/4')->numAddrs(), 0x10000000);
    }

    public function testNumHosts()
    {
        $this->assertEquals(IPv4::create('0/0')->numHosts(), 0xFFFFFFFD);
        $this->assertEquals(IPv4::create('255.255.255.255/32')->numHosts(), 1);
        $this->assertEquals(IPv4::create('192.168/16')->numHosts(), 0x0000FFFE);
        $this->assertEquals(IPv4::create('192.168.100.15/30')->numHosts(), 2);
        $this->assertEquals(IPv4::create('192.168.100.15/4')->numHosts(), 0x0FFFFFFE);
    }

    public function testHostBits()
    {
        $this->assertEquals(IPv4::create(0)->hostBits(), 0);
        $this->assertEquals(IPv4::create('255.255.255.255', '255.255.255.255')->hostBits(), 0);
        $this->assertEquals(IPv4::create('192.168/16')->hostBits(), 16);
        $this->assertEquals(IPv4::create('192.168.100.15/30')->hostBits(), 2);
        $this->assertEquals(IPv4::create('10/8')->hostBits(), 24);
    }

    public function testAddr()
    {
        $this->assertEquals(IPv4::create(0)->addr(), '0.0.0.0');
        $this->assertEquals(IPv4::create('255.255.255.255', '255.255.255.255')->addr(), '255.255.255.255');
        $this->assertEquals(IPv4::create('192.168/16')->addr(), '192.168.0.0');
        $this->assertEquals(IPv4::create('192.168.100.15', '255.255.255.252')->addr(), '192.168.100.15');
        $this->assertEquals(IPv4::create('10/8')->addr(), '10.0.0.0');
    }

    public function testMask()
    {
        $this->assertEquals(IPv4::create(0)->mask(), '255.255.255.255');
        $this->assertEquals(IPv4::create('255.255.255.255', '255.255.255.255')->mask(), '255.255.255.255');
        $this->assertEquals(IPv4::create('192.168/16')->mask(), '255.255.0.0');
        $this->assertEquals(IPv4::create('192.168.100.15', '255.255.255.252')->mask(), '255.255.255.252');
        $this->assertEquals(IPv4::create('10/8')->mask(), '255.0.0.0');
    }

    public function testCidr()
    {
        $this->assertEquals(IPv4::create(0)->cidr(), '0.0.0.0/32');
        $this->assertEquals(IPv4::create('255.255.255.255', '255.255.255.255')->cidr(), '255.255.255.255/32');
        $this->assertEquals(IPv4::create('192.168/16')->cidr(), '192.168.0.0/16');
        $this->assertEquals(IPv4::create('192.168.100.15', '255.255.255.252')->cidr(), '192.168.100.15/30');
        $this->assertEquals(IPv4::create('10/8')->cidr(), '10.0.0.0/8');
    }

    public function testReverse()
    {
        $this->assertEquals(IPv4::create(0)->reverse(), '0.0.0.0.in-addr.arpa.');
        $this->assertEquals(IPv4::create('255.255.255.255', '255.255.255.255')->reverse(), '255.255.255.255.in-addr.arpa.');
        $this->assertEquals(IPv4::create('192.168/16')->reverse(), '0.0.168.192.in-addr.arpa.');
        $this->assertEquals(IPv4::create('192.168.100.15', '255.255.255.252')->reverse(), '15.100.168.192.in-addr.arpa.');
        $this->assertEquals(IPv4::create('10/8')->reverse(), '0.0.0.10.in-addr.arpa.');
    }

    public function testNetType()
    {
        $this->assertEquals(IPv4::create()->netType(), '"This" Network');
        $this->assertEquals(IPv4::create('192.168')->netType(), 'Private-Use Networks');
        $this->assertEquals(IPv4::create('224.0.0.10')->netType(), 'Multicast');
        $this->assertEquals(IPv4::create('95.126.18.4')->netType(), 'Public');
    }

    public function testNetClass()
    {
        $this->assertEquals(IPv4::create()->netClass(), 'C');
        $this->assertEquals(IPv4::create('192.168/16')->netClass(), 'E');
        $this->assertEquals(IPv4::create('192.169/16')->netClass(), 'B');
        $this->assertEquals(IPv4::create('127/8')->netClass(), 'A');
        $this->assertEquals(IPv4::create('224/4')->netClass(), 'D');
        $this->assertEquals(IPv4::create('95.126.18.4/24')->netClass(), 'C');
        $this->assertEquals(IPv4::create('1.2.3.4/4')->netClass(), '-');
    }

    public function testLtEq()
    {
        $this->assertTrue(IPv4::create()->ltEq(IPv4::create()));
        $this->assertTrue(IPv4::create('255.255.255.255')->ltEq(IPv4::create(0xFFFFFFFF)));
        $this->assertFalse(IPv4::create('192.168.100.100')->ltEq(IPv4::create('192.168/16')));
        $this->assertTrue(IPv4::create('192.167.100.100')->ltEq(IPv4::create('192.168/16')));
    }

    public function testGtEq()
    {
        $this->assertTrue(IPv4::create()->gtEq(IPv4::create()));
        $this->assertTrue(IPv4::create('255.255.255.255')->gtEq(IPv4::create(0xFFFFFFFF)));
        $this->assertTrue(IPv4::create('192.168.100.100')->gtEq(IPv4::create('192.168/16')));
        $this->assertFalse(IPv4::create('192.167.100.100')->gtEq(IPv4::create('192.168/16')));
    }

    public function testContains()
    {
        $this->assertTrue(IPv4::create()->contains(IPv4::create()));
        $this->assertTrue(IPv4::create('255.255.255.255')->contains(IPv4::create(0xFFFFFFFF)));
        $this->assertTrue(IPv4::create('192.168.100.100')->contains(IPv4::create('192.168/16')));
        $this->assertFalse(IPv4::create('192.167.100.100')->contains(IPv4::create('192.168/16')));
    }

    public function testWithin()
    {
        $this->assertTrue(IPv4::create()->within(IPv4::create()));
        $this->assertTrue(IPv4::create(0xFFFFFFFF)->within(IPv4::create('255.255.255.255')));
        $this->assertTrue(IPv4::create('192.168/16')->within(IPv4::create('192.168.100.100')));
        $this->assertFalse(IPv4::create('192.168/16')->within(IPv4::create('192.167.100.100')));
    }

    public function testToString()
    {
        $ip = new IPv4('10.10.10.3', '255.255.255.252');

        $this->assertEquals(sprintf("%s", $ip), '10.10.10.3/30');

        $ip->assign('192.168', '255.255.255');

        $this->assertEquals(sprintf("%s", $ip), '192.168.0.0/24');
    }
}
