<?php
namespace AppBundle\Services\Workers;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class DownloadService
 * @package AppBundle\Service\Workers
 */
class CreateProductService implements ConsumerInterface
{

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function execute(AMQPMessage $msg)
    {
        $woocommerce = $this->container->get('app.services.wordpress.woocommerce');
        $product = unserialize($msg->getBody());

        $currentProduct = $woocommerce->getProducts(['sku' => (string)$product->ean]);
    }



}
