<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CatCdiscount;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class CategoriesController extends Controller
{
    /**
     * @Route("/cat-cdiscount", name="admin_categories_cdiscount")
     */
    public function indexAction(Request $request)
    {

        $catsCdiscount = $this->container->get('doctrine')->getManager()->getRepository(CatCdiscount::class)->findBy(['wpCategorie'=>NULL], NULL, 10);

        $termsTaxonomyObj = $this->container->get('ekino.wordpress.manager.term_taxonomy')->findBy(['taxonomy'=>'product_cat']);
        $catsWp = [];
        foreach ($termsTaxonomyObj as $termTaxonomyObj){
            $term = $this->container->get('ekino.wordpress.manager.term')->findOneBy(['id' => $termTaxonomyObj->getTerm()->getId()]);
            $catsWp[$term->getId()] = [
                'name' => $term->getName(),
                'parent' => ($termTaxonomyObj->getParent()->getId() != 0)?$termTaxonomyObj->getParent()->getTerm()->getName() . ' - ':''];
        }



        // replace this example code with whatever you need
        return $this->render('@App/Categories/cdiscount.html.twig', [
            'catsCdiscount' => $catsCdiscount,
            'catsWp' => $catsWp,
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }
}
