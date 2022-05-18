<?php

namespace Jason\Chain33\Kernel;

use Exception;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use Jason\Chain33\Application;
use Jason\Chain33\Kernel\Exceptions\ChainException;
use Jason\Chain33\Kernel\Support\RpcRequest;

class Request
{
    protected Application $app;

    protected array $config;

    protected Guzzle $client;

    public function __construct(Application $app)
    {
        $this->app    = $app;
        $this->config = $app['config'];

        $this->client = new Guzzle([
            'base_uri' => $this->config['base_uri'].':'.$this->config['base_port'],
        ]);
    }

    /**
     * @throws ChainException
     */
    public function __call($method, $args)
    {
        try {
            return $this->request($method, ...$args);
        } catch (ChainException $exception) {
            throw new ChainException($exception->getMessage());
        }
    }

    /**
     * @throws ChainException
     */
    private function request(string $method, array $params = [], $prefix = null)
    {
        $rpcRequest = new RpcRequest();

        if ($prefix) {
            $rpcRequest->setPrefix($prefix);
        }

        $rpcRequest->setMethod($method);

        if (! empty($params)) {
            $rpcRequest->setParams($params);
        }

        try {
            return $this->post($rpcRequest);
        } catch (GuzzleException|ChainException $exception) {
            throw new ChainException($exception->getMessage());
        }
    }

    /**
     * @throws GuzzleException
     * @throws ChainException
     */
    private function post(RpcRequest $body)
    {
        try {
            $response = $this->client->post('', ['body' => (string) $body]);

            $resJson = json_decode($response->getBody()->getContents(), true);

            if ($body->getId() != $resJson['id']) {
                throw new ChainException('No the same id');
            }

            if ($resJson['error']) {
                throw new ChainException($resJson['error']);
            }

            return $resJson['result'];
        } catch (Exception $exception) {
            throw new ChainException($exception->getMessage());
        }
    }
}
