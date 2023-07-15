<?php

namespace App\Controller\ThirdParty\Google;

use App\Service\ThirdParty\Google\OAuthControlService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/thirdparty/gmail')]
class GMailApiController extends AbstractController
{
    #[Route('/oauth/auth')]
    public function oauthAuth(Request $req, OAuthControlService $gmailOAuth): Response
    {
        if (!$req->query->has('code'))
            return new Response('Code wanted', Response::HTTP_BAD_REQUEST);

        try {
            $gmailOAuth->saveAccessTokenByAuthCode($req->query->get('code'));
            return new Response('Success');
        }
        catch (Exception $e) {
            return new Response($e->getMessage());
        }
    }
}