<?php

namespace App\Controller\ThirdParty;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/github')]
class GithubController extends AbstractController
{
    /**
     * Обработка хуков от Github после пуша пул-реквестов для обновления файлов на сервере
     */
    #[Route('/pull_repo', name: 'github_pull_repo')]
    public function updateFilesAfterPullRequest(KernelInterface $kernel): Response
    {
        $commandFilePath = $kernel->getProjectDir().'/pull_github_repo.sh';
        exec('bash '.$commandFilePath, $out, $code);

        return new Response();
    }
}