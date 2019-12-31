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
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AwinService
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
        $fileC = "/tmp/".$message['id'].$message['key'].".xml.gz";
        file_put_contents($fileC, fopen($message['file']['src'], 'r'));
        return $fileC;
    }


    public function loafFile($file, $message){
        $xml = new \XMLReader();
        $xml->open('compress.zlib://'.$file);
        while($xml->read() && $xml->name != 'prod'){;}
        while($xml->name == 'prod')
        {
            $product = new \SimpleXMLElement($xml->readOuterXML());
            $this->readDatas($product, $message);

            $xml->next('prod');
            unset($product);
        }
        unset($file);
    }

    public function readDatas($product, $message){
        if (!is_null($message['file']['filter']) && (string)$product->cat->mCat != $message['file']['filter']){
            return false;
        }

        if (!is_null((string)$product->ean) && (string)$product->ean != ''){
            $currentProduct = $this->postMeta->findOneBy(['key'=>'_sku', 'value'=>(string)$product->ean]);
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
            $brand = (string)$product->brand->brandName;

            $categorieId = $this->checkCateg((string)$product->cat->merchantProductCategoryPath, $message['id']);
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
                'product_price' => (string)$product->price->buynow,
                'product_location' => (string)$product->price->productPriceOld,
                'product_logo' => NULL,
                'product_url' => (string)$product->uri->awTrack
            ];

            $metadata = [];

            $metadata[] = [
                'key' => '_product_retailers',
                'value' => $retailer
            ];
            $metadata[] = [
                'key' => '_knawatfibu_url',
                'value' => ['img_url' => (string)$product->uri->mImage, 'width' => 390, 'height' => 280]
            ];
            $metadata[] = [
                'key' => '_knawatfibu_alt',
                'value' => (string)$product->text->name
            ];

            $data['sale_price'] = (string)$product->price->buynow;
            $data['regular_price'] = (string)$product->price->buynow;
            if ((string)$product->price->productPriceOld && (string)$product->price->productPriceOld > 0){
                $data['regular_price'] = (string)$product->price->productPriceOld;
            }

            $data['external_url'] = (string)$product->uri->awTrack;
            $data['categories'] = $categories;
            $data['name'] = (string)$product->text->name;
            $data['description'] = (string)$product->text->desc;
            $data['short_description'] = (string)$product->text->desc;
            $data['sku'] = (string)$product->ean;
            $data['meta_data'] = $metadata;
            $data['attributes'] = [
                ['id'=>$this->attributeBrand, 'name'=>'Brand','visible' => true, 'options' => [$brand]],
            ];
            $this->container->get('old_sound_rabbit_mq.create_products_producer')->publish(serialize($data));
        }else{
            $data['sku'] = (string)$product->ean;
            $data['sale_price'] = (string)$product->price->buynow;
            $data['regular_price'] = (string)$product->price->buynow;
            if ((string)$product->price->productPriceOld && (string)$product->price->productPriceOld > 0){
                $data['regular_price'] = (string)$product->price->productPriceOld;
            }
            $data['external_url'] = (string)$product->uri->awTrack;

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