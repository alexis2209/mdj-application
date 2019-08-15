<?php
namespace AppBundle\Services\Wordpress;

use AppBundle\Entity\Repository\CategoryRepository;
use AppBundle\Entity\Repository\CompetitionRepository;
use AppBundle\Entity\Repository\EventOperatorRepository;
use AppBundle\Entity\Repository\SportRepository;
use AppBundle\Entity\Repository\StakeChoiceRepository;
use AppBundle\Services\Operator\OperatorResolver;
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
        return $this->woocommerce->post('products/categories', $data);
    }


}
