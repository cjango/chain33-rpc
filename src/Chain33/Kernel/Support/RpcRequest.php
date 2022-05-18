<?php

namespace Jason\Chain33\Kernel\Support;

class RpcRequest
{
    protected string $jsonrpc = '2.0';

    protected int $id;

    protected ?string $method;

    protected array $params = [];

    protected string $prefix = 'Chain33';

    public function __construct(int $id = null, string $method = null, array $params = [])
    {
        $this->id     = $id ?: time();
        $this->method = $method;
        $this->params = $params;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function setMethod(string $method): RpcRequest
    {
        $this->method = $this->prefix.'.'.$method;

        return $this;
    }

    public function setParams($params = []): RpcRequest
    {
        $this->params[] = $params;

        return $this;
    }

    public function toJson(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        $data = [
            'jsonrpc' => $this->jsonrpc,
            'id'      => $this->id,
            'method'  => $this->method,
            'params'  => $this->params,
        ];

        return json_encode($data);
    }
}
