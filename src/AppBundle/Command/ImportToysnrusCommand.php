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
class ImportToysnrusCommand extends Command
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
            ->setName('app:import:toysnrus')
            ->setDescription('Import XML File from toys n rus');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $woocommerce = $this->container->get('app.services.wordpress.woocommerce');
        $xml = $this->container->get('app.services.zanox.toysnrus')->getXml();
        foreach ($xml as $product)
        {
            $currentProduct = $woocommerce->getProducts(['sku' => (string)$product->ean]);

            $images = [];
            $images[] = ['src' => (string)$product->mediumImage];
            $images[] = ['src' => (string)$product->largeImage];

            $data = [];
            $data['name'] = (string)$product->name;
            $data['type'] = 'external';
            $data['description'] = (string)$product->longDescription;
            $data['short_description'] = (string)$product->description;
            $data['sku'] = (string)$product->ean;
            $data['regular_price'] = (string)$product->price;
            $data['sale_price'] = (string)$product->price;
            $data['external_url'] = (string)$product->deepLink;
            //$data['images'] = $images;
            //var_dump($data);exit;

            echo (string)$product->name . "\n";

            if (!$currentProduct) {
                $woocommerce->postProduct($data);
            }else{
                $woocommerce->putProduct(current($currentProduct)->id, $data);
            }


            //echo $product->name . "\n";

        }

        //
        //$products = $woocommerce->getProducts();

        //var_dump($products);exit;


        echo 'ok';
    }
}
