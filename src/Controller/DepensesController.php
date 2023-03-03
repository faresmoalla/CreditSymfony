<?php

namespace App\Controller;

use App\Entity\Depense;
use App\Form\DepenseType;
use App\Repository\DepenseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class DepensesController extends AbstractController
{
    #[Route("/afficherdepense",name :"afficherdepense")]

    public function Affiche(Request $request,DepenseRepository $repository,PaginatorInterface $paginator){
        $tabledepense=$repository->findAll();
        $tabledepense = $paginator->paginate(
            $tabledepense,
            $request->query->getInt('page', 1),
            2
        );
        return $this->render('depense/afficherDepense.html.twig'
            ,['tabledepense'=>$tabledepense
            ]);
    }
    #[Route("/ajouterdepense",name:"ajouterdepense")]

    public function ajouterdepense(EntityManagerInterface $em,Request $request ,DepenseRepository $depense){
        $depense= new Depense();
        $form= $this->createForm(DepenseType::class,$depense);
        $form->add('Ajouter',SubmitType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($depense);
            $em->flush();

            return $this->redirectToRoute("afficherdepense");
        }
        return $this->render("depense/ajouterdepense.html.twig",array("form"=>$form->createView()));

    }
    #[Route("/supprimerdepense/{id}",name:"supprimerdepense")]

    public function delete($id,EntityManagerInterface $em ,DepenseRepository $repository){
        $rec=$repository->find($id);
        $em->remove($rec);
        $em->flush();

        return $this->redirectToRoute('afficherdepense');
    }



    #[Route("/{id}/modifierdepense'", name:"modifierdepense'")]

    public function edit(Request $request, Depense $depense): Response
    {
        $form = $this->createForm(DepenseType::class, $depense);
        $form->add('Confirmer',SubmitType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('afficherdepense');
        }
        return $this->render('depenses/Modiferdepense.html.twig', [
            'depensemodif' => $depense,
            'form' => $form->createView(),
        ]);
    }
}
