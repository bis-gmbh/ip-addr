# IP-Addr library manages IPv4 and IPv6 addresses and subnets

## Installation

Install with composer:

```
composer require bis-gmbh/ip-addr
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

- [2ip.io IP calculator](https://2ip.io/ip-calc/)

## API

### Constructor

#### :small_blue_diamond: v4::create | v6::create

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
  - range format `'<first_addr> - <second_addr>'`, where `<first_addr>` and `<second_addr>` - addresses in textual format with the same ip version
    - v4 `'10.0.0.0 - 10.0.0.255'`
    - v6 `'2000:b:c:d::5d - 2000:b:c:d::ff'`
- *maskString* network mask in textual format, allow if *anyFormat* parameter also in textual format
    - v4 `'255.255.255.0'`, `'255.0'`
    - v6 `'ffff:ffff:ffff:ffff::'`

###### Return values

Returns a `Address` object on success

###### Examples

```php
$v4instance = v4::create('127.0.0.1');
$v4subnet = v4::create('192.168.0.1', '255.255.255.0');
$v6instance = v6::create('::1');
```

#### :small_blue_diamond: new v4 | new v6

###### Description

Creates a `Address` instances by **new** operator with the same parameters as the method `create`.

###### Examples

```php
$v4instance = new v4('127.0.0.1');
$v6instance = new v6('::1');
```

### Class

#### Methods

##### :small_blue_diamond: v4::isNumeric | v6::isNumeric

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

##### :small_blue_diamond: v4::isTextual | v6::isTextual

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

##### :small_blue_diamond: v4::isCIDR | v6::isCIDR

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

##### :small_blue_diamond: v4::isRange | v6::isRange

###### Description

```
public static function isRange ( $value ) : bool;
```

Checks if the parameter *value* present in range format. Allows any order of addresses - direct or reverse, e.g. `'10.0.0.255 - 10.0.0.0'` is allowed range.

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

##### :small_orange_diamond: v4::$privateNetworks

###### Description

Array of private v4 networks as described in [rfc1918](https://tools.ietf.org/html/rfc1918#section-3).

##### :small_orange_diamond: v4::$multicastNetworks

###### Description

Array of multicast v4 networks as described in [rfc3171](https://tools.ietf.org/html/rfc3171).

##### :small_orange_diamond: v4::$reservedNetworks

###### Description

Array of reserved v4 networks as described in [RFC 1112, Section 4](https://tools.ietf.org/html/rfc1112#section-4).

##### :small_orange_diamond: v4::$networkTypes

###### Description

Array of associative arrays representing special-purpose v4 addresses with their descriptions, [rfc5735](https://tools.ietf.org/html/rfc5735#section-4).

##### :small_orange_diamond: v6::$addressTypes

###### Description

Array of associative arrays representing v6 address types with their descriptions, [rfc4291](https://tools.ietf.org/html/rfc4291).

#### Methods

##### :small_blue_diamond: v4::version | v6::version

###### Description

```
public function version ( void ) : int;
```

###### Return values

Returns the version number of the address of the current object.

###### Examples

```php
var_dump(v4::create('10.0.0.0')->version());
var_dump(v6::create('::1')->version());
```

```
int(4)
int(6)
```

##### :small_blue_diamond: v4::assign | v6::assign

###### Description

```
public static function assign ( $anyFormat [, string $maskString = null ] ) : Address;
```

Assigns new address and mask values for the current object.

###### Parameters

See [create](#small_blue_diamond-v4create--v6create) method.

###### Examples

```php
$ip = v4::create('127.0.0.1');
var_dump($ip->addr());
$ip->assign('192.168.0.1');
var_dump($ip->addr());
```

```
string(9) "127.0.0.1"
string(11) "192.168.0.1"
```

##### :small_blue_diamond: v4::binary | v6::binary

TODO

##### :small_blue_diamond: v4::decimal | v6::decimal

TODO

##### :small_blue_diamond: v4::hexadecimal | v6::hexadecimal

TODO

##### :small_blue_diamond: v4::netmask | v6::netmask

TODO

##### :small_blue_diamond: v4::prefixLength | v6::prefixLength

TODO

##### :small_blue_diamond: v4::first | v6::first

TODO

##### :small_blue_diamond: v4::last | v6::last

TODO

##### :small_blue_diamond: v4::numAddrs | v6::numAddrs

TODO

##### :small_blue_diamond: v4::numHosts | v6::numHosts

TODO

##### :small_blue_diamond: v4::hostBits | v6::hostBits

TODO

##### :small_blue_diamond: v4::within | v6::within

TODO

##### :small_blue_diamond: v4::contains | v6::contains

TODO

##### :small_blue_diamond: v4::addr | v6::addr

TODO

##### :small_blue_diamond: v4::mask | v6::mask

TODO

##### :small_blue_diamond: v4::cidr | v6::cidr

TODO

##### :small_blue_diamond: v4::range | v6::range

TODO

##### :small_blue_diamond: v4::reverse | v6::reverse

TODO

##### :small_blue_diamond: v4::reverseMask | v6::reverseMask

TODO

##### :small_blue_diamond: v4::netType | v6::netType

TODO

##### :small_blue_diamond: v4::network

TODO

##### :small_blue_diamond: v4::broadcast

TODO

##### :small_blue_diamond: v4::netClass

TODO

##### :small_blue_diamond: v6::full

TODO

##### :small_blue_diamond: v6::full4

TODO

##### :small_blue_diamond: v6::fullMask

TODO

##### :small_blue_diamond: v6::compressed

TODO

##### :small_blue_diamond: v6::compressed4

TODO

### Array access

TODO

### Iterators

#### Address iteration

TODO

#### Host iteration

TODO

#### Subnet iteration

TODO

### Exceptions

Static methods and methods of objects can throw exceptions of the following types:

- `\InvalidArgumentException` when calling a method with incorrect arguments;
- `\DomainException` when trying to use language constructs that do not apply to address objects, for example, overwriting an element when accessing an array by index key;
- `\RuntimeException` when the library classes can not work in the current environment, for example, PHP GMP extension not installed.

```php
try {
    $ip = v6::create('invalid addr');
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage() . PHP_EOL;
}

