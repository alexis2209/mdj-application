<?php
namespace AppBundle\Command;

use AppBundle\Entity\CatMisterGoodDeal;
use Automattic\WooCommerce\Client;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ImportMisterGoodDealCommand
 * @package AppBundle\Command
 */
class ImportMisterGoodDealCommand extends Command
{
    /**
     * @var Container
     */
    private $container;

    /**
     * ImportMisterGoodDealCommand constructor.
     * @param Container $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->attributeAge = 6;
        $this->attributeBrand = 7;
        //$this->retailersNum = 28147;
        //$this->retailersNum = 30650;
        $this->retailersNum = 30781;

        $this->listFiles = [
            1 => 'https://productdata.awin.com/datafeed/download/apikey/c702670fa395ca700e2ff9f3b5b491ff/language/fr/fid/35495/columns/aw_deep_link,product_name,aw_product_id,merchant_product_id,merchant_image_url,description,merchant_category,search_price,merchant_name,merchant_id,category_name,category_id,aw_image_url,currency,store_price,delivery_cost,merchant_deep_link,language,last_updated,brand_name,brand_id,colour,product_short_description,specifications,condition,product_model,model_number,dimensions,keywords,promotional_text,product_type,commission_group,merchant_product_category_path,merchant_product_second_category,merchant_product_third_category,rrp_price,saving,savings_percent,base_price,base_price_amount,base_price_text,product_price_old,delivery_restrictions,delivery_weight,warranty,terms_of_contract,delivery_time,in_stock,stock_quantity,valid_from,valid_to,is_for_sale,web_offer,pre_order,stock_status,size_stock_status,size_stock_amount,merchant_thumb_url,large_image,alternate_image,aw_thumb_url,alternate_image_two,alternate_image_three,reviews,average_rating,rating,number_available,custom_1,custom_2,custom_3,custom_4,custom_5,custom_6,custom_7,custom_8,custom_9,ean,isbn,upc,mpn,parent_product_id,product_GTIN,basket_link/format/xml/dtd/1.5/compression/gzip/adultcontent/1/',
            2 => 'https://productdata.awin.com/datafeed/download/apikey/c702670fa395ca700e2ff9f3b5b491ff/language/fr/fid/35507/columns/aw_deep_link,product_name,aw_product_id,merchant_product_id,merchant_image_url,description,merchant_category,search_price,merchant_name,merchant_id,category_name,category_id,aw_image_url,currency,store_price,delivery_cost,merchant_deep_link,language,last_updated,brand_name,brand_id,colour,product_short_description,specifications,condition,product_model,model_number,dimensions,keywords,promotional_text,product_type,commission_group,merchant_product_category_path,merchant_product_second_category,merchant_product_third_category,rrp_price,saving,savings_percent,base_price,base_price_amount,base_price_text,product_price_old,delivery_restrictions,delivery_weight,warranty,terms_of_contract,delivery_time,in_stock,stock_quantity,valid_from,valid_to,is_for_sale,web_offer,pre_order,stock_status,size_stock_status,size_stock_amount,merchant_thumb_url,large_image,alternate_image,aw_thumb_url,alternate_image_two,alternate_image_three,reviews,average_rating,rating,number_available,custom_1,custom_2,custom_3,custom_4,custom_5,custom_6,custom_7,custom_8,custom_9,ean,isbn,upc,mpn,parent_product_id,product_GTIN,basket_link/format/xml/dtd/1.5/compression/gzip/adultcontent/1/',
            3 => 'https://productdata.awin.com/datafeed/download/apikey/c702670fa395ca700e2ff9f3b5b491ff/language/fr/fid/35509/columns/aw_deep_link,product_name,aw_product_id,merchant_product_id,merchant_image_url,description,merchant_category,search_price,merchant_name,merchant_id,category_name,category_id,aw_image_url,currency,store_price,delivery_cost,merchant_deep_link,language,last_updated,brand_name,brand_id,colour,product_short_description,specifications,condition,product_model,model_number,dimensions,keywords,promotional_text,product_type,commission_group,merchant_product_category_path,merchant_product_second_category,merchant_product_third_category,rrp_price,saving,savings_percent,base_price,base_price_amount,base_price_text,product_price_old,delivery_restrictions,delivery_weight,warranty,terms_of_contract,delivery_time,in_stock,stock_quantity,valid_from,valid_to,is_for_sale,web_offer,pre_order,stock_status,size_stock_status,size_stock_amount,merchant_thumb_url,large_image,alternate_image,aw_thumb_url,alternate_image_two,alternate_image_three,reviews,average_rating,rating,number_available,custom_1,custom_2,custom_3,custom_4,custom_5,custom_6,custom_7,custom_8,custom_9,ean,isbn,upc,mpn,parent_product_id,product_GTIN,basket_link/format/xml/dtd/1.5/compression/gzip/adultcontent/1/',
            4 => 'https://productdata.awin.com/datafeed/download/apikey/c702670fa395ca700e2ff9f3b5b491ff/language/fr/fid/35511/columns/aw_deep_link,product_name,aw_product_id,merchant_product_id,merchant_image_url,description,merchant_category,search_price,merchant_name,merchant_id,category_name,category_id,aw_image_url,currency,store_price,delivery_cost,merchant_deep_link,language,last_updated,brand_name,brand_id,colour,product_short_description,specifications,condition,product_model,model_number,dimensions,keywords,promotional_text,product_type,commission_group,merchant_product_category_path,merchant_product_second_category,merchant_product_third_category,rrp_price,saving,savings_percent,base_price,base_price_amount,base_price_text,product_price_old,delivery_restrictions,delivery_weight,warranty,terms_of_contract,delivery_time,in_stock,stock_quantity,valid_from,valid_to,is_for_sale,web_offer,pre_order,stock_status,size_stock_status,size_stock_amount,merchant_thumb_url,large_image,alternate_image,aw_thumb_url,alternate_image_two,alternate_image_three,reviews,average_rating,rating,number_available,custom_1,custom_2,custom_3,custom_4,custom_5,custom_6,custom_7,custom_8,custom_9,ean,isbn,upc,mpn,parent_product_id,product_GTIN,basket_link/format/xml/dtd/1.5/compression/gzip/adultcontent/1/',
            5 => 'https://productdata.awin.com/datafeed/download/apikey/c702670fa395ca700e2ff9f3b5b491ff/language/fr/fid/35513/columns/aw_deep_link,product_name,aw_product_id,merchant_product_id,merchant_image_url,description,merchant_category,search_price,merchant_name,merchant_id,category_name,category_id,aw_image_url,currency,store_price,delivery_cost,merchant_deep_link,language,last_updated,brand_name,brand_id,colour,product_short_description,specifications,condition,product_model,model_number,dimensions,keywords,promotional_text,product_type,commission_group,merchant_product_category_path,merchant_product_second_category,merchant_product_third_category,rrp_price,saving,savings_percent,base_price,base_price_amount,base_price_text,product_price_old,delivery_restrictions,delivery_weight,warranty,terms_of_contract,delivery_time,in_stock,stock_quantity,valid_from,valid_to,is_for_sale,web_offer,pre_order,stock_status,size_stock_status,size_stock_amount,merchant_thumb_url,large_image,alternate_image,aw_thumb_url,alternate_image_two,alternate_image_three,reviews,average_rating,rating,number_available,custom_1,custom_2,custom_3,custom_4,custom_5,custom_6,custom_7,custom_8,custom_9,ean,isbn,upc,mpn,parent_product_id,product_GTIN,basket_link/format/xml/dtd/1.5/compression/gzip/adultcontent/1/',
            6 => 'https://productdata.awin.com/datafeed/download/apikey/c702670fa395ca700e2ff9f3b5b491ff/language/fr/fid/28737/columns/aw_deep_link,product_name,aw_product_id,merchant_product_id,merchant_image_url,description,merchant_category,search_price,merchant_name,merchant_id,category_name,category_id,aw_image_url,currency,store_price,delivery_cost,merchant_deep_link,language,last_updated,brand_name,brand_id,colour,product_short_description,specifications,condition,product_model,model_number,dimensions,keywords,promotional_text,product_type,commission_group,merchant_product_category_path,merchant_product_second_category,merchant_product_third_category,rrp_price,saving,savings_percent,base_price,base_price_amount,base_price_text,product_price_old,delivery_restrictions,delivery_weight,warranty,terms_of_contract,delivery_time,in_stock,stock_quantity,valid_from,valid_to,is_for_sale,web_offer,pre_order,stock_status,size_stock_status,size_stock_amount,merchant_thumb_url,large_image,alternate_image,aw_thumb_url,alternate_image_two,alternate_image_three,reviews,average_rating,rating,number_available,custom_1,custom_2,custom_3,custom_4,custom_5,custom_6,custom_7,custom_8,custom_9,ean,isbn,upc,mpn,parent_product_id,product_GTIN,basket_link/format/xml/dtd/1.5/compression/gzip/adultcontent/1/',
        ];

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:import:mistergooddeal')
            ->setDescription('Import XML File from Miset Good Deal');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $woocommerce = $this->container->get('app.services.wordpress.woocommerce');
        $xml = '';
        foreach ( $this->listFiles as $key => $files){
            unset($xml);
            echo "FILE : ".$key . "\n\n\n\n\n\n";
            $xml = $this->container->get('app.services.zanox.mistergooddeal')->getXml($files, $key);
            $i = 1;
            foreach ($xml->prod as $product)
            {
                if ((string)$product->cat->mCat != 'Jouets'){
                    continue;
                }

                if (!is_null((string)$product->ean) && (string)$product->ean != ''){
                    $currentProduct = $woocommerce->getProducts(['sku' => (string)$product->ean]);
                }else{
                    continue;
                }
                $data = [];
                $data['type'] = 'external';

                if (!$currentProduct) {
                    $categorieId = NULL;


                    //var_dump($product);exit;
                    $categoriesMisterGoodDeal = (string)$product->cat->merchantProductCategoryPath;

                    $testExist = $this->container->get('doctrine')->getManager()->getRepository(CatMisterGoodDeal::class)->findOneBy(['libelle' => $categoriesMisterGoodDeal]);

                    if (!$testExist){
                        $cat = new CatMisterGoodDeal();

                        $cat->setLibelle($categoriesMisterGoodDeal);
                        $this->container->get('doctrine')->getManager()->persist($cat);
                        $this->container->get('doctrine')->getManager()->flush();
                        echo "CATEGORIE : ".(string)$product->cat->merchantProductCategoryPath . "\n";
                        continue;
                    }elseif (!is_null($testExist->getWpCategorie())){
                        $categorieId = $testExist->getWpCategorie();
                    }else{
                        continue;
                    }


                    $brand = (string)$product->brand->brandName;

                    $categories = [
                        [
                            'id' => $categorieId
                        ]
                    ];

                    $retailer = [];
                    $retailer[] = [
                        'id' => $this->retailersNum,
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

                    $jouet = $woocommerce->postProduct($data);
                    echo "NOUVEAU JOUET : ";
                }else{

                    $data['sale_price'] = (string)$product->price->buynow;
                    $data['regular_price'] = (string)$product->price->buynow;
                    if ((string)$product->price->productPriceOld && (string)$product->price->productPriceOld > 0){
                        $data['regular_price'] = (string)$product->price->productPriceOld;
                    }

                    if (!current($currentProduct)->external_url ||current($currentProduct)->external_url == NULL ||current($currentProduct)->external_url == ""){
                        $data['external_url'] = (string)$product->uri->awTrack;
                    }




                    $metasdata = current($currentProduct)->meta_data;
                    $retailer = [];
                    $retailers = [];
                    $metadata = [];
                    $retailers = array_search('_product_retailers', array_column($metasdata, 'key'));
                    if (!$retailers){
                        $retailer[] = [
                            'id' => $this->retailersNum,
                            'product_price' => (string)$product->price->buynow,
                            'product_location' => (string)$product->price->productPriceOld,
                            'product_logo' => NULL,
                            'product_url' => (string)$product->uri->awTrack
                        ];

                        $metadata[] = [
                            'key' => '_product_retailers',
                            'value' => $retailer
                        ];

                    }else{
                        $value = $metasdata[$retailers]->value;
                        $retailer = array_search($this->retailersNum, array_column($value, 'id'));
                        if ($retailer === false){
                            $value[] = [
                                'id' => $this->retailersNum,
                                'product_price' => (string)$product->price->buynow,
                                'product_location' => (string)$product->price->productPriceOld,
                                'product_logo' => NULL,
                                'product_url' => (string)$product->uri->awTrack
                            ];
                        }else{
                            $value[$retailer] = [
                                'id' => $this->retailersNum,
                                'product_price' => (string)$product->price->buynow,
                                'product_location' => (string)$product->price->productPriceOld,
                                'product_logo' => NULL,
                                'product_url' => (string)$product->uri->awTrack
                            ];
                        }


                        $metadata[] = [
                            'key' => '_product_retailers',
                            'value' => $value
                        ];
                    }

                    $data['meta_data'] = $metadata;


                    $woocommerce->putProduct(current($currentProduct)->id, $data);
                }

                echo $i . ' ' . (string)$product->text->name . "\n";
                $i++;
            }
            unlink("/tmp/zanox_mistergooddeal".$key.".xml.gz");
            unlink("/tmp/zanox_mistergooddeal".$key.".xml");
        }


        echo $i . "\n";
        echo 'ok';
    }

}
