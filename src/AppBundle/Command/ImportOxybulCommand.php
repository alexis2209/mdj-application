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
        $this->attributeBrand = 5;
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
            $categoriesOxybul = explode(' > ', (string)$product->cat->merchantProductCategoryPath);
            array_pop($categoriesOxybul);
            $categorie = false;
            foreach ($categoriesOxybul as $key=>$val){

                $tab = [
                    'name' => $val,
                    'slug'=>urlencode($val)
                ];
                if (isset($categoriesOxybul[$key - 1]) && $categorie){
                    $tab['parent'] = $categorie->id;
                }
                $categorie = $woocommerce->getCategorie(['slug' => urlencode($val)]);
                if (!$categorie){
                    $categorie = $woocommerce->postCategorie($tab);
                }else{
                    $categorie = $categorie[0];
                }

                $categorieId = $categorie->id;
            }

            $brand = (string)$product->brand->brandName;
            $age = (string)$product->custom1;


            $currentProduct = $woocommerce->getProducts(['sku' => (string)$product->ean]);

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
                ]
            ];


            $data = [];
            $data['images'] = $images;
            $data['categories'] = $categories;
            $data['name'] = (string)$product->text->name;
            $data['type'] = 'external';
            $data['description'] = (string)$product->text->desc;
            $data['short_description'] = (string)$product->text->productShortDescription;
            $data['sku'] = (string)$product->ean;
            $data['regular_price'] = (string)$product->price->buynow;
            $data['sale_price'] = (string)$product->price->buynow;
            $data['external_url'] = (string)$product->uri->awTrack;
            $data['attributes'] = [
                ['id'=>$this->attributeBrand, 'name'=>'Brand','visible' => true, 'options' => [$brand]],
                ['id'=>$this->attributeAge, 'name'=>'Age','visible' => true, 'options' => [$age]],
            ];



            if (!$currentProduct) {
                $woocommerce->postProduct($data);
            }else{
                unset($data['images']);
                $woocommerce->putProduct(current($currentProduct)->id, $data);
            }
            echo $i . ' ' . (string)$product->text->name . "\n";
            $i++;
        }


        echo 'ok';
    }

}
