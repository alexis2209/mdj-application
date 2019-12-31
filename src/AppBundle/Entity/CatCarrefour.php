<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Coach
 *
 * @ORM\Table(name="cat_carrefour")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\CatCarrefourRepository")
 */
class CatCarrefour
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @ORM\Column(name="libelle", type="string", length=255, nullable=true)
     */
    protected $libelle;

    /**
     * @ORM\Column(name="wp_categorie", type="integer", nullable=true)
     */
    protected $wpCategorie;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * @param mixed $libelle
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;
    }

    /**
     * @return mixed
     */
    public function getWpCategorie()
    {
        return $this->wpCategorie;
    }

    /**
     * @param mixed $wpCategorie
     */
    public function setWpCategorie($wpCategorie)
    {
        $this->wpCategorie = $wpCategorie;
    }



}

