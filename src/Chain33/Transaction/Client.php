<?php

namespace Jason\Chain33\Transaction;

use Jason\Chain33\Kernel\BaseClient;
use Jason\Chain33\Kernel\Exceptions\ChainException;
use Jason\Chain33\Kernel\Exceptions\ConfigException;

/**
 * Class Client.
 */
class Client extends BaseClient
{
    /**
     * @var int 若是签名交易组，则为要签名的交易序号，从1开始，小于等于0则为签名组内全部交易
     */
    protected int $signIndex = 0;

    /**
     * Notes   : 转账，coins ，单笔交易.
     *
     * @Date   : 2021/3/24 10:19 上午
     * @Author : <Jason.C>
     *
     * @param  string  $to  转账地址
     * @param  int  $amount  转账金额
     * @param  string  $privateKey  转出账户的私钥
     * @param  int  $fee  转账手续费
     * @param  string  $note  转账备注
     * @return string
     *
     * @throws ChainException
     * @throws ConfigException
     */
    public function coins(string $to, int $amount, string $privateKey, int $fee = 0, string $note = ''): string
    {
        if ($this->isParaChain()) {
            $fee = 0;
        }

        $txHex = $this->request->CreateRawTransaction([
            'to'         => $to,
            'amount'     => $amount,
            'fee'        => $fee,
            'note'       => $note,
            'isWithdraw' => false,
            'execer'     => $this->parseExecer('coins'),
        ]);

        return $this->finalSend($txHex, $privateKey, $fee);
    }

    /**
     * Notes   : 签名并发送交易.
     *
     * @Date   : 2021/3/30 1:50 下午
     * @Author : <Jason.C>
     *
     * @param  string  $txHex  交易数据
     * @param  string  $privateKey  签名私钥
     * @param  int  $fee  手续费
     * @return string
     */
    public function finalSend(string $txHex, string $privateKey, int $fee = 0): string
    {
        $data = $this->sign($txHex, $privateKey, $fee);

        return $this->send($data);
    }

    /**
     * Notes  : 交易签名.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/2 21:28
     *
     * @param  string  $txHex  原始交易数据
     * @param  string  $privateKey
     * @param  int  $fee  费用
     * @return string 交易签名后的十六进制字符串
     */
    public function sign(string $txHex, string $privateKey, int $fee = 0): string
    {
        $txHex = $this->paraTransaction($txHex);

        return $this->request->SignRawTx([
            'privkey' => $privateKey,
            'txHex'   => $txHex,
            'expire'  => '300s',
            'index'   => $this->signIndex,
            'fee'     => $fee,
        ]);
    }

    /**
     * Notes   : 构造并发送不收手续费交易 CreateNoBalanceTransaction（平行链）
     *           构造交易 -> 平行链交易包装 -> 交易签名 -> 发送交易
     *           后面的交易签名步骤里需要注意一点，参数index需填2.
     *
     * @Date   : 2021/1/26 4:22 下午
     * @Author : <Jason.C>
     *
     * @param  string  $txHex  未签名的原始交易数据
     * @return string 未签名的原始交易数据
     */
    public function paraTransaction(string $txHex): string
    {
        if ($this->isParaChain() && $this->config['para_pay_private_key']) {
            $this->signIndex = 2;

            return $this->request->CreateNoBalanceTransaction([
                'txHex'   => $txHex,
                'privkey' => $this->config['para_pay_private_key'],
            ]);
        } else {
            return $txHex;
        }
    }

    /**
     * Notes: 发送交易.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/2 21:33
     *
     * @param  string  $data  签名后的交易数据
     * @return string 交易发送后，生成的交易哈希（后面可以使用此哈希查询交易状态和历史）
     */
    public function send(string $data): string
    {
        return $this->request->SendTransaction([
            'data' => $data,
        ]);
    }

    /**
     * Notes: 转账, token.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/2 21:34
     *
     * @param  string  $to  发送到地址
     * @param  string  $symbol  token名称
     * @param  int  $amount  转账金额
     * @param  string  $privateKey  私钥
     * @param  int  $fee  手续费
     * @param  string  $note  备注
     * @return string
     *
     * @throws ChainException
     * @throws ConfigException
     */
    public function token(
        string $to,
        string $symbol,
        int $amount,
        string $privateKey,
        int $fee = 0,
        string $note = ''
    ): string {
        if ($this->isParaChain()) {
            $fee = 0;
        }

        $txHex = $this->request->CreateRawTransaction([
            'to'          => $to,
            'amount'      => $amount,
            'fee'         => $fee,
            'note'        => $note,
            'isToken'     => true,
            'isWithdraw'  => false,
            'tokenSymbol' => strtoupper($symbol),
            'execName'    => '',
            'execer'      => $this->parseExecer('token'),
        ]);

        return $this->finalSend($txHex, $privateKey, $fee);
    }

