<?php
namespace AppBundle\Services\Zanox;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ActorService
 * @package AppBundle\Service
 */
class DecoannivService
{

    private $_url = 'https://productdata.awin.com/datafeed/download/apikey/c702670fa395ca700e2ff9f3b5b491ff/language/fr/fid/30349/columns/aw_deep_link,product_name,aw_product_id,merchant_product_id,merchant_image_url,description,merchant_category,search_price,merchant_name,merchant_id,category_name,category_id,aw_image_url,currency,store_price,delivery_cost,merchant_deep_link,language,last_updated,brand_name,brand_id,colour,product_short_description,specifications,condition,product_model,model_number,dimensions,keywords,promotional_text,product_type,commission_group,merchant_product_category_path,merchant_product_second_category,merchant_product_third_category,rrp_price,saving,savings_percent,base_price,base_price_amount,base_price_text,product_price_old,delivery_restrictions,delivery_weight,warranty,terms_of_contract,delivery_time,in_stock,stock_quantity,valid_from,valid_to,is_for_sale,web_offer,pre_order,stock_status,size_stock_status,size_stock_amount,merchant_thumb_url,large_image,alternate_image,aw_thumb_url,alternate_image_two,alternate_image_three,reviews,average_rating,rating,number_available,custom_1,custom_2,custom_3,custom_4,custom_5,custom_6,custom_7,custom_8,custom_9,ean,isbn,upc,mpn,parent_product_id,product_GTIN,basket_link/format/xml/dtd/1.5/compression/gzip/';
    private $_fileC = "/tmp/zanox_decoanniv.xml.gz";
    private $_file = "/tmp/zanox_decoanniv.xml";

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function getXml(){
        file_put_contents($this->_fileC, fopen($this->_url, 'r'));

        $string = implode("", gzfile($this->_fileC));
        $fp = fopen($this->_file, "w");
        fwrite($fp, $string, strlen($string));
        fclose($fp);

        if (file_exists($this->_file)) {
            $xml = simplexml_load_file($this->_file);
            return $xml->datafeed;
        } else {

            exit('Echec lors de l\'ouverture du fichier : '.$this->_file);
        }
    }


}
