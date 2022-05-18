<?php

namespace Jason\Chain33\Unfreeze;

use DateTimeInterface;
use Jason\Chain33\Kernel\BaseClient;
use Jason\Chain33\Kernel\Exceptions\ChainException;
use Jason\Chain33\Kernel\Exceptions\ConfigException;

/**
 * Class Client.
 */
class Client extends BaseClient
{
    /**
     * Notes: 创建定期解冻合约
     * 1. 开始之前，先将token合部打入冻结合约.
     *
     * @param  string  $beneficiary  受益人
     * @param  string  $symbol  要冻结的资产名称
     * @param  string  $exec  要冻结的资产执行器
     * @param  int  $total  冻结的资产数量
     * @param  DateTimeInterface  $startTime  开始解冻时间
     * @param  string  $algo  解冻算法，支持 'fix' 固定金额, 'LeftProportion' 剩余比例
     * @param  int  $period  解冻周期，单位 秒
     * @param  int  $parameter  解冻数值
     * @param  string  $privateKey  发起人签名私钥
     * @return string
     *
     * @throws ChainException
     * @throws ConfigException
     */
    public function create(
        string $beneficiary,
        string $symbol,
        string $exec,
        int $total,
        DateTimeInterface $startTime,
        string $algo,
        int $period,
        int $parameter,
        string $privateKey
    ): string {
        if (! in_array($algo, ['FixAmount', 'LeftProportion'])) {
            throw new ChainException('不支持的解冻算法');
        }

        $params = [
            'assetSymbol' => $symbol,
            'assetExec'   => $exec,
            'totalCount'  => $total,
            'beneficiary' => $beneficiary,
            'startTime'   => $startTime->getTimestamp(),
            'means'       => $algo,
        ];
        $params = array_merge($params, $this->parseMeans($algo, $period, $parameter));

        $txHex = $this->request->CreateRawUnfreezeCreate($params, 'unfreeze');

        return $this->app['transaction']->finalSend($txHex, $privateKey);
    }

    /**
     * Notes   : 解冻算法规则.
     *
     * @Date   : 2021/3/26 1:17 下午
     * @Author : <Jason.C>
     *
     * @param $means
     * @param $period
     * @param $parameter
     * @return array
     */
    private function parseMeans($means, $period, $parameter): array
    {
        $params = [];
        switch ($means) {
            case 'FixAmount':
                $params = [
                    'FixAmount' => [
                        'period' => $period,
                        'amount' => $parameter,
                    ],
                ];
                break;
            case 'LeftProportion':
                $params = [
                    'LeftProportion' => [
                        'period'        => $period,
                        'tenThousandth' => $parameter,
                    ],
                ];
                break;
        }

        return $params;
    }

    /**
     * Notes   : 查询合约状态
     *
     * @Date   : 2021/3/26 11:25 上午
     * @Author : <Jason.C>
     *
     * @param  string  $unfreezeID  合约的ID，
     * @return array
     *
     * @throws ChainException
     */
    public function status(string $unfreezeID): array
    {
        return $this->request->Query([
            'funcName' => 'GetUnfreeze',
            'execer'   => $this->parseExecer('unfreeze'),
            'payload'  => [
                'data' => $this->parseHexString($unfreezeID),
            ],
        ]);
    }

    /**
     * Notes   : 查询合约可提币量.
     *
     * @Date   : 2021/3/26 10:41 上午
     * @Author : <Jason.C>
     *
     * @param  string  $unfreezeID  合约的ID，可以查询创建冻结合约时得到，同创建冻结合约的交易ID的十六进制，是对应的unfreezeID去掉前缀 “mavl-unfreeze-“。
     * @return int
     *
     * @throws ChainException
     */
    public function balance(string $unfreezeID): int
    {
        $balance = $this->request->Query([
            'funcName' => 'GetUnfreezeWithdraw',
            'execer'   => $this->parseExecer('unfreeze'),
            'payload'  => [
                'data' => $this->parseHexString($unfreezeID),
            ],
        ]);

        if (array_key_exists('availableAmount', $balance)) {
            return (int) $balance['availableAmount'];
        } else {
            throw new ChainException('No Balance in execer');
        }
    }

    /**
     * Notes   : 受益人提取.
     *
     * @Date   : 2021/3/26 11:24 上午
     * @Author : <Jason.C>
     *
     * @param  string  $unfreezeID  冻结合约的ID 可以查询创建冻结合约时，得到， 同创建冻结合约的交易ID的十六进制
     * @param  string  $privateKey  受益人私钥
     * @return string
     */
    public function withdraw(string $unfreezeID, string $privateKey): string
    {
        $txHex = $this->request->CreateRawUnfreezeWithdraw([
            'unfreezeID' => $this->parseHexString($unfreezeID),
        ], 'unfreeze');

        return $this->app['transaction']->finalSend($txHex, $privateKey);
    }

    /**
     * Notes   : 终止冻结合约.
     *
     * @Date   : 2021/3/26 11:23 上午
     * @Author : <Jason.C>
     *
     * @param  string  $unfreezeID  冻结合约的ID
     * @param  string  $privateKey  创建者的私钥
     * @return mixed
     */
    public function terminate(string $unfreezeID, string $privateKey): string
    {
        $txHex = $this->request->CreateRawUnfreezeTerminate([
            'unfreezeID' => $this->parseHexString($unfreezeID),
        ], 'unfreeze');

        return $this->app['transaction']->finalSend($txHex, $privateKey);
    }

    /**
     * Notes   : 用创建地址查询合约列表.
     *
     * @Date   : 2021/3/26 11:22 上午
     * @Author : <Jason.C>
     *
     * @param  string  $creator  创建合约的地址
     * @param  string  $beneficiary  受益人地址
     * @param  int  $count  查询的数量
     * @param  int  $direction  查询的方向
     * @return array
     *
     * @throws ChainException
     */
    public function creator(string $creator = '', string $beneficiary = '', int $count = 100, int $direction = 0): array
    {
        return $this->request->Query([
            'funcName' => 'ListUnfreezeByCreator',
            'execer'   => $this->parseExecer('unfreeze'),
            'payload'  => [
                'initiator'   => $creator,
                'direction'   => $direction,
                'count'       => $count,
                'fromKey'     => '',
                'beneficiary' => $beneficiary,
            ],
        ])['unfreeze'];
    }

    /**
     * Notes   : 用受益地址查询合约列表 ListUnfreezeByBeneficiary.
     *
     * @Date   : 2021/3/26 11:18 上午
     * @Author : <Jason.C>
     *
     * @param  string  $beneficiary  受益人地址
     * @param  string  $creator  创建者地址
     * @param  int  $count  查询的数量
     * @param  int  $direction  查询的方向
     * @return array
     *
     * @throws ChainException
     */
    public function beneficiary(
        string $beneficiary = '',
        string $creator = '',
        int $count = 100,
        int $direction = 0
    ): array {
        return $this->request->Query([
            'funcName' => 'ListUnfreezeByBeneficiary',
            'execer'   => $this->parseExecer('unfreeze'),
            'payload'  => [
                'initiator'   => $creator,
                'direction'   => $direction,
                'count'       => $count,
                'fromKey'     => '',
                'beneficiary' => $beneficiary,
            ],
        ])['unfreeze'];
    }
}