    /**
     * Notes   : 转账到合约.
     *
     * @Date   : 2021/4/22 10:26 上午
     * @Author : <Jason.C>
     *
     * @param  string  $symbol  要转账的TOKEN
     * @param  int  $amount  转账金额
     * @param  string  $execName  转到的合约名称，平行链不需要前缀
     * @param  string  $privateKey  转账者私钥
     * @return string
     *
     * @throws ChainException|ConfigException
     */
    public function toExec(string $execName, int $amount, string $symbol, string $privateKey): string
    {
        $toAddr = $this->execToAddress($execName);

        if (strtoupper($symbol) === $this->app->system->coin()) {
            $isToken = false;
        } else {
            $isToken = true;
        }

        $txHex = $this->request->CreateRawTransaction([
            'to'          => $toAddr,
            'amount'      => $amount,
            'fee'         => 0,
            'isToken'     => $isToken,
            'isWithdraw'  => false,
            'tokenSymbol' => strtoupper($symbol),
            'execName'    => $this->parseExecer($execName),
        ]);

        return $this->finalSend($txHex, $privateKey);
    }

    /**
     * Notes   : 从合约中提款.
     *
     * @Date   : 2021/4/22 1:38 下午
     * @Author : <Jason.C>
     *
     * @param  string  $symbol  提款的标识
     * @param  int  $amount  提款金额
     * @param  string  $execName  合约名称，平行链不需要填前缀
     * @param  string  $privateKey  提币私钥
     * @return string
     *
     * @throws ChainException
     * @throws ConfigException
     */
    public function fromExec(
        string $execName,
        int $amount,
        string $symbol,
        string $privateKey
    ): string {
        $toAddr = $this->execToAddress($execName);

        if (strtoupper($symbol) === $this->app->system->coin()) {
            $isToken = false;
        } else {
            $isToken = true;
        }

        $txHex = $this->request->CreateRawTransaction([
            'to'          => $toAddr,
            'amount'      => $amount,
            'fee'         => 0,
            'isToken'     => $isToken,
            'isWithdraw'  => true,
            'tokenSymbol' => strtoupper($symbol),
            'execName'    => $this->parseExecer($execName),
        ]);

        return $this->finalSend($txHex, $privateKey);
    }

    /**
     * Notes: 根据哈希查询交易信息.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/2 21:34
     *
     * @param  string  $hash  交易哈希
     * @return array
     */
    public function query(string $hash): array
    {
        return $this->request->QueryTransaction([
            'hash' => $hash,
        ]);
    }

    /**
     * Notes: 根据地址获取交易信息.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/14 2:48 下午
     *
     * @param  string  $addr  要查询的账户地址
     * @param  int  $count  返回的数据条数
     * @param  int  $flag  交易类型；0：所有涉及到addr的交易； 1：addr作为发送方； 2：addr作为接收方；
     * @param  int  $direction  查询的方向；0：正向查询，区块高度从低到高；-1：反向查询；
     * @param  int  $height  交易所在的block高度，-1：表示从最新的开始向后取；大于等于0的值，从具体的高度+具体index开始取
     * @param  int  $index  交易所在block中的索引，取值0—100000
     * @return array
     */
    public function getTxByAddr(
        string $addr,
        int $count = 100,
        int $flag = 0,
        int $direction = 0,
        int $height = -1,
        int $index = 0
    ): array {
        return $this->request->GetTxByAddr([
            'addr'      => $addr,
            'flag'      => $flag,
            'count'     => $count,
            'direction' => $direction,
            'height'    => $height,
            'index'     => $index,
        ])['txInfos'];
    }

    /**
     * Notes: 获取地址相关摘要信息.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/5/14 2:57 下午
     *
     * @param  string  $addr  要查询的地址信息
     * @return array
     */
    public function overview(string $addr): array
    {
        return $this->request->GetAddrOverview([
            'addr' => $addr,
        ]);
    }

    /**
     * Notes   : 根据哈希数组批量获取交易信息.
     *
     * @Date   : 2021/1/26 4:48 下午
     * @Author : <Jason.C>
     *
     * @param  array  $hashes  交易组
     * @param  bool  $disableDetail  是否隐藏交易详情
     * @return array 交易详情信息
     */
    public function getTxByHashes(array $hashes, bool $disableDetail = false): array
    {
        return $this->request->GetTxByHashes([
            'hashes'        => $hashes,
            'disableDetail' => $disableDetail,
        ])['txs'];
    }

    /**
     * Notes   : 根据哈希获取交易的字符串.
     *
     * @Date   : 2021/1/26 4:51 下午
     * @Author : <Jason.C>
     *
     * @param  string  $hash  交易哈希
     * @return string 交易对象的十六进制编码数据
     */
    public function getHexTxByHash(string $hash): string
    {
        return $this->request->GetHexTxByHash([
            'hash' => $hash,
        ]);
    }

    /**
     * Notes   : 构造交易组.
     *
     * @Date   : 2021/1/26 4:58 下午
     * @Author : <Jason.C>
     *
     * @param  array  $txs  十六进制格式交易数组
     * @return string 交易组对象的十六进制字符串
     */
    public function createRawTxGroup(array $txs): string
    {
        return $this->request->CreateRawTxGroup([
            'txs' => $txs,
        ]);
    }
}
