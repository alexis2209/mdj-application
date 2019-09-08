<?php
namespace AppBundle\Services\Wordpress;

use AppBundle\Services\Wordpress\Api\Client;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ApiWordpressService
 * @package AppBundle\Service
 */
class ApiWordpressService
{

    private $woocommerce;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $url = $this->container->getParameter('urlWoocommerce');
        $consumer_key = 'amarchal';
        $consumer_secret = 'Alexsplif2209';
        $options = [];
        $this->wordpress = new Client($url, $consumer_key, $consumer_secret, $options);
    }

    public function getPosts($data = [])
    {
        return $this->wordpress->get('blurb_product', $data);
    }

    public function postPosts($data)
    {
        $post = $this->wordpress->post('blurb_product', $data);
        $metaObj = $this->container->get('ekino.wordpress.manager.post_meta');
        if ($post && isset($data['meta']) && !empty($data['meta'])){
            foreach ($data['meta'] as $key=>$value){
                $meta = $metaObj->create();
                $meta->setPost($this->container->get('ekino.wordpress.manager.post')->find($post->id));
                $meta->setKey($key);
                $meta->setValue($value);
                $metaObj->save($meta);
            }
        }
        return $post;
    }

    public function putPosts($id, $data)
    {
        $post = $this->wordpress->put('blurb_product/'.$id, $data);
        $metaObj = $this->container->get('ekino.wordpress.manager.post_meta');
        if ($post && isset($data['meta']) && !empty($data['meta'])){
            foreach ($data['meta'] as $key=>$value){
                $meta = $this->container->get('ekino.wordpress.manager.post_meta')->findOneBy(['post'=>$post->id, 'key'=>$key]);
                $meta->setValue($value);
                $metaObj->save($meta);
            }
        }
        return $post;
    }

    public function getCategorie($data = [])
    {
        return $this->wordpress->get('categories', $data);
    }

    public function postCategorie($data = [])
    {
        $term = $this->container->get('ekino.wordpress.manager.term');

        $term = $this->wordpress->post('categories', $data);

        $termTax = $this->container->get('ekino.wordpress.manager.term_taxonomy')->findOneBy(['term'=>$term->id]);
        $termTax->setTaxonomy('blurb_product_category');
        $this->container->get('ekino.wordpress.manager.term_taxonomy')->save($termTax);
    }


}
