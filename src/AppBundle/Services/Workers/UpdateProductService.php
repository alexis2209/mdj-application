<?php
namespace AppBundle\Services\Workers;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class DownloadService
 * @package AppBundle\Service\Workers
 */
class UpdateProductService implements ConsumerInterface
{

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function execute(AMQPMessage $msg)
    {
        $woocommerce = $this->container->get('app.services.wordpress.woocommerce');
        $product = unserialize($msg->getBody());

        $currentProduct = $woocommerce->getProducts(['sku' => $product['sku']]);

        $metasdata = current($currentProduct)->meta_data;
        $retailer = [];
        $retailers = [];
        $metadata = [];
        $retailers = array_search('_product_retailers', array_column($metasdata, 'key'));
        if (!$retailers){
            $retailer[] = [
                'id' => $product['retailer_id'],
                'product_price' => $product['sale_price'],
                'product_location' => (isset($product['regular_price']))?$product['regular_price']:NULL,
                'product_logo' => NULL,
                'product_url' => $product['external_url']
            ];

            $metadata[] = [
                'key' => '_product_retailers',
                'value' => $retailer
            ];

        }else{
            $value = $metasdata[$retailers]->value;
            $retailer = array_search($product['retailer_id'], array_column($value, 'id'));
            if ($retailer === false){
                $value[] = [
                    'id' => $product['retailer_id'],
                    'product_price' => $product['sale_price'],
                    'product_location' => (isset($product['regular_price']))?$product['regular_price']:NULL,
                    'product_logo' => NULL,
                    'product_url' => $product['external_url']
                ];
            }else{
                $value[$retailer] = [
                    'id' => $product['retailer_id'],
                    'product_price' => $product['sale_price'],
                    'product_location' => (isset($product['regular_price']))?$product['regular_price']:NULL,
                    'product_logo' => NULL,
                    'product_url' => $product['external_url']
                ];
            }


            $metadata[] = [
                'key' => '_product_retailers',
                'value' => $value
            ];

        }

        $product['meta_data'] = $metadata;
        unset($product['retailer_id']);

        $woocommerce->putProduct(current($currentProduct)->id, $product);
    }



}
