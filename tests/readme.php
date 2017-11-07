<?php

// cmd script for testing code from readme examples
// run command> php tests/readme.php

require 'vendor/autoload.php';

use \BIS\IPAddr\Utils as IP;
use \BIS\IPAddr\HostIterator;
use \BIS\IPAddr\SubnetIterator;

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
