<?php

namespace AntanasGa\XmlRpcClient;

use Error;
use Exception;

/**
 * ***Connect*** dispatches calls to outside server
 */
class Connect
{
    private string $serverURL;
    private bool $curl = false;
    private bool $fileGetContents = false;
    private bool $fopen = false;
    private int $timeout = 30;

    /**
     * @param  string $serverURL
     * @param  int $timeout
     */
    public function __construct(string $serverURL, int $timeout = 30)
    {
        $this->serverURL = $serverURL;
        $this->timeout = $timeout;
        $this->checkCurl();
        $this->checkFileGetContents();
        $this->checkfopen();
        if (!$this->curl && !$this->fileGetContents && !$this->fopen) {
            throw new Error('No available message transport function');
        }
    }

    /**
     * ***call*** calls in available method
     *
     * @param  string $context
     * @return string
     */
    public function call(string $context): string
    {
        $response = null;
        if ($this->curl) {
            $response = $this->callCurl($context);
        } elseif ($this->fileGetContents) {
            $response = $this->callFileGetContents($context);
        } elseif ($this->fopen) {
            $response = $this->callFopen($context);
        }
        return $response;
    }

    /**
     * ***callCurl*** calls via `curl`
     *
     * @param  string $context
     * @return string
     */
    private function callCurl(string $context): string
    {
        $curlRequest = curl_init($this->serverURL);
        curl_setopt($curlRequest, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlRequest, CURLINFO_HEADER_OUT, true);
        curl_setopt($curlRequest, CURLOPT_POST, true);
        curl_setopt($curlRequest, CURLOPT_POSTFIELDS, $context);
        curl_setopt($curlRequest, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($curlRequest, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
        $result = curl_exec($curlRequest);
        if ($result === false) {
            throw new Exception('Could not communicate with server');
        }
        curl_close($curlRequest);
        return $result;
    }

    /**
     * ***callFileGetContents*** calls via `file_get_contents`
     *
     * @param  string $context
     * @return string
     */
    private function callFileGetContents(string $context): string
    {
        $options = [
            'http' => [
                'method' => 'POST',
                'timeout' => $this->timeout,
                'header' => 'Content-Type: text/xml',
                'content' => $context,
            ]
        ];
        $sendData = stream_context_create($options);
        $result = file_get_contents($this->serverURL, false, $sendData);
        if ($result === false) {
            throw new Exception('Could not communicate with server');
        }
        return $result;
    }

    /**
     * ***callFopen*** calls via `fopen`
     *
     * @param  string $context
     * @return string
     */
    private function callFopen(string $context): string
    {
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: text/xml\r\n",
                'timeout' => $this->timeout,
                'content' => $context
            ]
        ];
        $sendData = stream_context_create($options);
        $data = fopen($this->serverURL, 'rb', false, $sendData);
        if ($data == false) {
            throw new Exception('Could not communicate with server');
        }
        $response = stream_get_contents($data);
        fclose($data);
        return $response;
    }

    /**
     * ***checkCurl*** Checks if `curl` is available
     *
     * @return void
     */
    private function checkCurl(): void
    {
        if (
            function_exists('curl_init')
            && function_exists('curl_init')
            && function_exists('curl_exec')
            && function_exists('curl_close')
        ) {
            $this->curl = true;
        }
    }

    /**
     * ***checkFileGetContents*** Checks if `file_get_contents` is available
     *
     * @return void
     */
    private function checkFileGetContents()
    {
        if (
            function_exists('file_get_contents')
            && function_exists('stream_context_create')
        ) {
            $this->fileGetContents = true;
        }
    }

    /**
     * ***checkfopen*** Checks if `fopen` is available
     *
     * @return void
     */
    private function checkfopen()
    {
        if (
            function_exists('fopen')
            && function_exists('stream_context_create')
            && function_exists('fpassthru')
            && function_exists('fclose')
        ) {
            $this->fopen = true;
        }
    }
}
