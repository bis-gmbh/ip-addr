# IP-Addr library manages IPv4 and IPv6 addresses and subnets

## Installation

Install with composer:

```
composer require --dev bis-gmbh/ip-addr ^0.6
```

### Installation requirements

- PHP version 5.4 or above
- PHP GMP extension for IPv6 arbitrary arithmetic

## Abstract usage example

```php
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
Provider subnets:
0: 10.0.0.0/10
1: 10.64.0.0/10
2: 10.128.0.0/10
3: 10.192.0.0/10
```

## Used by

TODO

## API

### Constructor

#### v4::create | v6::create

###### Description

```
public static function create ( $anyFormat [, string $maskString = null ] ) : Address;
```

###### Parameters

- *anyFormat*
  - integer, e.g. `123`, `0xABCDEF00`, `075227`, `0b0101010`
  - numeric string, e.g. `'123'`, `'0xABCDEF00'`, `'075227'`, `'0b0101010'`
  - textual format:
    - v4, `'192.168.10.1'`, `'172.16.3'`, `'10.0'`
    - v6, `'a:b:c:d::'`, `'::'`, `'::345d:10.40.60.1'`
  - CIDR format:
    - v4, `'10.0.0.0/8'`, `'192.168/16'`
    - v6, `'2000:b:c:d::/64'`, `'::1/128'`
  - range format `<first_addr> - <second_addr>`, where `<first_addr>` and `<second_addr>` - addresses in textual format with the same ip version
    - v4 `'10.0.0.0 - 10.0.0.255'`
    - v6 `'2000:b:c:d::5d - 2000:b:c:d::ff'`

###### Return values

Returns a `Address` object on success

###### Examples

```php
$v4instance = v4::create('127.0.0.1');
$v6instance = v6::create('::1');
```

#### new v4 | new v6

###### Description

Creates a `Address` instances by **new** operator with the same parameters as the method `create`.

###### Examples

```php
$v4instance = new v4('127.0.0.1');
$v6instance = new v6('::1');
```

### Class

#### Methods

##### v4::isNumeric | v6::isNumeric

###### Description

```
public static function isNumeric ( $value ) : bool;
```

Checks if the parameter *value* present in numeric format.

###### Parameters

- *value* - verified value

###### Return values

Returns **TRUE** if the *value* in numeric format, and **FALSE** if not.

###### Examples

```php
var_dump(v4::isNumeric(0xFF000000));
var_dump(v4::isNumeric('0xFF000000'));
var_dump(v4::isNumeric('abcdef'));
var_dump(v4::isNumeric(true));
```

```
bool(true)
bool(true)
bool(false)
bool(false)
```

##### v4::isTextual | v6::isTextual

###### Description

```
public static function isTextual ( $value ) : bool;
```

Checks if the parameter *value* present in textual format.

###### Parameters

- *value* - verified value

###### Return values

Returns **TRUE** if the *value* in textual format, and **FALSE** if not.

```php
var_dump(v6::isTextual('1111:2222::5555:6666:7777:8888'));
var_dump(v6::isTextual('::ffff:2.3.4.0'));
var_dump(v6::isTextual('::/'));
var_dump(v6::isTextual('::ffff:2.3.4'));
```

```
bool(true)
bool(true)
bool(false)
bool(false)
```

##### v4::isCIDR | v6::isCIDR

###### Description

```
public static function isCIDR ( $value ) : bool;
```

Checks if the parameter *value* present in CIDR format.

###### Parameters

- *value* - verified value

###### Return values

Returns **TRUE** if the *value* in CIDR format, and **FALSE** if not.

```php
var_dump(v6::isCIDR('2000:2222::5555:6666:7777:8888/64'));
var_dump(v6::isCIDR('::/128'));
var_dump(v6::isCIDR('::'));
var_dump(v6::isCIDR('::/129'));
```

```
bool(true)
bool(true)
bool(false)
bool(false)
```

##### v4::isRange | v6::isRange

###### Description

```
public static function isRange ( $value ) : bool;
```

Checks if the parameter *value* present in range format. Allows any order of addresses - direct or reverse, e.g. '10.0.0.255 - 10.0.0.0' is allowed range.

###### Parameters

- *value* - verified value

###### Return values

Returns **TRUE** if the *value* in range format, and **FALSE** if not.

###### Examples

```php
var_dump(v4::isRange('10.0 - 10.10'));
var_dump(v4::isRange('192.168.0.1-   192.168.255.255'));
var_dump(v4::isRange('127.0.0.0-'));
var_dump(v4::isRange('127.0.0.0-::1'));
```

```
bool(true)
bool(true)
bool(false)
bool(false)
```

### Instance

#### Properties

##### v4::$privateNetworks

TODO

##### v4::$multicastNetworks

TODO

##### v4::$reservedNetworks

TODO

##### v4::$networkTypes

TODO

##### v6::$addressTypes

TODO

#### Methods

##### v4::version | v6::version

TODO

##### v4::assign | v6::assign

TODO

##### v4::binary | v6::binary

TODO

##### v4::decimal | v6::decimal

TODO

##### v4::hexadecimal | v6::hexadecimal

TODO

##### v4::netmask | v6::netmask

TODO

##### v4::prefixLength | v6::prefixLength

TODO

##### v4::first | v6::first

TODO

##### v4::last | v6::last

TODO

##### v4::numAddrs | v6::numAddrs

TODO

##### v4::numHosts | v6::numHosts

TODO

##### v4::hostBits | v6::hostBits

TODO

##### v4::within | v6::within

TODO

##### v4::contains | v6::contains

TODO

##### v4::addr | v6::addr

TODO

##### v4::mask | v6::mask

TODO

##### v4::cidr | v6::cidr

TODO

##### v4::range | v6::range

TODO

##### v4::reverse | v6::reverse

TODO

##### v4::reverseMask | v6::reverseMask

TODO

##### v4::netType | v6::netType

TODO

##### v4::network

TODO

##### v4::broadcast

TODO

##### v4::netClass

TODO

##### v6::full

TODO

##### v6::full4

TODO

##### v6::fullMask

TODO

##### v6::compressed

TODO

##### v6::compressed4

TODO

### Exceptions

TODO

- `\InvalidArgumentException`
- `\BadMethodCallException`
- `\DomainException`
- `\RuntimeException`

### Utils Class

#### Methods

##### make

TODO

##### info

TODO
