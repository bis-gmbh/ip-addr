# IP-Addr library manages IPv4 and IPv6 addresses and subnets

How to install:

```
composer require --dev bis-gmbh/ip-addr ^0.2
```

Usage example:

```php
use \IPAddr\Utils as IP;

$providerSubnet = IP::make('10/8');

$userSubnet = IP::make('10.100.0.2/30');

if ($providerSubnet->within($userSubnet)) {
    printf("User network: %s\n", $userSubnet->network()->addr());
    printf("User broadcast: %s\n", $userSubnet->broadcast()->addr());
    printf("User addrs:\n");
    foreach ($userSubnet as $addr) {
        printf("%s\n", $addr->addr());
    }
}
```

Will output:

```
User network: 10.100.0.0
User broadcast: 10.100.0.3
User addrs:
10.100.0.0
10.100.0.1
10.100.0.2
10.100.0.3
```
