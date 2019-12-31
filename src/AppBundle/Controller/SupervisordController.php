<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Class SupervisordController
 * @package AppBundle\Controller\Monitoring
 * @Route("/monitoring/supervisord")
 */
class SupervisordController extends Controller
{
    /**
     * @Route("/server/list", name="app_monitoring_supervisord_server_list", options={"expose":true})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function serverListAction()
    {
        $serversConfiguration   = $this->getParameter('remote_server.config');
        $serverList             = [];

        foreach($serversConfiguration['servers'] as $serverName => $serverConfiguration) {
            if(strpos($serverConfiguration['class'] , 'XmlRpc') !== false) {
                array_push($serverList, $serverName);
            }
        }

        return new JsonResponse($serverList);
    }

    /**
     * @Route("/server/details", name="app_monitoring_supervisord_server", options={"expose":true})
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function serverAction()
    {

        $remoteServer               = $this->get('app.services.supervisord.xmlrpc');
        $processes                  = $remoteServer->listProcess();
        $processList                = [];

        foreach($processes as $process) {
            if(!isset($processList[$process['group']])) {
                $processList[$process['group']] = [];
            }

            array_push($processList[$process['group']], $process);
        }

        $server                     = [];
        $server['process']          = $processList;

        // replace this example code with whatever you need
        return $this->render('@App/Supervisord/server.html.twig', [
            'server' => $server,
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/log/stdout/{servername}/{workergroup}/{workername}", name="app_monitoring_supervisord_log_stdout", options={"expose":true})
     * @param $workername
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function workerLogAction($workergroup, $workername)
    {
        $serversConfiguration   = $this->getParameter('remote_server.config');

        $server         = $this->get('remote_server.server.'.$servername);
        $response       = $server->tailProcessStdoutLog($workergroup.':'.$workername, -1000, 1000);
        $response       = nl2br($response[0]);

        return new JsonResponse($response);
    }

    /**
     * @param $processName
     * @return null
     * @throws \Exception
     */
    private function findProcessDataFromServer($processName)
    {
        //$serversConfiguration   = $this->getParameter('remote_server.config');

        $server         = $this->get('app.services.supervisord.xmlrpc');
        $processList    = $server->listProcess();
        $processData    = null;

        foreach($processList as $process) {
            if($process['name'] == $processName) {
                $processData = $process;
            }
        }

        if(is_null($processData)) {
            throw new \Exception("Process '{$processName}' not found on server");
        }

        return $processData;
    }

    /**
     * @Route("/start/process/{processname}", name="app_monitoring_supervisord_start_process", options={"expose":true})
     * @param $processname
     * @return JsonResponse
     * @throws \Exception
     */
    public function startprocessAction($processname)
    {
        $processData    = $this->findProcessDataFromServer($processname);

        $response       = ['code' => 'KO', 'data' => ''];

        switch($processData['statename']) {
            case 'STOPPED':
            case 'FATAL':
                $response['code'] = 'OK';
                break;
            default:
                $response['code'] = $processData['statename'];
        }

        if($response['code'] != 'OK') {
            return new JsonResponse($response);
        }

        $workerName = ($processData['name'] != $processData['group']) ? $processData['group'].':'.$processData['name'] : $processData['name'];

        try {
            $this->get('app.services.supervisord.xmlrpc')->startProcess($workerName);
        } catch(\Exception $e) {
            $response['code'] = 'KO';
            $response['data'] = $e->getMessage();
        }

        return new JsonResponse($response);
    }

    /**
     * @Route("/start/group/{processgroup}", name="app_monitoring_supervisord_start_group", options={"expose":true})
     * @param $processgroup
     * @return JsonResponse
     * @throws \Exception
     */
    public function startgroupAction($processgroup)
    {
        $response       = ['code' => 'KO', 'data' => ''];

        try {
            $this->get('app.services.supervisord.xmlrpc')->startGroup($processgroup);
            $response['code'] = 'OK';
        } catch(\Exception $e) {
            $response['code'] = 'KO';
            $response['data'] = $e->getMessage();
        }

        return new JsonResponse($response);
    }

    /**
     * @Route("/stop/process/{processname}", name="app_monitoring_supervisord_stop_process", options={"expose":true})
     * @param $processname
     * @return JsonResponse
     * @throws \Exception
     */
    public function stopprocessAction($processname)
    {
        $processData = $this->findProcessDataFromServer($processname);
        $response       = ['code' => 'KO', 'data' => ''];

        switch($processData['statename']) {
            case 'RUNNING':
            case 'STARTING':
                $response['code'] = 'OK';
                break;
            default:
                $response['code'] = $processData['statename'];
        }

        if($response['code'] != 'OK') {
            return new JsonResponse($response);
        }

        $workerName         = ($processData['name'] != $processData['group']) ? $processData['group'].':'.$processData['name'] : $processData['name'];
        $this->get('app.services.supervisord.xmlrpc')->stopProcess($workerName);

        return new JsonResponse($response);
    }

