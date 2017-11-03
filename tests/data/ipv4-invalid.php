<?php

// Original from https://github.com/beaugunderson/ip-address/blob/master/test/data/invalid-ipv4-addresses.json
return [
    null,
    ' 127.0.0.1',
    '127.0.0.1 ',
    '127.0.0.1 127.0.0.1',
    '127.0.0.256',
    '127.0.0.1//1',
    '127.0.0.1/0x1',
    '127.0.0.1/-1',
    '127.0.0.1/ab',
    '127.0.0.1/',
    '127.0.0.256/32',
    '127.0.0.1/33',
    '/16',
];
