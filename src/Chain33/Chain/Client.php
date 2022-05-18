<?php

namespace Jason\Chain33\Chain;

use Jason\Chain33\Kernel\BaseClient;
use Jason\Chain33\Kernel\Exceptions\ChainException;
use function PHPUnit\Framework\isNull;

/**
 * 区块链基础信息
 */
class Client extends BaseClient
{
    /**
     * Notes: 获取版本.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 16:18
     *
     * @return array
     */
    public function version(): array
    {
        return $this->request->Version();
    }

    /**
     * Notes: 获取区间区块.
     *
     * @Author : <C.Jason>
     * @Date   : 2020/4/30 16:20
     *
     * @param  int  $start  开始区块高度
     * @param  int  $end  结束区块高度
     * @param  bool  $isDetail  是否打印区块详细信息
     * @return array
     */
    public function blocks(int $start, int $end, bool $isDetail = false): array
    {
        return $this->request->GetBlocks([
            'start'    => $start,
            'end'      => $end,
            'isDetail' => $isDetail,
        ])['items'];
    }

    /**
     * Notes: 获取最新的区块头.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 16:23
     *
     * @return array
     */
    public function lastHeader(): array
    {
        return $this->request->GetLastHeader();
    }

    /**
     * Notes: 获取区间区块头.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 16:25
     *
     * @param  int  $start  开始区块高度
     * @param  int  $end  结束区块高度
     * @param  bool  $isDetail  是否打印区块详细信息
     * @return array
     */
    public function headers(int $start, int $end, bool $isDetail = false): array
    {
        return $this->request->GetHeaders([
            'start'    => $start,
            'end'      => $end,
            'isDetail' => $isDetail,
        ])['items'];
    }

    /**
     * Notes: 获取区块哈希值
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 16:29
     *
     * @param  int  $height
     * @return string
     */
    public function hash(int $height): string
    {
        return $this->request->GetBlockHash([
            'height' => $height,
        ])['hash'];
    }

    /**
     * Notes: 获取区块的详细信息.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 16:31
     *
     * @param  string|int  $hash  区块哈希值
     * @return array
     */
    public function overview($hash): array
    {
        if (is_numeric($hash)) {
            $hash = $this->hash($hash);
        }
        return $this->request->GetBlockOverview([
            'hash' => $hash,
        ]);
    }

    /**
     * Notes: 通过区块哈希获取区块信息.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 16:34
     *
     * @param  string[]  $hashes  区块哈希列表
     * @param  bool  $disableDetail  是否打印区块详细信息
     * @return array
     */
    public function hashes(array $hashes, bool $disableDetail = false): array
    {
        return $this->request->GetBlockByHashes([
            'hashes'        => $hashes,
            'disableDetail' => $disableDetail,
        ])['items'];
    }

    /**
     * Notes: 获取区块的序列信息.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 16:37
     *
     * @param  int  $start  开始区块高度
     * @param  int  $end  结束区块高度
     * @param  bool  $isDetail  是否打印区块详细信息
     * @return array
     */
    public function sequences(int $start, int $end, bool $isDetail = false): array
    {
        return $this->request->GetBlockSequences([
            'start'    => $start,
            'end'      => $end,
            'isDetail' => $isDetail,
        ])['blkseqInfos'];
    }

    /**
     * Notes: 获取最新区块的序列号.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 16:43
     *
     * @return int
     */
    public function lastSequences(): int
    {
        return $this->request->GetLastBlockSequence();
    }

    /**
     * Notes: 注册区块（区块头）推送服务或者合约回执推送服务.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 16:51
     *
     * @param  string  $name  注册名称，长度不能超过 128；一旦通过 name 完成注册，其他订阅用户就不能使用相同的名字进行注册。
     * @param  string  $url  接受推送的 URL，长度不能超过 1024；
     *                       当 name 相同，URL 不同，提示该 name 已经被注册，注册失败；
     *                       当 name 相同，URL 相同 如果推送已经停止，则重新开始推送，如果推送正常，则继续推送。
     * @param  int  $type  推送的数据类型；0:代表区块；1:代表区块头信息；2:代表交易回执
     * @param  string|null  $contract  map[string]bool 订阅的合约名称，当type=2的时候起效，比如“coins=true”
     * @return bool
     * @throws ChainException
     */
    public function addPush(string $name, string $url, int $type, string $contract = null): bool
    {
        $params = [
            'name'          => $name,
            'URL'           => $url,
            'encode'        => 'json',
            'lastSequence'  => 0,
            'lastHeight'    => 0,
            'lastBlockHash' => '',
            'type'          => $type,
        ];
        if ($type == 2 && ! isNull($contract)) {
            $params['contract'] = [
                $contract => true,
            ];
        }
        $result = $this->request->AddPushSubscribe($params);
        if ($result['msg'] == 'Succeed') {
            return true;
        } else {
            throw new ChainException($result['msg']);
        }
    }

    /**
     * Notes: 列举推送服务.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 16:52
     *
     * @return array
     */
    public function pushes(): array
    {
        return $this->request->ListPushes()['pushes'];
    }

    /**
     * Notes: 获取某推送服务最新序列号的值.
     *
     * @Author: <C.Jason>
     * @Date  : 2020/4/30 16:57
     *
     * @param  string  $name  推送服务名
     * @return int
     */
    public function lastPush(string $name): int
    {
        return $this->request->GetPushSeqLastNum([
            'data' => $name,
        ])['data'];
    }
}
