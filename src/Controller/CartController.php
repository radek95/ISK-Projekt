<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use App\Entity\Product;
use App\Form\ProductType;
use App\Form\UserType;
use App\Repository\CartRepository;
use App\Repository\UserRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    private Security $security;
    private EntityManagerInterface $em;
    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->em = $em;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Route('/product/{product}/add-to-cart/{quantity}', name: 'app_product_add_to_cart')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function addToCart(Product $product, int $quantity, Request $request): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (null !== $user) {
            if (null === $user->getCart()) {
                $cart = new Cart();
                $user->setCart($cart);
            } else {
                $cart = $user->getCart();
            }
            $cart->setUserId($user);
            $prev_products = $cart->getProducts();
            if ($cart->getProducts()[$product->getTitle()] ?? null) {
                $prev_quantity = $cart->getProducts()[$product->getTitle()];
                $cart->setProducts(array_merge($prev_products, [$product->getTitle() => ++$prev_quantity]));
            } else {
                $cart->setProducts(array_merge($prev_products, [$product->getTitle() => $quantity]));
            }
            $this->em->persist($user);
            $this->em->persist($cart);
            $this->em->flush();

            $this->addFlash('success', 'Product added to cart');
        } else {
            $this->addFlash('failure', 'You must be logged in to add products to cart!');
        }

        $route = $request->headers->get('referer');
        return $this->redirect($route);
    }

    #[Route('/product/cart', name: 'app_cart' )]
    #[IsGranted('IS_AUTHENTICATED')]
    public function cart(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if (null !== $user->getCart()) {
            $products = $user->getCart()->getProducts();
            if (count($products) == 0) {
                $this->addFlash('failure', 'Nie masz zadnych produktow w koszyku');
            }
        } else {
            return $this->redirectToRoute('app_product');
        }

        return $this->render(
            'product/cart.html.twig', [
                'products' => $products,
        ]);
    }

    #[Route('/product/cart/delete/{id}', name: 'app_delete_product', requirements: ['product' => '\d+'])]
    #[IsGranted('IS_AUTHENTICATED')]
    public function deleteFromCart(int $id): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $cart = $user->getCart();
        $products = $cart->getProducts();
        $keys = array_keys($products);
        unset($products[$keys[$id]]);

        if (count($products) == 0) {
            $this->addFlash('failure', 'You don\'t have any products in cart!');
        }

        $cart->setProducts($products);
        $this->em->persist($cart);
        $this->em->flush();

        return $this->render(
            'product/cart.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/product/cart/change-quantity/{id}/{quantity}', name: 'app_product_change_quantity')]
    public function changeQuantity(int $id, int $quantity): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $cart = $user->getCart();
        $products = $cart->getProducts();
        $keys = array_keys($products);
        $current_product = $keys[$id];

        $prev_quantity = $cart->getProducts()[$current_product];
        if ($quantity == -1) {
            if ($prev_quantity == 1) {
                unset($products[$keys[$id]]);
                $cart->setProducts($products);
            } else {
                $cart->setProducts(array_merge($products, [$current_product => --$prev_quantity]));
            }
        } elseif ($quantity == 1) {
            $cart->setProducts(array_merge($products, [$current_product => ++$prev_quantity]));
        }
        $this->em->persist($cart);
        $this->em->flush();

        return $this->redirectToRoute('app_cart');
    }
    
 }
