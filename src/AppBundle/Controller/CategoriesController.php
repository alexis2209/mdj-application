<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CatCdiscount;
use AppBundle\Entity\Categories;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/", name="admin_")
 */
class CategoriesController extends Controller
{
    /**
     * @Route("/categories", name="categories_index")
     */
    public function indexAction(Request $request)
    {
        $categories = $this->container->get('doctrine')->getManager()->getRepository(Categories::class)->findBy(['wpCategorie'=>NULL], NULL, 10);

        $termsTaxonomyObj = $this->container->get('ekino.wordpress.manager.term_taxonomy')->findBy(['taxonomy'=>'product_cat']);
        $catsWp = [];
        foreach ($termsTaxonomyObj as $termTaxonomyObj){
            $term = $this->container->get('ekino.wordpress.manager.term')->findOneBy(['id' => $termTaxonomyObj->getTerm()->getId()]);
            $catsWp[$term->getId()] = [
                'name' => $term->getName(),
                'parent' => ($termTaxonomyObj->getParent()->getId() != 0)?$termTaxonomyObj->getParent()->getTerm()->getName() . ' - ':''];
        }



        // replace this example code with whatever you need
        return $this->render('@App/Categories/index.html.twig', [
            'categories' => $categories,
            'catsWp' => $catsWp,
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }


    /**
     * @Route("/valid-categories/{source}/{dest}", name="categories_validation", options={"expose":true})
     * @param $source
     * @param $dest
     * @return JsonResponse
     * @throws \Exception
     */
    public function validCategoriesAction($source, $dest)
    {
        $response = ['code' => 'KO'];
        $categorie = $this->container->get('doctrine')->getManager()->getRepository(Categories::class)->find($source);
        if ($categorie){
            try{
                $categorie->setWpCategorie($dest);
                $this->container->get('doctrine')->getManager()->persist($categorie);
                $this->container->get('doctrine')->getManager()->flush();
                $response = ['code' => 'OK'];
                $response = ['message' => 'Catégorie ajouté avec succès !'];
            }catch (\Exception $e){
                $response = ['message' => $e->getMessage()];
            }
        }else{
            $response = ['message' => 'categorie introuvable !'];
        }

        return new JsonResponse($response);
    }
}
