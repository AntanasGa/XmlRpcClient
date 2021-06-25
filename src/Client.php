<?php

namespace AntanasGa\XmlRpcClient;

use AntanasGa\XmlRpcDecode\Decode;
use AntanasGa\XmlRpcDecode\ResponseError;
use AntanasGa\XmlRpcEncode\Encode;

/**
 * ***Client*** handles XMLRPC requests
 * uses `__call` to for request, gives request name
 */
class Client
{
    private Connect $requests;
    private bool $raw = false;
    private ?ResponseError $error = null;

    /**
     * @param  string $serverURL
     * @param  int $timeout total allowed request time in seconds
     */
    public function __construct(string $serverURL, int $timeout = 30)
    {
        $this->requests = new Connect($serverURL, $timeout);
    }

    /**
     * ***getRaw*** request basis settable variable
     *
     * @return self
     */
    public function getRaw(): self
    {
        $this->raw = true;
        return $this;
    }

    /**
     * ***__call*** calls server with request of function name
     *
     * @param  string $name method name
     * @param  array $arguments method variables
     * @return array|string
     */
    public function __call(string $name, array $arguments)
    {
        $response = null;
        $this->error = null;
        $context = new Encode($arguments, $name);
        $call = $this->requests->call($context);
        if ($this->raw) {
            $response = $call;
            $this->raw = false;
        } else {
            $parsed = new Decode($call);
            $response = $parsed->fetch();
            $this->error = $parsed->errorInfo();
        }
        return $response;
    }

    /**
     * ***errorInfo*** gets request failure details
     *
     * @return ResponseError
     */
    public function errorInfo(): ?ResponseError
    {
        return $this->error;
    }
}
