<?php
/**
 * Created by PhpStorm.
 * User: florian
 * Date: 28/09/17
 * Time: 14:09
 */

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Ekino\WordpressBundle\Repository\TermRepository;

class CatCdiscountRepository extends EntityRepository
{

    /**
     *
     * @return \Doctrine\ORM\Query
     */
    public function getWpCategories()
    {




        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Ekino\WordpressBundle\Entity\Term', 'wp');
        $rsm->addFieldResult('wp', 'term_id', 'id');
        $rsm->addFieldResult('wp', 'name', 'libelle');

        $query = $this->getEntityManager()->createNativeQuery("
            SELECT wp.term_id, wp.name
            FROM wp_terms wp
            INNER JOIN wp_term_taxonomy wt ON wt.term_id = wp.term_id
            WHERE taxonomy = '?'
        ", $rsm);
        $query->setParameter(1, 'product_cat');
        $cats = $query->execute();


        var_dump($cats);exit;
    }

}