try {
    $ip = v6::create('2002::fdce/64');
    echo $ip[12]->addr() . PHP_EOL;
    $ip[12] = 0;
} catch (\DomainException $e) {
    echo $e->getMessage() . PHP_EOL;
}
```

```
Wrong arguments
2002::c
Read-only access
```

### Utils Class

#### Methods

##### :small_blue_diamond: make

###### Description

```
public static function make ( $anyFormat [, string $maskString = null ] ) : Address
```

Trying to create an object of any of the versions based on the provided arguments

###### Parameters

See [create](#small_blue_diamond-v4create--v6create) method.

###### Return values

Returns a `Address` object on success

###### Examples

```php
use \BIS\IPAddr\Utils as IP;

var_dump(IP::make('127.0.0.1')->version());
var_dump(IP::make('::1')->version());
```

```
int(4)
int(6)
```

##### :small_blue_diamond: info

###### Description

```
public static function info ( Address $addr ) : array
```

###### Parameters

*addr* - `v4` or `v6` object

###### Return values

Returns the array with summary information about given address.

###### Examples

```php
use \BIS\IPAddr\Utils as IP;

$ip = IP::make('127.0.0.1/8');
echo json_encode(IP::info($ip), JSON_PRETTY_PRINT) . PHP_EOL;
```

```
{
    "ver": 4,
    "host": {
        "addr": "127.0.0.1",
        "bin": "0b01111111000000000000000000000001",
        "dec": 2130706433,
        "hex": "0x7f000001",
        "raddr": "1.0.0.127.in-addr.arpa.",
        "type": "Loopback"
    },
    "net": {
        "cidr": "127.0.0.1\/8",
        "range": "127.0.0.0 - 127.255.255.255",
        "masklen": 8,
        "hostbits": 24,
        "mask": "255.0.0.0",
        "rmask": "0.0.0.255",
        "addrs": 16777216,
        "hosts": 16777214,
        "network": "127.0.0.0",
        "broadcast": "127.255.255.255",
        "class": "A"
    }
}
```
