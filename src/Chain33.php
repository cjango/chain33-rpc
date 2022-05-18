<?php

namespace Jason;

use Illuminate\Support\Facades\Facade;

/**
 * Class Chain33.
 *
 * @method static Chain33\Balance\Client Balance
 * @method static Chain33\Chain\Client Chain
 * @method static Chain33\Kernel\Request Client
 * @method static Chain33\Evm\Client Evm
 * @method static Chain33\Manage\Client Manage
 * @method static Chain33\System\Client System
 * @method static Chain33\Token\Client Token
 * @method static Chain33\Transaction\Client Transaction
 * @method static Chain33\Unfreeze\Client Unfreeze
 */
class Chain33 extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Chain33\Application::class;
    }
}
