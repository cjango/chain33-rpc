<?php

namespace Jason\Chain33\System;

use Jason\Chain33\Kernel\BaseClient;

/**
 * Chain33 系统接口
 */
class Client extends BaseClient
{
    /**
     * Notes   : 节点类型.
     *
     * @Date   : 2021/3/25 11:53 上午
     * @Author : <Jason.C>
     *
     * @return string
     */
    public function type(): string
    {
        return $this->isParaChain() ? '平行链' : '主链';
    }

    /**
     * Notes   : 判断是否平行链.
     *
     * @Date   : 2021/10/9 1:47 下午
     * @Author : <Jason.C>
     *
     * @return bool
     */
    public function isParaChain(): bool
    {
        return parent::isParaChain();
    }

    /**
     * Notes: 获取远程节点列表.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 16:05
     *
     * @return array
     */
    public function peers(): array
    {
        if ($this->isParaChain()) {
            return [];
        }

        return $this->request->GetPeerInfo()['peers'];
    }

    /**
     * Notes: 查询节点状态.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 16:04
     *
     * @return array
     */
    public function info(): array
    {
        if ($this->isParaChain()) {
            return [];
        }

        return $this->request->GetNetInfo();
    }

    /**
     * Notes: 查询时间状态.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 16:01
     *
     * @return array
     */
    public function timeStatus(): array
    {
        return $this->request->GetTimeStatus();
    }

    /**
     * Notes: 查询同步状态.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 17:32
     *
     * @return bool
     */
    public function isSync(): bool
    {
        return $this->request->IsSync();
    }

    /**
     * Notes   : 获取主代币信息.
     *
     * @Date   : 2021/1/27 10:35 下午
     * @Author : <Jason.C>
     *
     * @return string
     */
    public function coin(): string
    {
        return $this->request->GetCoinSymbol()['data'];
    }

    /**
     * Notes   : 获取系统支持签名类型.
     *
     * @Date   : 2021/10/8 5:01 下午
     * @Author : <Jason.C>
     *
     * @return mixed
     */
    public function cryptos()
    {
        return $this->request->GetCryptoList()['cryptos'];
    }

    /**
     * Notes   : 时钟同步状态.
     *
     * @Date   : 2021/3/30 9:57 上午
     * @Author : <Jason.C>
     *
     * @return bool
     */
    public function clockSync(): bool
    {
        return $this->request->IsNtpClockSync();
    }

    /**
     * Notes   : 可能是获取失败的数量.
     *
     * @Date   : 2021/3/30 11:19 上午
     * @Author : <Jason.C>
     *
     * @return int
     */
    public function failure(): int
    {
        return $this->request->GetFatalFailure();
    }

    /**
     * Notes   : 交易解析.
     *
     * @Date   : 2021/3/30 11:20 上午
     * @Author : <Jason.C>
     *
     * @param  string  $txHex
     * @return mixed
     */
    public function decode(string $txHex)
    {
        return $this->request->DecodeRawTransaction([
            'txHex' => $txHex,
        ]);
    }
}
