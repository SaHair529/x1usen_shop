<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserSettingsFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/me')]
class UserController extends AbstractController
{
    #[Route('/settings', name: 'user_settings')]
    #[IsGranted('ROLE_USER')]
    public function userSettings(Request $req, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager)
    {
        /** @var User $user */
        $user = $this->getUser();

        $userSettingsForm = $this->createForm(UserSettingsFormType::class);
        $userSettingsForm->handleRequest($req);

        if ($userSettingsForm->isSubmitted()) {
            if (!$userPasswordHasher->isPasswordValid($user, $userSettingsForm->get('old_password')->getData())) {
                $this->addFlash('danger', 'Неверный старый пароль');
                return $this->render('user/settings.html.twig', [
                    'form' => $userSettingsForm
                ]);
            }
            elseif ($userSettingsForm->get('new_password')->getData() === $userSettingsForm->get('old_password')->getData()) {
                $this->addFlash('danger', 'Новый пароль совпадает с вашим текущим паролем');
                return $this->render('user/settings.html.twig', [
                    'form' => $userSettingsForm
                ]);
            }
            $this->addFlash('success', 'Пароль успешно обновлён');
            $this->updateUser($user, $userPasswordHasher, $userSettingsForm, $entityManager);
        }

        return $this->render('user/settings.html.twig', [
            'form' => $userSettingsForm,
            'username' => $user->getUsername()
        ]);
    }

    private function updateUser($user, $userPasswordHasher, $form, $entityManager)
    {
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $form->get('new_password')->getData()
            )
        );

        $entityManager->persist($user);
        $entityManager->flush();
    }
}