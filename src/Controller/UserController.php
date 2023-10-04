<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    private Security $security;
    private EntityManagerInterface $em;
    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->em = $em;
    }
    #[Route('/user/{user}/role', name: 'app_user_role')]
    #[IsGranted('ROLE_ADMIN')]
    public function editRoles(User $user, Request $request, UserRepository $users): Response 
    {
        
        $form = $this->createForm(UserType::class, $user);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
           $user = $form->getData();
           $user->setEdited(new DateTime());
           $currentUser = $this->security->getUser();
           $user->setEditedBy($currentUser->getId());
           $users -> save($user, true);
           $this -> addFlash('success', 'User role have been updated');

           
           return $this->redirectToRoute('app_user');
        }
        return $this->render(
            'user/edit_user.html.twig', [
                'roleForm' => $form,
                'user' => $user
            ]);

    }

    #[Route('/user', name: 'app_user')]
    public function index(UserRepository $users, ): Response
    {
        
        return $this->render('user/user.html.twig', [
            'users' => $users->findAll(),
            
        ]);
    }
}