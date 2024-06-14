<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use \App\Repository\EtudiantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use App\Entity\Etudiant;
use App\Entity\UploadedFiles;
use App\Service\PdfGeneratorService;
use App\Service\UploadService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Security;



class StudentController extends AbstractController
{
  private $security;

  public function __construct(Security $security)
  {
      $this->security = $security;
  }
   
    /**
        * @IsGranted("ROLE_ADMIN")
        * @Route("/student/uploads", name="uploads_file", methods={"POST"})

     */

    public function upload( Request $request, UploadService $uploadService, EntityManagerInterface $entityManager ){
      $target_dir = $this->getParameter('kernel.project_dir') . '/public/uploads/';
      $files= $request->files->get("fileToUpload");

      foreach($files as $file){
       $filecontent= $uploadService->uploadFile($file,$target_dir );
       $fileUploaded= new UploadedFiles();
    
       $fileUploaded->setFileName($filecontent['filename']);
       $fileUploaded->setFilepath($filecontent['filepath']);
       $fileUploaded->setSize($filecontent['size']);
        
       $entityManager->persist($fileUploaded);
      }

      $entityManager->flush();
      return new Response('');

    }

    /**
     * @Route("/student/{page<\d+>}", name="app_student")
     */
    public function index(Request $request, EtudiantRepository $EtudiantRepository, int $page=1, $options= array() ): Response
    {
      if (!$this->security->getUser()) {
        return $this->redirectToRoute('app_login');
    }
        $checkAll = $request->get('checkAll', false);
        $sort= $request->query->get('sort', 'id');
        $sortDirection= $request ->query->get('sortDirection', 'ASC');
        $availableSortFields = ['id', 'nom', 'prenom', 'email', 'tel'];
        $sort = in_array($sort, $availableSortFields) ? $sort : 'id'; 
        $sortDirection = in_array($sortDirection, ['ASC', 'DESC']) ? $sortDirection : 'ASC'; 

       
        $queryBuilder = $EtudiantRepository->createQueryBuilder('e')
        ->orderBy('e.' . $sort, $sortDirection);
        $keyword = $request->query->get('keyword', '');
        if ($keyword) {
          $queryBuilder
              ->andWhere('e.nom LIKE :keyword')
              ->orWhere('e.prenom LIKE :keyword')
              ->setParameter('keyword', '%' . $keyword . '%');
      }

        $adapter = new DoctrineORMAdapter($queryBuilder, $options); 
        $pagerfanta = new Pagerfanta($adapter); 
        $pagerfanta->setMaxPerPage(10); 
        $pagerfanta->setCurrentPage($page); 
        $students = $pagerfanta->getCurrentPageResults(); 

        return $this->render('student/index.html.twig', [
            'students' => $students,
            'pager' => $pagerfanta,
            'sort' => $sort,
            'sortDirection' => $sortDirection,
            'availableSortFields' => $availableSortFields,
            'keyword' => $keyword,
            'checkAll' => $checkAll,
        ]);
    }

    /**
     *  @IsGranted("ROLE_ADMIN")
     * @Route("/student/new", name="new_student", methods={"GET", "POST"})
     * 
     */
    public function new(Request $request) {
        $student = new Etudiant();
      
        $form = $this->createFormBuilder($student)
          ->add('Nom', TextType::class, array('attr' => array('class' => 'form-control')))
          ->add('Prenom', TextType::class, array('attr' => array('class' => 'form-control')))
          ->add('Email', TextType::class, array('attr' => array('class' => 'form-control')))
          ->add('Tel', TextType::class, array('attr' => array('class' => 'form-control')))
          ->add('save', SubmitType::class, array(
            'label' => 'Ajouter',
            'attr' => array('class' => 'btn btn-primary mt-3')
          ))
          ->getForm();

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()) {
          $student = $form->getData();
          $entityManager = $this->getDoctrine()->getManager();
          $entityManager->persist($student);
          $entityManager->flush();
  
          return $this->redirectToRoute('app_student');
        }
       
  
        return $this->render('student/new.html.twig', array(
          'form' => $form->createView()
        ));
      }
    /**
     * @IsGranted("ROLE_ADMIN")
       * @Route("/student/delete/{id}", name="delete_student",methods={"DELETE"})
       * 
      */
      public function delete(Request $request,EtudiantRepository $EtudiantRepository, EntityManagerInterface $entityManager, $id):response{
        $student = $EtudiantRepository-> find($id);
        
        $entityManager -> remove($student);
        $entityManager->flush();  
        return new Response("", Response::HTTP_OK);
   
      }
      /**
       *  @IsGranted("ROLE_ADMIN")
     * @Route("/student/edit/{id}", name="edit_student", methods={"GET", "POST"})
     * 
     */
    public function edit(Request $request,EtudiantRepository $EtudiantRepository, $id) {
      $student = new Etudiant();
      $student = $EtudiantRepository-> find($id);

      $form = $this->createFormBuilder($student)
        ->add('Nom', TextType::class, array('attr' => array('class' => 'form-control')))
        ->add('Prenom', TextType::class, array('attr' => array('class' => 'form-control')))
        ->add('Email', TextType::class, array('attr' => array('class' => 'form-control')))
        ->add('Tel', TextType::class, array('attr' => array('class' => 'form-control')))
        ->add('save', SubmitType::class, array(
          'label' => 'sauvgarder',
          'attr' => array('class' => 'btn btn-primary mt-3')
        ))
        ->getForm();

      $form->handleRequest($request);

      if($form->isSubmitted() && $form->isValid()) {

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        return $this->redirectToRoute('app_student');
      }

      return $this->render('student/new.html.twig', array(
        'form' => $form->createView()
      ));
    }
    
     /**
     * @Route("/student/pdf/{id}", name="student_pdf")
     */
    public function generatePdf($id, PdfGeneratorService $pdfGenerator, EtudiantRepository $EtudiantRepository)
    {
       $student= new Etudiant();
       $student= $EtudiantRepository->find($id);


        $pdfContent = $pdfGenerator->generatePdf('student/pdf.html.twig', [
            'student' => $student,
        ]);

        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="student_' . $id . '.pdf"',
        ]);
    }

      
}
