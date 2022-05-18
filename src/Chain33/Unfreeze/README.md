```json
{
    "means": "FixAmount",
    "assetExec": "coins",
    "key": "000000000053200001",
    "startTime": "10000",
    "fixAmount": {
        "period": "10",
        "amount": "1000000"
    },
    "terminated": false,
    "initiator": "1EDnnePAZN48aC2hiTDzhkczfF39g1pZZX",
    "remaining": "0",
    "unfreezeID": "mavl-unfreeze-086bca6fa6a7f232df95802637c756526abc04a9ea7169191fb90f389d914471",
    "beneficiary": "1PUiGcbsccfxW3zuvHXZBJfznziph5miAo",
    "assetSymbol": "bty",
    "totalCount": "400000000"
}
```

返回值说明

| 参数 | 类型 | 说明 |
| --- | --- | --- |
| means | string | 合约解冻算法名 |
| assetExec | string | 资产所在执行器名 |
| startTime | string | 合约生效时间， UTC 秒数 |
| assetSymbol | string | 资产标识 |
| unfreezeID | string | 这里合约的ID |
| initiator | string | 合约创建者 |
| beneficiary | string | 合约受益人 |
| fixAmount | array | 算法对应参数 |
| totalCount | string | 冻结资产总数 |
| remaining | string | 合约中剩余资产总数 |
| terminated | boolean | 是否终结 |

```php
// 创建冻结合约的一个例子
$txHex = $this->client->CreateRawUnfreezeCreate([
    'assetSymbol' => 'UZI',
    'assetExec'   => 'coins',
    'means'       => 'FixAmount',
    'totalCount'  => 200,
    'beneficiary' => '134W55fT3hDt5XEowQzz8gFnSkfRUtg24u',
    'startTime'   => time() + 10,
    'fixAmount'   => [
        'period' => 5,
        'amount' => 4,
    ],
], 'unfreeze');
```

## 自动解冻合约流程

-
    1. 发起人先转币到unfreeze合约
-
    2. 创建解冻合约
-
    3. 受益人查看可提现余额
-
    4. 受益人提现
-
    5. 受益人从合约提币到账户