<?php
namespace AppBundle\Services\Workers;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class DownloadService
 * @package AppBundle\Service\Workers
 */
class CheckProductService implements ConsumerInterface
{

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function execute(AMQPMessage $msg)
    {
        $product = unserialize($msg->getBody());

        $currentProduct = $postMetaObj = $this->container->get('ekino.wordpress.manager.post_meta')->findOneBy(['key'=>'_sku', 'value'=>$product['product']['ean']]);
        $msg = array('id' => $product['id'], 'product' => $product['product']);
        if (!$currentProduct) {
            $this->container->get('old_sound_rabbit_mq.create_products_producer')->publish(serialize($msg));
        }else{
            $this->container->get('old_sound_rabbit_mq.update_products_producer')->publish(serialize($msg));
        }
    }



}
