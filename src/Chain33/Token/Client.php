<?php

namespace Jason\Chain33\Token;

use Exception;
use Jason\Chain33\Kernel\BaseClient;
use Jason\Chain33\Kernel\Exceptions\ChainException;
use Jason\Chain33\Kernel\Exceptions\ConfigException;

/**
 * Class Client.
 */
class Client extends BaseClient
{
    /**
     * Notes: 预发行TOKEN.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/25 3:59 下午
     *
     * @param  string  $name  token 名称
     * @param  string  $symbol  token标记符，最大长度是16个字符，且必须为大写字符和数字
     * @param  string  $introduction  token 简介
     * @param  int  $total  发行总量
     * @param  string  $owner  token拥有者地址
     * @param  string  $privateKey  发行者私钥
     * @param  int  $category  token属性类别， 0 为普通token， 1 可增发和燃烧
     * @param  int  $price  发行该token愿意承担的费用
     * @return string
     *
     */
    public function preCreate(
        string $name,
        string $symbol,
        string $introduction,
        int $total,
        string $owner,
        string $privateKey,
        int $category = 0,
        int $price = 0
    ): string {
        $txHex = $this->request->CreateRawTokenPreCreateTx([
            'name'         => $name,
            'symbol'       => strtoupper($symbol),
            'introduction' => $introduction,
            'total'        => $total,
            'price'        => $price,
            'category'     => $category,
            'owner'        => $owner,
        ], 'token');

        return $this->app['transaction']->finalSend($txHex, $privateKey);
    }

    /**
     * Notes: 完成发行TOKEN.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/14 6:17 下午
     *
     * @param  string  $symbol  token标记符，最大长度是16个字符，且必须为大写字符和数字
     * @param  string  $owner  token拥有者地址
     * @param  string  $privateKey  管理员的私钥
     * @return string
     *
     */
    public function finish(string $symbol, string $owner, string $privateKey): string
    {
        $txHex = $this->request->CreateRawTokenFinishTx([
            'symbol' => strtoupper($symbol),
            'owner'  => $owner,
        ], 'token');

        return $this->app['transaction']->finalSend($txHex, $privateKey);
    }

    /**
     * Notes   :  查询所有预创建的token | 查询所有创建成功的token.
     *
     * @Date   : 2021/3/31 12:03 下午
     * @Author : <C.Jason>
     *
     * @param  int  $status
     * @param  bool  $symbolOnly
     * @return array
     */
    public function get(int $status = 0, bool $symbolOnly = false): array
    {
        try {
            return $this->request->Query([
                'execer'   => $this->parseExecer('token'),
                'funcName' => 'GetTokens',
                'payload'  => [
                    'status'     => $status,
                    'queryAll'   => true,
                    'symbolOnly' => $symbolOnly,
                ],
            ])['tokens'];
        } catch (Exception $exception) {
            return [];
        }
    }

    /**
     * Notes: 查询指定创建成功的token.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/14 6:19 下午
     *
     * @param  string  $symbol  token的Symbol
     * @return array
     * @throws ChainException
     */
    public function info(string $symbol): array
    {
        return $this->request->Query([
            'execer'   => $this->parseExecer('token'),
            'funcName' => 'GetTokenInfo',
            'payload'  => [
                'data' => strtoupper($symbol),
            ],
        ]);
    }

    /**
     * Notes: 生成撤销创建token的交易，只能撤销未完成的（un finish）.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/20 3:24 下午
     *
     * @param  string  $symbol  token的Symbol
     * @param  string  $owner  拥有者地址
     * @param  string  $privateKey  管理员私钥
     * @return string
     */
    public function revoke(string $symbol, string $owner, string $privateKey): string
    {
        $txHex = $this->request->CreateRawTokenRevokeTx([
            'symbol' => $symbol,
            'owner'  => $owner,
        ], 'token');

        return $this->app['transaction']->finalSend($txHex, $privateKey);
    }

    /**
     * Notes: 查询token相关的交易.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/25 11:07 上午
     *
     * @param  string  $symbol  token标记符
     * @param  string  $address  过滤地址
     * @param  int  $count  count: 交易的数量
     * @param  int  $flag  分页相关参数
     * @param  int  $direction  分页相关参数
     * @param  int  $height  分页相关参数
     * @param  int  $index  分页相关参数
     * @return array
     *
     * @throws ChainException
     */
    public function tx(
        string $symbol,
        string $address = '',
        int $count = 100,
        int $flag = 0,
        int $direction = 0,
        int $height = -1,
        int $index = 0
    ): array {
        return $this->request->Query([
            'execer'   => $this->parseExecer('token'),
            'funcName' => 'GetTxByToken',
            'payload'  => [
                'symbol'    => strtoupper($symbol),
                'addr'      => $address,
                'count'     => $count,
                'flag'      => $flag,
                'height'    => $height,
                'index'     => $index,
                'direction' => $direction,
            ],
        ])['txInfos'];
    }

    /**
     * Notes: token的增发.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/20 3:44 下午
     *
     * @param  string  $symbol  token的标记符
     * @param  int  $amount  增发token的数量
     * @param  string  $privateKey  token 拥有者的私钥，只有拥有者才能操作增发燃烧
     * @return string
     */
    public function mint(string $symbol, int $amount, string $privateKey): string
    {
        $txHex = $this->request->CreateRawTokenMintTx([
            'symbol' => strtoupper($symbol),
            'amount' => $amount,
        ], 'token');

        return $this->app['transaction']->finalSend($txHex, $privateKey);
    }

    /**
     * Notes: token的燃烧.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/20 3:44 下午
     *
     * @param  string  $symbol  token的标记符
     * @param  int  $amount  燃烧token的数量
     * @param  string  $privateKey  token 拥有者的私钥
     * @return string
     */
    public function burn(string $symbol, int $amount, string $privateKey): string
    {
        $txHex = $this->request->CreateRawTokenBurnTx([
            'symbol' => strtoupper($symbol),
            'amount' => $amount,
        ], 'token');

        return $this->app['transaction']->finalSend($txHex, $privateKey);
    }

    /**
     * Notes: 查询token的变化记录.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/20 3:47 下午
     *
     * @param  string  $symbol  token标记符
     * @return array actionType: 8 是token创建， 12 是增发， 13 是燃烧
     *
     * @throws ChainException
     */
    public function history(string $symbol): array
    {
        return $this->request->Query([
            'execer'   => $this->parseExecer('token'),
            'funcName' => 'GetTokenHistory',
            'payload'  => [
                'data' => strtoupper($symbol),
            ],
        ])['logs'];
    }
}
