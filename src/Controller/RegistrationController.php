<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\ThirdParty\Abcp\AbcpApi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, AbcpApi $abcpApi): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $abcpResponse = $abcpApi->userProcessor->registerUser($user, $form);
            # todo обработать различные ответы от ABCP (например, когда он сообщает, что введенный номер телефона уже зарегистрирован)
            $user->setAbcpUserCode($abcpResponse->toArray(false)['userCode']);
            $this->registerUser($user, $userPasswordHasher, $form, $entityManager);
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    private function registerUser($user, $userPasswordHasher, $form, $entityManager)
    {
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            )
        );

        $cart = new Cart();
        $user->setCart($cart);

        $entityManager->persist($user);
        $entityManager->flush();
    }
}
