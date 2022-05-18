<?php

namespace Jason\Chain33;

use Pimple\Container;

/**
 * Class Application.
 *
 * @method static Balance\Client Balance
 * @method static Chain\Client Chain
 * @method static Evm\Client Evm
 * @method static Multisig\Client Multisig
 * @method static Oracle\Client Oracle
 * @method static ParaCross\Client ParaCross
 * @method static Retrieve\Client Retrieve
 * @method static Storage\Client Storage
 * @method static System\Client System
 * @method static Token\Client Token
 * @method static Trade\Client Trade
 * @method static Transaction\Client Transaction
 * @method static Unfreeze\Client Unfreeze
 * 组合的请求方式
 * @method static newAccountLocal()
 */
class Application extends Container
{
    /**
     * 要注册的服务类.
     *
     * @var array
     */
    protected array $providers = [
        Balance\ServiceProvider::class,
        Chain\ServiceProvider::class,
        Evm\ServiceProvider::class,
        Kernel\ServiceProvider::class,
        Manage\ServiceProvider::class,
        System\ServiceProvider::class,
        Token\ServiceProvider::class,
        Transaction\ServiceProvider::class,
        Unfreeze\ServiceProvider::class,
    ];

    /**
     * Application constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this['config'] = static function () {
            return config('chain33');
        };

        $this->registerProviders();
    }

    /**
     * Register providers.
     */
    protected function registerProviders(): void
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

    /**
     * 获取服务 $this->account->do().
     *
     * @Author: <C.Jason>
     * @Date  : 2020/3/17 20:44 下午
     *
     * @param  string  $name  服务名称
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->offsetGet(strtolower($name));
    }

    /**
     * Notes: 获取服务 $this->account($args)->do().
     *
     * @Author: <C.Jason>
     * @Date  : 2020/3/17 20:44 下午
     *
     * @param  string  $name  服务名称
     * @param  $arguments
     * @return mixed
     */
    public function __call(string $name, $arguments)
    {
        return $this->offsetGet(strtolower($name));
    }
}
