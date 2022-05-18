# 添加/删除 token的完成者 和 黑名单

# 联盟练新增节点

## 1. 新增验证节点

### 1.拷贝节点到服务器上

scp chain33 chain33-cli chain33.toml sendtx peer0/genesis.json root@2.20.105.231:/root/wuxi_fzm_test step2: 加权限： chmod
u+x *
step3: 执行： ./chain33-cli valnode init_keyfile -n 1 -t bls 生成该节点的私钥， 并且重命名将priv_validator_0.json 改成 priv_validator.json
step4: 启动： nohup ./chain33 -f chain33.toml >> wuxi.out& step5: 等待一会，查看 ./chain33-cli net peer info
看节点的同步状态，这一步看的是p2p的网络同步 step6: 查看./chain33-cli valnode nodes， 这一步查看到目前共识节点数目。

## 2. 新增共识节点

### 1.确认目前共识节点数据

登录4台服务器中的任意一台，执行 ./chain33-cli valnode nodes 查看共识节点数目

### 2.新节点公钥

打开新节点的priv_validator.json文件，找到节点生成的公钥（pub_key）

### 3.创建一个可以授权共识节点的管理者

```php
Chain33::Manage()->tendermint($addr, 'add');
```

### 4.addConsensusNode

调用NodeTest类中的addConsensusNode方法，需要拿step2中的公钥，替换方法中的pubkey参数

### 5.执行成功后，再在节点上执行 ./chain33-cli valnode nodes 查看共识节点数目