<?php

namespace Jason\Chain33\Evm;

use Exception;
use Jason\Chain33\Kernel\BaseClient;
use Jason\Chain33\Kernel\Exceptions\ChainException;
use Jason\Chain33\Kernel\Utils\Base58;

/**
 * Class Client.
 */
class Client extends BaseClient
{

    /**
     * @var int 最大GAS消耗
     */
    private int $gas = 300000;

    /**
     * Notes   : 估算合约调用Gas消耗.
     *
     * @Date   : 2021/10/8 4:39 下午
     * @Author : <Jason.C>
     *
     * @param  string  $txHex  部署合约交易或者调用合约交易的序列化后的字符串
     * @param  string  $from  合约交易调用者地址
     * @return int
     *
     * @throws ChainException
     */
    private function estimateGas(string $txHex, string $from): int
    {
        return $this->request->Query([
            'execer'   => $this->parseExecer('evm'),
            'funcName' => 'EstimateGas',
            'payload'  => [
                'tx'   => $txHex,
                'from' => $from,
            ],
        ])['gas'];
    }

    /**
     * Notes   : 调用合约.
     *
     * @Date   : 2021/10/8 5:19 下午
     * @Author : <Jason.C>
     *
     * @param  string  $contractAddr  合约地址
     * @param  string  $abi  部署合约的 abi 内容
     * @param  string  $parameter  操作合约的参数，例如转账交易 “transfer(‘${evm_transferAddr}’, 20)”
     * @param  string  $privateKey  调用者私钥
     * @param  string  $note  合约备注
     * @return string
     *
     * @throws ChainException
     */
    public function invoking(
        string $contractAddr,
        string $abi,
        string $parameter,
        string $privateKey,
        string $note = ''
    ): string {
        $abi   = preg_replace('/\s?/', '', $abi);
        $txHex = $this->request->CreateCallTx([
            'abi'          => $abi,
            'fee'          => $this->gas,
            'note'         => $note,
            'parameter'    => $parameter,
            'contractAddr' => $contractAddr,
            'paraName'     => $this->parseExecer(''),
        ], 'evm');

        $gas = $this->estimateGas($txHex, $this->config['superManager']['address']);

        $txHex = $this->request->CreateCallTx([
            'abi'          => $abi,
            'fee'          => $gas,
            'note'         => $note,
            'parameter'    => $parameter,
            'contractAddr' => $contractAddr,
            'paraName'     => $this->parseExecer(''),
        ], 'evm');

        return $this->app['transaction']->finalSend($txHex, $privateKey, $gas);
    }

    /**
     * Notes   : 部署合约.
     *
     * @Date   : 2021/10/8 4:49 下午
     * @Author : <Jason.C>
     *
     * @param  string  $parameter  部署合约的参数 “constructor(zbc, zbc, 3300, ‘${evmcreatorAddr}’)” 原型为 constructor (string
     *                             memory name, string memory symbol_,uint256 supply, address
     *                             owner),这里表示部署一个名称和symbol都为 zbc，总金额3300*le8，拥有者为 evm_creatorAddr 的ERC20合约
     * @param  string  $code  需要部署合约的 bin 内容
     * @param  string  $abi  部署合约的 abi 内容
     * @param  string  $alias  合约别名
     * @param  string  $privateKey  部署者的私钥
     * @param  string  $note  合约备注
     * @return string
     *
     * @throws ChainException
     */
    public function deploy(
        string $parameter,
        string $code,
        string $abi,
        string $alias,
        string $privateKey,
        string $note = ''
    ): string {
        $abi   = preg_replace('/\s?/', '', $abi);
        $txHex = $this->request->CreateDeployTx([
            'code'      => $code,
            'abi'       => $abi,
            'fee'       => $this->gas,
            'note'      => $note,
            'alias'     => $alias,
            'parameter' => $parameter,
            'paraName'  => $this->parseExecer(''),
        ], 'evm');

        $gas = $this->estimateGas($txHex, $this->config['superManager']['address']);

        $txHex = $this->request->CreateDeployTx([
            'code'      => $code,
            'abi'       => $abi,
            'fee'       => $gas,
            'note'      => $note,
            'alias'     => $alias,
            'parameter' => $parameter,
            'paraName'  => $this->parseExecer(''),
        ], 'evm');

        return $this->app['transaction']->finalSend($txHex, $privateKey, $gas);
    }

