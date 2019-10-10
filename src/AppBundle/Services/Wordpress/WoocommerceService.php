<?php
namespace AppBundle\Services\Wordpress;

use Automattic\WooCommerce\Client;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ActorService
 * @package AppBundle\Service
 */
class WoocommerceService
{

    private $woocommerce;

    private $attributeAge;
    private $attributeBrand;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $url = $this->container->getParameter('urlWoocommerce');
        $consumer_key = $this->container->getParameter('consumerKeyWoocommerce');
        $consumer_secret = $this->container->getParameter('consumerSecretWoocommerce');
        $options = [];
        $this->woocommerce = new Client($url, $consumer_key, $consumer_secret, $options);


    }

    public function getProducts($data = [])
    {
        return $this->woocommerce->get('products', $data);
    }

    public function postProduct($data)
    {
        return $this->woocommerce->post('products', $data);
    }

    public function putProduct($id, $data)
    {
        return $this->woocommerce->put('products/'.$id, $data);
    }

    public function getCategorie($data = [])
    {
        return $this->woocommerce->get('products/categories', $data);
    }

    public function postCategorie($data = [])
    {
        $cat = $this->woocommerce->post('products/categories', $data);
        return $cat;
    }

    public function getAttributeTerm($id, $data = [])
    {
        return $this->woocommerce->get('products/attributes/'.$id.'/terms', $data);
    }

    public function postAttributeTerm($id, $data = [])
    {
        $cat = $this->woocommerce->post('products/attributes/'.$id.'/terms', $data);
        return $cat;
    }


}
