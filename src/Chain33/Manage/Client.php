<?php

namespace Jason\Chain33\Manage;

use Jason\Chain33\Kernel\BaseClient;
use Jason\Chain33\Kernel\Exceptions\ConfigException;

/**
 * Class Client.
 */
class Client extends BaseClient
{
    const OP_ADD = 'add';
    const OP_DELETE = 'delete';

    /**
     * Notes   : 添加/删除一个token-finisher.
     *
     * @Date   : 2021/3/24 2:02 下午
     * @Author : <Jason.C>
     *
     * @param  string  $addr
     * @param  string  $op  add / delete
     * @return string
     *
     * @throws ConfigException
     */
    public function finisher(string $addr, string $op = self::OP_ADD): string
    {
        $txHex = $this->request->CreateTransaction([
            'execer'     => 'manage',
            'actionName' => 'Modify',
            'payload'    => [
                'key'   => 'token-finisher',
                'value' => $addr,
                'op'    => $op,
            ],
        ]);

        return $this->app['transaction']->finalSend($txHex, $this->config['superManager']['privateKey']);
    }

    /**
     * Notes   : TOKEN 黑名单管理.
     *
     * @Date   : 2021/3/24 2:02 下午
     * @Author : <Jason.C>
     *
     * @param  string  $symbol
     * @param  string  $op
     * @return string
     *
     * @throws ConfigException
     */
    public function blacklist(string $symbol, string $op = self::OP_ADD): string
    {
        $txHex = $this->request->CreateTransaction([
            'execer'     => 'manage',
            'actionName' => 'Modify',
            'payload'    => [
                'key'   => 'token-blacklist',
                'value' => $symbol,
                'op'    => $op,
            ],
        ]);

        return $this->app['transaction']->finalSend($txHex, $this->config['superManager']['privateKey']);
    }

    /**
     * Notes   : 共识节点的管理者.
     *
     * @Date   : 2021/4/2 11:01 上午
     * @Author : <Jason.C>
     *
     * @param  string  $addr
     * @param  string  $op
     * @return string
     *
     * @throws ConfigException
     */
    public function tendermint(string $addr, string $op = self::OP_ADD): string
    {
        $txHex = $this->request->CreateTransaction([
            'execer'     => 'manage',
            'actionName' => 'Modify',
            'payload'    => [
                'key'   => 'tendermint-manager',
                'value' => $addr,
                'op'    => $op,
            ],
        ]);

        return $this->app['transaction']->finalSend($txHex, $this->config['superManager']['privateKey']);
    }

    /**
     * Notes   : 通知全网，加入新的共识节点.
     *
     * @Date   : 2021/4/2 11:11 上午
     * @Author : <Jason.C>
     *
     * @param  string  $pubkey  新节点的公钥
     * @param  int  $power  投票权，范围从【1~~全网总power/3】，如果设置为 0 则代表剔除节点
     * @return string
     */
    public function addConsensusNode(string $pubkey, int $power = 10): string
    {
        $txHex = $this->request->CreateTransaction([
            'execer'     => 'valnode',
            'actionName' => 'NodeUpdate',
            'payload'    => [
                'pubKey' => $pubkey,
                'power'  => $power,
            ],
        ]);

        return $this->app['transaction']->finalSend($txHex, $this->config['superManager']['privateKey']);
    }

    /**
     * Notes: 查看finish apprv列表.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/2 21:43
     *
     * @param  string  $type  操作标识符
     * @return array
     */
    public function get(string $type = 'finisher'): array
    {
        $value = $this->request->Query([
            'execer'   => 'manage',
            'funcName' => 'GetConfigItem',
            'payload'  => [
                'data' => 'token-'.$type,
            ],
        ])['value'];

        $value = str_replace(['[', ']'], '', $value);

        if (empty($value)) {
            return [];
        } else {
            return explode(' ', $value);
        }
    }
}
