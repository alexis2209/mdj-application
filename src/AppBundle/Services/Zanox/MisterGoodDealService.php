<?php
namespace AppBundle\Services\Zanox;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ActorService
 * @package AppBundle\Service
 */
class MisterGoodDealService
{

    private $_fileC = "/tmp/zanox_mistergooddeal.xml.gz";
    private $_file = "/tmp/zanox__mistergooddeal.xml";

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function getXml($files, $key){
        $this->_fileC = "/tmp/zanox_mistergooddeal".$key.".xml.gz";
        $this->_file = "/tmp/zanox_mistergooddeal".$key.".xml";
        file_put_contents($this->_fileC, fopen($files, 'r'));

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
