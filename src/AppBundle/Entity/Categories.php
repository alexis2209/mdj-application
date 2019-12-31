<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Coach
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\CategoriesRepository")
 */
class Categories
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="affiliate", type="integer", nullable=true)
     */
    protected $affiliate;


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

    /**
     * @return mixed
     */
    public function getAffiliate()
    {
        return $this->affiliate;
    }

    /**
     * @param mixed $affiliate
     */
    public function setAffiliate($affiliate)
    {
        $this->affiliate = $affiliate;
    }





}

