<?php

namespace App\Controller;

use App\Entity\Credit;
use App\Form\CreditType;
use App\Repository\CreditRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class CreditController extends AbstractController
{
    #[Route("/affichercredit",name :"affichercredit")]

    public function Affiche(Request $request,CreditRepository $repository,PaginatorInterface $paginator){
        $tablecredits=$repository->findAll();
        $tablecredits = $paginator->paginate(
            $tablecredits,
            $request->query->getInt('page', 1),
            2
        );
        return $this->render('credit/affichercredit.html.twig'
            ,['tablecredits'=>$tablecredits
            ]);
    }
    #[Route("/ajoutercredit",name:"ajoutercredit")]

    public function ajoutercredit(EntityManagerInterface $em,Request $request ,CreditRepository $credit){
        $credit= new Credit();
        $form= $this->createForm(CreditType::class,$credit);
        $form->add('Ajouter',SubmitType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em->persist($credit);
            $em->flush();

            return $this->redirectToRoute("affichercredit");
        }
        return $this->render("credit/ajoutercredit.html.twig",array("form"=>$form->createView()));

    }
    #[Route("/supprimercredit/{id}",name:"supprimercredit")]

    public function delete($id,EntityManagerInterface $em ,CreditRepository $repository){
        $rec=$repository->find($id);
        $em->remove($rec);
        $em->flush();

        return $this->redirectToRoute('affichercredit');
    }

    #[Route("/envoyeremail/{id}",name:"envoyeremail")]

    public function envoyeremail($id,Request $request,CreditRepository $repository){
        $rec=$repository->find($id);




        return $this->redirectToRoute('affichercredit');
    }

    #[Route("/{id}/modifiercredit", name:"modifiercredit")]

    public function edit(Request $request, Credit $credit): Response
    {
        $form = $this->createForm(CreditType::class, $credit);
        $form->add('Confirmer',SubmitType::class);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            $this->getDoctrine()->getManager()->flush();


            return $this->redirectToRoute('affichercredit');
        }

        return $this->render('credit/Modifercredit.html.twig', [
            'creditmodif' => $credit,
            'form' => $form->createView(),
        ]);
    }
    #[Route("/pdf/{id}",name:"pdf", methods: ['GET'])]
    public function pdf($id,CreditRepository $repository): Response{
        $credit=$repository->find($id);
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('credit/pdf.html.twig', [
            'pdf' => $credit,

        ]);
        $dompdf->loadHtml($html);
        //  $dompdf->loadHtml('<h1>Hello, World!</h1>');

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();
        //  $dompdf->stream();
        // Output the generated PDF to Browser (force download)
        /* $dompdf->stream($reclamation->getType(), [
             "Attachment" => false
         ]);*/
        $pdfOutput = $dompdf->output();
        return new Response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="credits.pdf"'
        ]);

    }



}
