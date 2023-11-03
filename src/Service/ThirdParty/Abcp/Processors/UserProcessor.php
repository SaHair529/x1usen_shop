<?php

namespace App\Service\ThirdParty\Abcp\Processors;

use App\Entity\User;
use App\Service\ThirdParty\Abcp\Actions\UserActions;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class UserProcessor
{
    public function __construct(private UserActions $userActions){}

    /**
     * Регистрация нового пользователя в ABCP и его активация
     * @param User $user
     * @param FormInterface $form
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function registerUser(User $user, FormInterface $form): ResponseInterface
    {
        $userFullnameArray = explode(' ', $user->getName());

        $registerUserRequestData = [
            'name' => $userFullnameArray[0],
            'password' => $form->get('plainPassword')->getData(),
            'mobile' => $user->getPhone()
        ];

        if (isset($userFullnameArray[1]))
            $registerUserRequestData['surname'] = $userFullnameArray[1];
        if (isset($userFullnameArray[2]))
            $registerUserRequestData['secondName'] = $userFullnameArray[2];

        return $this->userActions->new($registerUserRequestData);
    }
}