<?php
namespace AppBundle\Command;

use Automattic\WooCommerce\Client;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ImportOxybulCommand
 * @package AppBundle\Command
 */
class ImportOxybulCommand extends Command
{
    /**
     * @var Container
     */
    private $container;

    /**
     * ImportPlaymobileCommand constructor.
     * @param Container $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->attributeAge = 6;
        $this->attributeBrand = 7;
        //$this->retailersNum = 28146;
        $this->retailersNum = 28319;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:import:oxybul')
            ->setDescription('Import XML File from toys n rus');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $woocommerce = $this->container->get('app.services.wordpress.woocommerce');
        $xml = $this->container->get('app.services.zanox.oxybul')->getXml();
        $i = 1;
        foreach ($xml->prod as $product)
        {
            $currentProduct = $woocommerce->getProducts(['sku' => (string)$product->ean]);

            $data = [];
            $data['type'] = 'simple';
            $data['regular_price'] = (string)$product->price->productPriceOld;
            $data['sale_price'] = (string)$product->price->buynow;

            if (!$currentProduct) {
                $categoriesOxybul = explode(' > ', (string)$product->cat->merchantProductCategoryPath);
                array_pop($categoriesOxybul);
                $categorie = false;

                foreach ($categoriesOxybul as $key=>$val){

                    $tab = [
                        'name' => $val,
                    ];
                    if (isset($categoriesOxybul[$key - 1]) && $categorie){
                        $tab['parent'] = $categorie->id;
                    }
                    $categorie = $woocommerce->getCategorie(['slug' => $val]);
                    if (!$categorie){
                        $categorie = $woocommerce->postCategorie($tab);
                    }else{
                        $categorie = $categorie[0];
                    }

                    $categorieId = $categorie->id;
                }

                $brand = (string)$product->brand->brandName;
                $age = (string)$product->custom1;




                $images = [
                    [
                        'src' => (string)$product->uri->mImage
                    ],
                    [
                        'src' => (string)$product->uri->largeImage
                    ]
                ];

                $categories = [
                    [
                        'id' => $categorieId
                    ],
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
                    'key' => 'fifu_image_url',
                    'value' => $retailer
                ];
                $metadata[] = [
                    'key' => 'fifu_image_alt',
                    'value' => (string)$product->text->name
                ];

                //$data['images'] = $images;
                $data['categories'] = $categories;
                $data['name'] = (string)$product->text->name;
                $data['type'] = 'simple';
                $data['description'] = (string)$product->text->desc;
                $data['short_description'] = (string)$product->text->productShortDescription;
                $data['sku'] = (string)$product->ean;
                $data['meta_data'] = $metadata;
                $data['attributes'] = [
                    ['name'=>'Marque','visible' => true, 'options' => [$brand]],
                    ['name'=>'Age','visible' => true, 'options' => [$age]],
                ];
                $woocommerce->postProduct($data);
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

                /*var_dump($data);
                if ($i == 2){
                    exit;
                }*/

                $woocommerce->putProduct(current($currentProduct)->id, $data);
            }

            echo $i . ' ' . (string)$product->text->name . "\n";
            $i++;
        }


        echo 'ok';
    }

}
