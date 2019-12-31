<?php
/**
 * Created by PhpStorm.
 * User: alexis
 * Date: 03/12/19
 * Time: 12:54
 */

namespace AppBundle\Services\Import;


use AppBundle\Entity\Categories;
use Ekino\WordpressBundle\Manager\PostMetaManager;
use League\Csv\Reader;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EffiliateService
{

    private $attributeAge = 6;
    private $attributeBrand = 7;

    public function __construct(ContainerInterface $container, PostMetaManager $postMeta, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->postMeta = $postMeta;
    }

    public function downloadFile($message){
        $fileC = "/tmp/".$message['id'].$message['key'].".csv";
        file_put_contents($fileC, fopen($message['file']['src'], 'r'));
        return $fileC;
    }


    public function loafFile($file, $message){
        $rows = array_map(function($v){return str_getcsv($v, ";");}, file($file));
        $header = array_shift($rows);
        $csv    = array();
        foreach($rows as $row) {
            try{
                $product = array_combine($header, $row);
                $this->readDatas($product, $message);
            }catch(\Exception $e){
                echo '!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! ERREUR !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!' . "\n";
            }
        }
        unset($file);
    }

    public function readDatas($product, $message){
        if (!is_null($message['file']['filter']) && $product['google_product_category'] != $message['file']['filter']){
            return false;
        }

        if ($product['availability'] != 'en stock'){
            return false;
        }

        if (!is_null($product['gtin']) && $product['gtin'] != ''){
            $currentProduct = $this->postMeta->findOneBy(['key'=>'_sku', 'value'=>$product['gtin']]);
        }else{
            return false;
        }

        $this->formatData($product, $message, $currentProduct);
    }

    public function formatData($product, $message, $currentProduct){
        $data = [];
        $data['type'] = 'external';
        $data['retailer_id'] = $message['id'];

        if (!$currentProduct){
            $brand = $product['brand'];
            $libCateg = $product['category'] . ' - ' . $product['category_level2'] . ' - ' .$product['category_level3'] .  ' - ' .$product['category_level4'];
            $categorieId = $this->checkCateg($libCateg, $message['id']);
            if (!$categorieId){
                return false;
            }

            $categories = [
                [
                    'id' => $categorieId
                ]
            ];

            $retailer = [];
            $retailer[] = [
                'id' => $message['id'],
                'product_price' => $product['price'],
                'product_location' => $product['price'],
                'product_logo' => NULL,
                'product_url' => $product['link']
            ];

            $metadata = [];

            $metadata[] = [
                'key' => '_product_retailers',
                'value' => $retailer
            ];
            $metadata[] = [
                'key' => '_knawatfibu_url',
                'value' => ['img_url' => $product['image_link'], 'width' => 390, 'height' => 280]
            ];
            $metadata[] = [
                'key' => '_knawatfibu_alt',
                'value' => $product['title']
            ];

            $data['sale_price'] = $product['price'];
            $data['regular_price'] = $product['price'];
            if ($product['price'] && $product['price'] > 0){
                $data['regular_price'] = $product['price'];
            }

            $data['external_url'] = $product['link'];
            $data['categories'] = $categories;
            $data['name'] = $product['title'];
            $data['description'] = $product['description'];
            $data['short_description'] = $product['description'];
            $data['sku'] = $product['gtin'];
            $data['meta_data'] = $metadata;
            $data['attributes'] = [
                ['id'=>$this->attributeBrand, 'name'=>'Brand','visible' => true, 'options' => [$brand]],
            ];
            $this->container->get('old_sound_rabbit_mq.create_products_producer')->publish(serialize($data));
        }else{
            $data['sku'] = $product['gtin'];
            $data['sale_price'] = $product['price'];
            $data['regular_price'] = $product['price'];
            if ($product['price'] && $product['price'] > 0){
                $data['regular_price'] = $product['price'];
            }
            $data['external_url'] = $product['link'];

            $this->container->get('old_sound_rabbit_mq.update_products_producer')->publish(serialize($data));
        }


        return true;
    }

    public function checkCateg($stringCateg, $id){
        $categorieId = NULL;

        $categorieLib = $stringCateg;

        $testExist = $this->container->get('doctrine')->getManager()->getRepository(Categories::class)->findOneBy(['libelle' => $categorieLib, 'affiliate'=>$id]);

        if (!$testExist){
            $cat = new Categories();

            $cat->setLibelle($categorieLib);
            $cat->setAffiliate($id);
            $this->container->get('doctrine')->getManager()->persist($cat);
            $this->container->get('doctrine')->getManager()->flush();
            return false;
        }elseif (!is_null($testExist->getWpCategorie())){
            $categorieId = $testExist->getWpCategorie();
            return $categorieId;
        }else{
            return false;
        }
    }
}