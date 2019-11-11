<?php
namespace AppBundle\Command;

use AppBundle\Entity\CatCdiscount;
use Automattic\WooCommerce\Client;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ImportCdiscountCommand
 * @package AppBundle\Command
 */
class ImportCdiscountCommand extends Command
{
    /**
     * @var Container
     */
    private $container;

    /**
     * ImportCdiscountCommand constructor.
     * @param Container $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->attributeAge = 6;
        $this->attributeBrand = 7;
        //$this->retailersNum = 28147;
        $this->retailersNum = 28317;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:import:cdiscount')
            ->setDescription('Import XML File from Cdiscount');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $woocommerce = $this->container->get('app.services.wordpress.woocommerce');
        $xml = $this->container->get('app.services.zanox.cdiscount')->getXml();
        $i = 1;
        foreach ($xml->prod as $product)
        {
            if ((string)$product->cat->mCat != 'JEUX - JOUETS'){
                continue;
            }

            if (!is_null((string)$product->ean) && (string)$product->ean != ''){
                $currentProduct = $woocommerce->getProducts(['sku' => (string)$product->ean]);
            }else{
                continue;
            }
            $data = [];
            $data['type'] = 'external';
            if ($currentProduct && (string)$product->price->buynow <= current($currentProduct)->price){
                if ((string)$product->price->productPriceOld && (string)$product->price->productPriceOld > 0){
                    $data['regular_price'] = (string)$product->price->productPriceOld;
                }else{
                    $data['regular_price'] = NULL;
                }
                $data['sale_price'] = (string)$product->price->buynow;
                $data['external_url'] = (string)$product->uri->awTrack;
            }

            if (!$currentProduct) {
                $categorieId = NULL;


                //var_dump($product);exit;
                $categoriesCdiscount = (string)$product->cat->merchantProductCategoryPath;

                $testExist = $this->container->get('doctrine')->getManager()->getRepository(CatCdiscount::class)->findOneBy(['libelle' => $categoriesCdiscount]);

                if (!$testExist){
                    $cat = new CatCdiscount();

                    $cat->setLibelle($categoriesCdiscount);
                    $this->container->get('doctrine')->getManager()->persist($cat);
                    $this->container->get('doctrine')->getManager()->flush();
                    echo "CATEGORIE ".(string)$product->cat->merchantProductCategoryPath . "\n";
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
            }else{
                $metasdata = current($currentProduct)->meta_data;
                $retailer = [];
                $retailers = [];
                $metadata = [];
                $retailers = array_search('_product_retailers', array_column($metasdata, 'key'));
                if (!$retailers){
                    $retailer[] = [
                        'id' => $this->retailersNum,
                        'product_price' => (string)$product->price->buynow,
                        'product_location' => NULL,
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
                echo $i . ' ' . (string)$product->text->name . "\n";
                exit;
            }

            echo $i . ' ' . (string)$product->text->name . "\n";
            $i++;
        }

        echo $i . "\n";
        echo 'ok';
    }

}
