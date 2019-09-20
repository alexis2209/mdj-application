<?php
namespace AppBundle\Command;

use Automattic\WooCommerce\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ImportToysnrusCommand
 * @package AppBundle\Command
 */
class ImportTestCommand extends Command
{
    /**
     * @var Container
     */
    private $container;

    /**
     * ImportToysnrusCommand constructor.
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
            ->setName('app:import:test')
            ->setDescription('Import XML File from toys n rus');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $apiWordpress = $this->container->get('app.services.wordpress.apiwordpress');
        $woocommerce = $this->container->get('app.services.wordpress.woocommerce');

        $decoAnnivMarket = 30185;

        $xml = $this->container->get('app.services.zanox.decoanniv')->getXml();
        $termObj = $this->container->get('ekino.wordpress.manager.term');
        $firstTerm = $termObj->findOneBy(['slug'=>'decoration-anniversaire']);
        $categParent = $firstTerm->getId();
        foreach ($xml->prod as $product)
        {
            $productCategorieMb = [];
            $productCategorieMb['select_caregory_fieldset']['select_product_caregory_lvl_1'] = $firstTerm->getSlug();
            $categoriesDecoAnniv = explode(' > ', (string)$product->cat->merchantProductCategoryPath);

            if (isset($categoriesDecoAnniv[0])){
                $categorie1 = $termObj->findOneBy(['name'=>$categoriesDecoAnniv[0]]);




                if (!$categorie1 && !is_null($categorie1)){
                    //$categorie1 = $apiWordpress->postCategorie(['name' => $categoriesDecoAnniv[0], 'parent'=>$categParent, 'taxonomy' => 'blurb_product_category']);
                    $cat = $woocommerce->postCategorie(['name' => $categoriesDecoAnniv[0], 'slug'=>urlencode($categoriesDecoAnniv[0]), 'parent'=>$categParent]);
                    $categorie1 = $termObj->find($cat->id);
                }



                $productCategorieMb['select_caregory_fieldset']['select_product_caregory_lvl_2'] = $categorie1->getSlug();




                if (count($categoriesDecoAnniv) > 1 && isset($categoriesDecoAnniv[count($categoriesDecoAnniv) - 1])){

                    $categ = $categoriesDecoAnniv[count($categoriesDecoAnniv) - 1];
                    $categorie = $termObj->findOneBy(['name' => $categ]);

                    if (!$categorie && !is_null($categorie)){

                        $cat = $woocommerce->postCategorie(['name' => $categ, 'slug'=>urlencode($categ), 'parent'=>$categorie1->getId()]);
                        //$categorie = $apiWordpress->postCategorie(['name' => $categ, 'parent'=>$categorie1->getId(), 'taxonomy' => 'blurb_product_category']);
                        $categorie = $termObj->find($cat->id);
                    }


                    $productCategorieMb['select_caregory_fieldset']['select_product_caregory_lvl_3'] = $categorie->getSlug();
                }
            }

            $productMb = array (
                'images' => (string)$product->uri->awThumb.','.(string)$product->uri->awImage,
                'brand_name' => 'Décoration anniversaire',
                'features' =>
                    array (),
                'markets' =>
                    array (
                        1 =>
                            array (
                                'market' => $decoAnnivMarket,
                                'price' => (string)$product->price->buynow,
                                'warranty' => '',
                                'link' => (string)$product->uri->awTrack,
                            ),
                    ),
                'enbl_review' => true,
                'rating_properties' =>
                    array (
                        'rating_property_1' => '',
                        'rating_property_2' => '',
                        'rating_property_3' => '',
                        'rating_property_4' => '',
                        'rating_property_5' => '',
                    ),
            );

            $seoMb = array (
                'title' => (string)$product->text->name,
                'keywords' => '',
                'description' => '',
                'img' => '',
                'img_alt' => '',
                'type' => 'blurb_product',
                'enbl_seo_settings' => false,
            );

            $meta = [
                'product_category_mb' => serialize($productCategorieMb),
                'product_mb' => serialize($productMb),
                'seo_mb' => serialize($seoMb),
                'product_mb_4278_ean' => (string)$product->ean
            ];


            $data['title'] = (string)$product->text->name;
            $data['content'] = (string)$product->text->desc;
            $data['status'] = 'publish';
            $data['comment_status'] = 'open';

            $data['meta'] = $meta;

            $testPost = $this->container->get('ekino.wordpress.manager.post_meta')->findOneBy(['key'=>'product_mb_4278_ean', 'value'=>(string)$product->ean]);

            if (!$testPost) {
                $apiWordpress->postPosts($data);
                echo (string)$product->text->name . " ajoute \n";
            }else{
                unset($data['meta']['product_mb_4278_ean']);
                //var_dump($testPost->getPost()->getId());exit;
                $apiWordpress->putPosts($testPost->getPost()->getId(), $data);
                echo (string)$product->text->name . " update \n";
            }

        }
    }
}
