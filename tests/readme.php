<?php

// cmd script for testing code from readme examples
// run command> php tests/readme.php

require 'vendor/autoload.php';

use \IPAddr\Utils as IP;
use \IPAddr\HostIterator;

$providerSubnet = IP::make('10/8');

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
