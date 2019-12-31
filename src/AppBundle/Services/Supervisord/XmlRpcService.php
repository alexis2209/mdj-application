<?php

namespace AppBundle\Services\Supervisord;

/**
 * Class XmlRpc
 * @package Sportnco\RemoteServerBundle\Server
 */
class XmlRpcService
{
    /**
     * @var string
     */
    private $serverUrl;

    /**
     * @var string
     */
    private $headers;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration = ['host'=>'127.0.0.1','port'=>9001,'path'=>'/RPC2','username'=>'user','password'=>'123'])
    {
        $this->serverUrl    = sprintf("http://%s:%d%s",$configuration['host'], $configuration['port'], $configuration['path']);
        $this->headers      = [];
        $this->headers[]    = 'Authorization: Basic '.base64_encode($configuration['username'].':'.$configuration['password']);
        $this->headers[]    = 'Content-Type: application/x-www-form-urlencoded\r\n';
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function listProcess()
    {
        $request    = xmlrpc_encode_request('supervisor.getAllProcessInfo', []);
        $response   = $this->processRequest($request);

        return $response;
    }

    /**
     * @param $workerGroup
     * @param int $number
     * @return mixed
     * @throws \Exception
     */
    public function startGroup($workerGroup, $number = 1)
    {
        $request    = xmlrpc_encode_request('supervisor.startProcessGroup', [$workerGroup, $number]);
        $response   = $this->processRequest($request);

        return $response;
    }

    /**
     * @param $workerName
     * @param int $number
     * @return mixed
     * @throws \Exception
     */
    public function startProcess($workerName, $number = 1)
    {
        $request    = xmlrpc_encode_request('supervisor.startProcess', [$workerName, $number]);
        $response   = $this->processRequest($request);

        return $response;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function startAllProcesses()
    {
        $request    = xmlrpc_encode_request('supervisor.startAllProcesses', [1]);
        $response   = $this->processRequest($request);

        return $response;
    }

    /**
     * @param $workerGroup
     * @param int $number
     * @return mixed
     * @throws \Exception
     */
    public function stopGroup($workerGroup, $number = 1)
    {
        $request    = xmlrpc_encode_request('supervisor.stopProcessGroup', [$workerGroup, $number]);
        $response   = $this->processRequest($request);

        return $response;
    }

    /**
     * @param $workerName
     * @param int $number
     * @return mixed
     * @throws \Exception
     */
    public function stopProcess($workerName, $number = 1)
    {
        $request    = xmlrpc_encode_request('supervisor.stopProcess', [$workerName, $number]);
        $response   = $this->processRequest($request);

        return $response;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function stopAllProcesses()
    {
        $request    = xmlrpc_encode_request('supervisor.stopAllProcesses', [1]);
        $response   = $this->processRequest($request);

        return $response;
    }

    /**
     * @return mixed
     */
    public function restartAllProcesses()
    {
        $this->stopAllProcesses();

        sleep(2);

        $response = $this->startAllProcesses();

        return $response;
    }

    /**
     * @param $workerName
     * @param int $offset
     * @param int $length
     * @return mixed
     * @throws \Exception
     */
    public function tailProcessStdoutLog($workerName, $offset = 0, $length = 50)
    {
        $request    = xmlrpc_encode_request('supervisor.tailProcessStdoutLog', [$workerName, $offset, $length]);
        $response   = $this->processRequest($request);

        return $response;
    }

    /**
     * @param string $request
     * @return mixed
     * @throws \Exception
     */
    private function processRequest($request)
    {
        $options = [
        'http'      => [
        'method'    => 'POST',
        'header'    => $this->headers,
        'content'   => $request
        ]
        ];

        $context  = stream_context_create($options);
        $file     = file_get_contents($this->serverUrl, false, $context);
        $response = xmlrpc_decode(trim($file));

        if (is_array($response) && xmlrpc_is_fault($response)) {
            throw new \Exception($response['faultString'], $response['faultCode']);
        }

        return $response;
    }
}
