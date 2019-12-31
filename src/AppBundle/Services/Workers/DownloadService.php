<?php
namespace AppBundle\Services\Workers;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class DownloadService
 * @package AppBundle\Service\Workers
 */
class DownloadService implements ConsumerInterface
{

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get('logger');
    }

    public function execute(AMQPMessage $msg)
    {
        $debut = microtime(true);
        //$msg will be an instance of `PhpAmqpLib\Message\AMQPMessage` with the $msg->body being the data sent over RabbitMQ.
        $message = unserialize($msg->getBody());

        $import = $this->container->get('app.services.import.'.$message['file']['type']);
        $file = $import->downloadFile($message);

        if (file_exists($file)) {
            $msg = array('id' => $message['id'], 'key' => $message['key'], 'file' => $file, 'message'=>$message);
            $this->container->get('old_sound_rabbit_mq.parse_files_producer')->publish(serialize($msg));
            $this->logger->info($message['id'] . ' - ' . $message['key']);
        } else {
            $this->logger->crit('Echec lors de l\'ouverture du fichier : '.$file);
            exit();
        }
    }



}
