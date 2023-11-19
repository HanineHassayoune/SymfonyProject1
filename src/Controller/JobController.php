<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Job;
use App\Entity\Image;
use App\Entity\Candidature;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;

class JobController extends AbstractController
{
    #[Route('/job', name: 'app_job')]
    public function index(): Response
    {   /* $entityManager c'est une variable de type getDoctrine */
        $entityManager=$this->getDoctrine()->getManager(); 
        $job=new Job();
        $job->setType('Développeur');       
        $job->setCompany('SOTORIPOP');
        $job->setDescription('Génie logiciel');
        $job->setExpiresAt(new \DateTimeImmutable());
        $job->setEmail('hassayounehanine@gmail.com');

        $image = new Image();
        $image->setUrl('https://cdn.pixabay.com/photo/2015/10/30/10/03/gold-1013618_960_720.jpg');
        $image->setAlt('job de reves');
        $job->setImage($image);
        
        //Ajout de candidats
        $candidature1=new Candidature();
        $candidature1->setCandidat("Rhiem");
        $candidature1->setContenu("formation J2EE");
        $candidature1->setDate(new \DateTime());
        $candidature2=new Candidature();
        $candidature2->setCandidat("Salima");
        $candidature2->setContenu("formation Symfony");
        $candidature2->setDate(new \DateTime());
        //ajouter le job pour ces 2 candidats
        $candidature1->setJob($job);
        $candidature2->setJob($job);

        $entityManager->persist($candidature1);
        $entityManager->persist($candidature2);

        /*persist() simulation d'insertion image + job */
        $entityManager->persist($job->getImage());
        $entityManager->persist($job);
        /*flush() confirmer la requete d'insertion*/
        $entityManager->flush();
        return $this->render('job/index.html.twig', [
            'id' => $job->getId(),
        ]);
    }


        /**
        * @Route("/job/{id}", name="job_show")
        */
        public function show($id)
        {
        /*select from job where id = */
        $job = $this->getDoctrine()->getRepository(Job::class)->find($id);
        $em = $this->getDoctrine()->getManager();
        $listCandidatures = $em->getRepository(Candidature::class)->findBy(['Job'=>$job]);
        if (!$job) {
        throw $this->createNotFoundException('No job found for id '.$id);
        }
        /*retourner tout un objet $job */
        return $this->render('job/show.html.twig', [
        'job' =>$job,
        'listCandidatures'=>$listCandidatures
    ]);
    }

     /**
        * @Route("/Ajouter", name="Ajouter")
        */
        public function ajouter(Request $request)
        {
        $candidat = new Candidature();
        $fb = $this->createFormBuilder($candidat)
            ->add('candidat', TextType:: class)
            ->add('contenu', TextType:: class, array("label" => "Contenu"))
            ->add('date', DateType:: class)
            ->add('job', EntityType:: class, [
                'class' => Job:: class,
                'choice_label' => 'type', ])

            ->add('Valider', SubmitType:: class);
         // générer le formulaire à partir du FormBuilder
        $form = $fb->getForm();
        //injection dans la base de donnees
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em=$this->getDoctrine()->getManager();
            $em->persist($candidat);
            $em->flush();
            return $this->redirectToRoute('Accueil');
        }

        // Utiliser la methode createView() pour que l'objet soit exploitable par la vue
        return $this->render('job/ajouter.html.twig',
        ['f' => $form->createView()] );
        }

}
