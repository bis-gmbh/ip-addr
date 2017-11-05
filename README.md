# IP-Addr library manages IPv4 and IPv6 addresses and subnets

How to install:

```
composer require --dev bis-gmbh/ip-addr ^0.3
```

Usage example:

```php
use \BIS\IPAddr\Utils as IP;
use \BIS\IPAddr\HostIterator;

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
```

Will output:

```
User network: 10.100.0.0
User broadcast: 10.100.0.3
User addrs:
0: 10.100.0.0
1: 10.100.0.1
2: 10.100.0.2
3: 10.100.0.3
User hosts:
0: 10.100.0.1
1: 10.100.0.2
```
