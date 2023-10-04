<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use App\Form\UserType;
use App\Entity\Product;
use App\Form\ProductType;
use App\Entity\UserProfile;
use Doctrine\ORM\EntityManager;
use App\Repository\CartRepository;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProductController extends AbstractController
{
    private Security $security;
    private EntityManagerInterface $em;
    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->em = $em;
    }

    #[Route('/product', name: 'app_product')]
    public function index(ProductRepository $products, Request $request, PaginatorInterface $paginator, ManagerRegistry $managerRegistry): Response
    {
        $entityManager = $managerRegistry->getManager();
        $query = $entityManager->getRepository(Product::class)->createQueryBuilder('p')
        ->orderBy('p.id', 'ASC')
        ->getQuery();

    $pagination = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        2
    );
        return $this->render('product/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/product/{product}', name: 'app_product_show', requirements: ['product' => '\d+'])]
    public function showOne(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product/add', name: 'app_product_add', priority: 2)]
    #[IsGranted('ROLE_WORKER')]
    public function add(Request $request, ProductRepository $products, SluggerInterface $slugger ): Response 
    {
        $form = $this->createForm(ProductType::class, new Product());
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
           $product = $form->getData();
           $productImageFile = $form->get('productImage')->getData();
           
           if($productImageFile){
            
                $originalFileName = pathinfo(
                    $productImageFile->getClientOriginalName(),
                    PATHINFO_FILENAME
                );
                $safeFilename = $slugger->slug($originalFileName);
                $newFileName = $safeFilename.'-'.uniqid().'.'. $productImageFile->guessExtension();

                try {
                    $productImageFile->move(
                        $this->getParameter('products_directory'),
                        $newFileName
                    );
           } catch (FileException $e){
           }

           $product -> setImage($newFileName);
        } 
           $products -> save($product, true);

           
           $this -> addFlash('success', 'Product have been added');

           
           return $this->redirectToRoute('app_product');
        }
        return $this->render(
            'product/add.html.twig', [
                'form' => $form
            ]);

    }
    
    #[Route('/product/{product}/edit', name: 'app_product_edit')]
    #[IsGranted('ROLE_WORKER')]
    public function edit(Product $product, Request $request, ProductRepository $products, SluggerInterface $slugger): Response 
    {
        $form = $this->createForm(ProductType::class, $product);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
           $product = $form->getData();
           $productImageFile = $form->get('productImage')->getData();
           
           if($productImageFile){
            
                $originalFileName = pathinfo(
                    $productImageFile->getClientOriginalName(),
                    PATHINFO_FILENAME
                );
                $safeFilename = $slugger->slug($originalFileName);
                $newFileName = $safeFilename.'-'.uniqid().'.'. $productImageFile->guessExtension();

                try {
                    $productImageFile->move(
                        $this->getParameter('products_directory'),
                        $newFileName
                    );
           } catch (FileException $e){
           }

           $product -> setImage($newFileName);
        } 
           $products -> save($product, true);

           
           $this -> addFlash('success', 'Product have been updated');

           
           return $this->redirectToRoute('app_product');
        }
        return $this->render(
            'product/edit.html.twig', [
                'form' => $form,
                'product' => $product
            ]);

    }
    
 }
