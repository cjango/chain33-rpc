<?php

namespace Jason\Chain33\Balance;

use Exception;
use Jason\Chain33\Kernel\BaseClient;
use Jason\Chain33\Kernel\Exceptions\ChainException;

/**
 * Class Client.
 */
class Client extends BaseClient
{
    /**
     * Notes: 查询地址余额.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 22:48
     *
     * @param  string|array  $address  要查询的地址，或地址组
     * @return array
     *
     * @throws ChainException
     */
    public function coin($address): array
    {
        $flat = is_string($address);

        $addresses = $flat ? [$address] : $address;

        $result = $this->request->GetBalance([
            'addresses' => $addresses,
        ]);

        return $flat ? $result[0] : $result;
    }

    /**
     * Notes: 查询地址token余额.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 22:50
     *
     * @param  string|array  $address  要查询的地址，或地址组
     * @param  string  $symbol  token符号名称
     * @return array
     *
     * @throws ChainException
     */
    public function token(string $address, string $symbol): array
    {
        $flat = is_string($address);

        $addresses = $flat ? [$address] : $address;

        $result = $this->request->GetTokenBalance([
            'execer'      => $this->parseExecer('token'),
            'tokenSymbol' => strtoupper($symbol),
            'addresses'   => $addresses,
        ], 'token');

        return $flat ? $result[0] : $result;
    }

    /**
     * Notes   : 查询地址所有合约地址余额.
     *
     * @Date   : 2021/8/12 8:52 上午
     * @Author : <Jason.C>
     *
     * @param  string  $address  要查询的地址
     * @return mixed
     * @throws ChainException
     */
    public function all(string $address)
    {
        return $this->request->GetAllExecBalance([
            'addr' => $address,
        ])['execAccount'];
    }

    /**
     * Notes: 查询地址所有TOKEN余额.
     *
     * @Author : <C.Jason>
     * @Date   : 2020/4/30 22:53
     *
     * @param  string  $address  要查询的地址
     * @return array
     */
    public function assets(string $address): ?array
    {
        try {
            return $this->request->Query([
                'execer'   => 'token',
                'funcName' => 'GetAccountTokenAssets',
                'payload'  => [
                    'address' => $address,
                    'execer'  => 'token',
                ],
            ])['tokenAssets'];
        } catch (Exception $exception) {
            return [];
        }
    }

    /**
     * Notes   :  查询合约地址余额.
     *
     * @Date   : 2021/8/11 10:39 上午
     * @Author : <Jason.c>
     *
     * @param  string  $execer  合约名称
     * @param  string  $address  账户地址
     * @param  string  $symbol  代币符号
     * @return array
     *
     * @throws ChainException
     */
    public function exec(string $address, string $execer, string $symbol): ?array
    {
        if (strtoupper($symbol) === $this->app->system->coin()) {
            $assetExec = 'coins';
        } else {
            $assetExec = 'token';
        }

        return $this->request->GetAllExecBalance([
            'addr'         => $address,
            'execer'       => $this->parseExecer($execer),
            'asset_exec'   => $assetExec,
            'asset_symbol' => strtoupper($symbol),
        ])['execAccount'];
    }
}
