<?php
namespace AppBundle\Services\Workers;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class DownloadService
 * @package AppBundle\Service\Workers
 */
class ParseService implements ConsumerInterface
{

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function execute(AMQPMessage $msg)
    {
        //$msg will be an instance of `PhpAmqpLib\Message\AMQPMessage` with the $msg->body being the data sent over RabbitMQ.
        $message = unserialize($msg->getBody());

        $import = $this->container->get('app.services.import.'.$message['message']['file']['type']);

        $datas = $import->loafFile($message['file'], $message['message']);

        //$import->readDatas($datas, $message['message']);

        //unlink($message['file']);
    }

}
