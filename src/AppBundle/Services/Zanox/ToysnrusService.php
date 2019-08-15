<?php
namespace AppBundle\Services\Zanox;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ActorService
 * @package AppBundle\Service
 */
class ToysnrusService
{

    private $_url = 'http://productdata.zanox.com/exportservice/v1/rest/18521457C349115923.xml?ticket=87E891472561713FFE38A366542E9240AF798C29AB1D3C40B2DCF2722693C042&productIndustryId=1&gZipCompress=null';
    private $_file = "/tmp/zanox_toysnrus.xml";

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function getXml(){
        file_put_contents($this->_file, fopen($this->_url, 'r'));
        if (file_exists($this->_file)) {
            $xml = simplexml_load_file($this->_file);
            return $xml;
        } else {

            exit('Echec lors de l\'ouverture du fichier : '.$this->_file);
        }
    }


}