    /**
     * Notes   : 获取合约地址.
     *
     * @Date   : 2021/10/8 5:21 下午
     * @Author : <Jason.C>
     *
     * @param  string  $caller  部署合约的地址
     * @param  string  $txhash  创建合约的交易哈希，去掉前面的 0x
     */
    public function getAddr(string $caller, string $txhash): string
    {
        return $this->request->CalcNewContractAddr([
            'caller' => $caller,
            'txhash' => $this->parseHexString($txhash),
        ], 'evm');
    }

    /**
     * Notes   : 查询合约地址是否存在.
     *
     * @Date   : 2021/10/8 5:24 下午
     * @Author : <Jason.C>
     *
     * @param  string  $addr  被查询的合约地址
     * @return array
     *
     * @throws ChainException
     */
    public function checkAddr(string $addr): array
    {
        $result = $this->request->Query([
            'execer'   => $this->parseExecer('evm'),
            'funcName' => 'CheckAddrExists',
            'payload'  => [
                'addr' => $addr,
            ],
        ]);

        return $result;
    }

    /**
     * Notes   : 查询ABI接口方法 pack 后的数据.
     *
     * @Date   : 2021/12/17 4:35 PM
     * @Author : <Jason.C>
     * @param  string  $abi
     * @param  string  $parameter
     * @return string
     * @throws ChainException
     */
    public function getPackData(string $abi, string $parameter): string
    {
        return $this->request->Query([
            'execer'   => $this->parseExecer('evm'),
            'funcName' => 'GetPackData',
            'payload'  => [
                'abi'       => $abi,
                'parameter' => $parameter
            ],
        ])['packData'];
    }

    /**
     * Notes   : 查询什么，
     *
     * @Date   : 2021/12/17 4:39 PM
     * @Author : <Jason.C>
     * @param  string  $address  合约地址
     * @param  string  $abi  合约的ABI代码
     * @param  string  $input  需要查询的信息 pack 后的数据
     * @param  string  $caller  合约部署者地址
     * @return mixed
     * @throws ChainException
     */
    public function query(string $address, string $abi, string $input, string $caller = ''): string
    {
        $query = $this->request->Query([
            'execer'   => $this->parseExecer('evm'),
            'funcName' => 'Query',
            'payload'  => [
                'address' => $address,
                'input'   => $this->getPackData($abi, $input),
                'caller'  => $caller
            ],
        ]);

        return $this->getUnPackData($abi, $input, $query['rawData'])[0];
    }

    /**
     * Notes   : 解码数据.
     *
     * @Date   : 2021/12/17 4:48 PM
     * @Author : <Jason.C>
     * @param  string  $abi
     * @param  string  $parameter
     * @param  string  $data
     * @return array
     * @throws ChainException
     */
    public function getUnPackData(string $abi, string $parameter, string $data): array
    {
        return $this->request->Query([
            'execer'   => $this->parseExecer('evm'),
            'funcName' => 'GetUnpackData',
            'payload'  => [
                'abi'       => $abi,
                'parameter' => $parameter,
                'data'      => $data,
            ],
        ])['unpackData'];
    }

    /**
     * Notes   : evm的地址转换为chain33地址(相当于ETH地址和BTY地址转换).
     *
     * @Date   : 2021/10/8 4:21 下午
     * @Author : <Jason.C>
     *
     * @param  string  $evmAddr
     * @return string
     *
     * @throws Exception
     */
    public function convertToChain(string $evmAddr): string
    {
        $evmAddr   = $this->parseHexString($evmAddr);
        $transAddr = hex2bin($evmAddr);
        $transAddr = '00'.bin2hex($transAddr);
        $checksum  = hash('sha256', hex2bin(hash('sha256', hex2bin($transAddr))));

        return Base58::encode($transAddr.substr($checksum, 0, 8));
    }

    /**
     * Notes   : chain33的地址转换为evm地址.
     *
     * @Date   : 2021/10/8 4:21 下午
     * @Author : <Jason.C>
     *
     * @param  string  $chainAddr
     * @return string
     */
    public function convertToEvm(string $chainAddr): string
    {
        return '0x'.substr((Base58::decode($chainAddr)), 2, 40);
    }
}
