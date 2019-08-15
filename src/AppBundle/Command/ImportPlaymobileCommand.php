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
 * Class ImportToysnrusCommand
 * @package AppBundle\Command
 */
class ImportPlaymobileCommand extends Command
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
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:import:playmobile')
            ->setDescription('Import XML File from toys n rus');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $woocommerce = $this->container->get('app.services.wordpress.woocommerce');
        $xml = $this->container->get('app.services.zanox.playmobile')->getXml();
        $categParent = 22;
        foreach ($xml->prod as $product)
        {
            $categoriesPlaymobile = explode('/', (string)$product->cat->mCat);
            $categ = $categoriesPlaymobile[count($categoriesPlaymobile) - 1];
            $categorie = $woocommerce->getCategorie(['slug' => urlencode($categ)]);


            if (!$categorie){
                $categorie = $woocommerce->postCategorie(['name' => $categ, 'slug'=>urlencode($categ), 'parent'=>$categParent]);
            }

            var_dump($categorie);


            $currentProduct = $woocommerce->getProducts(['sku' => (string)$product->ean]);

            $images = [
                [
                    'src' => (string)substr($product->uri->mImage, 0, strpos($product->uri->mImage, "?")).".jpg"
                ],
                [
                    'src' => (string)substr($product->uri->mImage, 0, strpos($product->uri->mImage, "?")).".jpg"
                ]
            ];

            if (is_array($categorie) && isset($categorie[0])){
                $categorieId = $categorie[0]->id;
            }else{
                $categorieId = $categorie->id;
            }

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
            $data['short_description'] = (string)$product->text->desc;
            $data['sku'] = (string)$product->ean;
            $data['regular_price'] = (string)$product->price->buynow;
            $data['sale_price'] = (string)$product->price->buynow;
            $data['external_url'] = (string)$product->uri->awTrack;

            echo (string)$product->text->name . "\n";

            if (!$currentProduct) {
                $woocommerce->postProduct($data);
            }else{
                $woocommerce->putProduct(current($currentProduct)->id, $data);
            }

        }


        echo 'ok';
    }

}