    /**
     * @Route("/stop/group/{processgroup}", name="app_monitoring_supervisord_stop_group", options={"expose":true})
     * @param $processgroup
     * @return JsonResponse
     * @throws \Exception
     */
    public function stopgroupAction($processgroup)
    {
        $response       = ['code' => 'OK', 'data' => ''];
        $this->get('app.services.supervisord.xmlrpc')->stopGroup($processgroup);

        return new JsonResponse($response);
    }

    /**
     * @Route("/start/all", name="app_monitoring_supervisord_start_all")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function startallAction()
    {
        $serversConfiguration   = $this->getParameter('remote_server.config');

        $server         = $this->get('app.services.supervisord.xmlrpc');
        $server->startAllProcesses();

        return $this->indexAction();
    }

    /**
     * @Route("/restart/all/{servername}", name="app_monitoring_supervisord_restart_all")
     * @param $servername
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function restartallAction($servername)
    {
        $serversConfiguration   = $this->getParameter('remote_server.config');

        if(!isset($serversConfiguration['servers'][$servername])) {
            throw new \Exception("Server not found");
        }

        $server         = $this->get('remote_server.server.'.$servername);
        $server->restartAllProcesses();

        return $this->indexAction();
    }

    /**
     * @Route("/stop/all/{servername}", name="app_monitoring_supervisord_stop_all")
     * @param $servername
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function stopallAction($servername)
    {
        $serversConfiguration   = $this->getParameter('remote_server.config');

        if(!isset($serversConfiguration['servers'][$servername])) {
            throw new \Exception("Server not found");
        }

        $server         = $this->get('remote_server.server.'.$servername);
        $server->stopAllProcesses();

        return $this->indexAction();
    }

    /**
     * @Route("/tail/stdout/{workername}", name="app_monitoring_supervisord_tail_stdout")
     * @param $servername
     * @param $workername
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function refreshAction($workername)
    {
        $serversConfiguration   = $this->getParameter('remote_server.config');

        if(!isset($serversConfiguration['servers'][$servername])) {
            throw new \Exception("Server not found");
        }

        $server         = $this->get('remote_server.server.'.$servername);
        $response       = $server->tailProcessStdoutLog($workername, -5000, 5000);

        return $this->render('AppBundle:Monitoring/Supervisord:tail.html.twig', [
            'servername'    => $servername,
            'workername'    => $workername,
            'response'      => $response,
        ]);
    }

    /**
     * @Route("/server/usage/{servername}", name="app_monitoring_supervisord_server_usage", options={"expose":true})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function serverUsageAction($servername)
    {
        $redis        = $this->get('app.service.main_redis_getter')->getRedis();
        $timeDelay          = 300; // Seconds
        $timeInterval       = 60; // Seconds
        $ItemsData          = [];
        $nowDateTime        = new \DateTime(date('Y-m-d H:i'));
        $baseTime           = $nowDateTime->getTimestamp();

        for($i=($baseTime-$timeDelay); $i<=$baseTime; ($i += $timeInterval)) {
            $timeMin = $i;
            $timeMax = $i + $timeInterval;

            // Calculate memory usage
            $memoryUsageData    = $redis->zrangebyscore('stat_serverMemoryUsage_'.$servername.'_'.date('Ymd'), $timeMin, $timeMax);
            $memoryUsage        = [];

            foreach($memoryUsageData as $memory) {
                $memory = (array)json_decode($memory);
                $currentMemoryUsage = $memory['MemTotal'] - $memory['MemFree'] - $memory['Buffers'] - $memory['Cached'];
                $currentMemoryUsage = $currentMemoryUsage / $memory['MemTotal'] * 100;
                array_push($memoryUsage, $currentMemoryUsage);
            }

            $memoryUsagePerc    = count($memoryUsage) > 0 ? round(array_sum($memoryUsage)/count($memoryUsage)) : 0;

            // Calculate charge usage
            $chargeUsageData    = $redis->zrangebyscore('stat_serverCpuCharge_'.$servername.'_'.date('Ymd'), $timeMin, $timeMax);
            $chargeUsagePerc    = count($chargeUsageData) > 0 ? round(array_sum($chargeUsageData)/count($chargeUsageData)) : 0;

            $ItemsData[] = [
                'y' => date('Y-m-d H:i', $timeMin),
                'a' => $memoryUsagePerc,
                'b' => $chargeUsagePerc,
            ];
        }

        return new JsonResponse($ItemsData);
    }
}
