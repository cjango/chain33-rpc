<?php

return [
    /**
     * 服务器地址
     */
    'base_uri'             => env('BLOCK_CHAIN_URI', '127.0.0.1'),

    /**
     * 服务器端口.
     */
    'base_port'            => env('BLOCK_CHAIN_PORT', 8801),

    /**
     * 平行链名称 user.p.XXX.  主链置空.
     */
    'para_name'            => env('BLOCK_CHAIN_PARA_NAME', ''),

    /**
     * 平行链交易代扣私钥
     */
    'para_pay_private_key' => env('BLOCK_CHAIN_PAY_PRIVATE_KEY', ''),
];
