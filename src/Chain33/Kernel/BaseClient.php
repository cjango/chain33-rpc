<?php

namespace Jason\Chain33\Kernel;

use Illuminate\Support\Str;
use Jason\Chain33\Application;
use Jason\Chain33\Kernel\Exceptions\ChainException;
use Jason\Chain33\Kernel\Exceptions\ConfigException;

abstract class BaseClient
{
    protected Application $app;

    protected array $config;

    protected Request $request;

    public function __construct(Application $app)
    {
        $this->app     = $app;
        $this->config  = $app['config'];
        $this->request = $app['request'];
    }

    /**
     * Notes   : 是否是平行链.
     *
     * @Date   : 2021/3/22 4:18 下午
     * @Author : <Jason.c>
     */
    protected function isParaChain(): bool
    {
        return $this->config['para_name'] && preg_match('/user\.p\.[a-zA-Z\d]*\./', $this->config['para_name']);
    }

    /**
     * Notes   : 解析平行链的执行器地址
     *
     * @Date   : 2021/3/22 2:48 下午
     * @Author : <Jason.c>
     *
     * @param  string  $execer
     * @return string
     *
     * @throws ChainException
     */
    protected function parseExecer(string $execer): string
    {
        if ($this->config['para_name']) {
            if (! preg_match('/user\.p\.[a-zA-Z\d]*\./', $this->config['para_name'])) {
                throw new ChainException('平行链名称配置不正确');
            }

            return $this->config['para_name'].$execer;
        } else {
            return $execer;
        }
    }

    /**
     * Notes   : 获取执行器地址
     *
     * @Date   : 2022/5/18 14:20
     * @Author : <Jason.C>
     * @param  string  $name
     * @return string
     * @throws ChainException
     */
    public function execToAddress(string $name): string
    {
        return $this->request->ConvertExectoAddr([
            'execname' => $this->parseExecer($name),
        ]);
    }

    /**
     * Notes   : 格式化16进制字符串.
     *
     * @Date   : 2021/3/26 11:05 上午
     * @Author : <Jason.c>
     *
     * @param  string  $hex
     * @return string
     */
    protected function parseHexString(string $hex): string
    {
        if (Str::startsWith($hex, '0x')) {
            $hex = Str::substr($hex, 2);
        }

        return $hex;
    }
}
