<?php

// cmd script for testing code from readme examples
// run command> php tests/readme.php

require 'vendor/autoload.php';

use \BIS\IPAddr\Utils as IP;
use \BIS\IPAddr\HostIterator;
use \BIS\IPAddr\SubnetIterator;
use \BIS\IPAddr\v4;
use \BIS\IPAddr\v6;

$providerSubnet = IP::make('10.0/8');

$userSubnet = IP::make('10.100.0.2/30');

if ($providerSubnet->contains($userSubnet)) {
    printf("User network: %s\n", $userSubnet->network()->addr());
    printf("User broadcast: %s\n", $userSubnet->broadcast()->addr());
    printf("User addrs:\n");
    foreach ($userSubnet as $index => $ip) {
        printf("%d: %s\n", $index, $ip->addr());
    }
    $userHosts = new HostIterator($userSubnet);
    printf("User hosts:\n");
    foreach ($userHosts as $index => $ip) {
        printf("%d: %s\n", $index, $ip->addr());
    }
}

printf("Provider subnets:\n");
$providerSubnets = new SubnetIterator($providerSubnet, 10);
foreach ($providerSubnets as $index => $subnet) {
    printf("%d: %s\n", $index, $subnet->cidr());
}

var_dump(v4::isNumeric(0xFF000000));
var_dump(v4::isNumeric('0xFF000000'));
var_dump(v4::isNumeric('abcdef'));
var_dump(v4::isNumeric(true));

var_dump(v6::isTextual('1111:2222::5555:6666:7777:8888'));
var_dump(v6::isTextual('::ffff:2.3.4.0'));
var_dump(v6::isTextual('::/'));
var_dump(v6::isTextual('::ffff:2.3.4'));

var_dump(v6::isCIDR('2000:2222::5555:6666:7777:8888/64'));
var_dump(v6::isCIDR('::/128'));
var_dump(v6::isCIDR('::'));
var_dump(v6::isCIDR('::/129'));

var_dump(v4::isRange('10.0 - 10.10'));
var_dump(v4::isRange('192.168.0.1-   192.168.255.255'));
var_dump(v4::isRange('127.0.0.0-'));
var_dump(v4::isRange('127.0.0.0-::1'));

var_dump(v4::create('10.0.0.0')->version());
var_dump(v6::create('::1')->version());

$ip = v4::create('127.0.0.1');
var_dump($ip->addr());
$ip->assign('192.168.0.1');
var_dump($ip->addr());

try {
    $ip = v6::create('invalid addr');
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}

try {
    $ip = v6::create('2002::fdce/64');
    echo $ip[12]->addr() . PHP_EOL;
    $ip[12] = 0;
} catch (\DomainException $e) {
    echo $e->getMessage() . PHP_EOL;
}

var_dump(IP::make('127.0.0.1')->version());
var_dump(IP::make('::1')->version());

$ip = IP::make('127.0.0.1/8');
echo json_encode(IP::info($ip), JSON_PRETTY_PRINT) . PHP_EOL;
