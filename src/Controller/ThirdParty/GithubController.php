<?php

namespace App\Controller\ThirdParty;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/github')]
class GithubController extends AbstractController
{
    /**
     * Обработка хуков от Github после пуша пул-реквестов для обновления файлов на сервере
     */
    #[Route('/pull_repo', name: 'github_pull_repo')]
    public function updateFilesAfterPullRequest(KernelInterface $kernel): JsonResponse
    {
        $commandFilePath = $kernel->getProjectDir().'/pull_github_repo.sh';
        exec('bash '.$commandFilePath, $out, $code);

        return new JsonResponse([
            'out' => $out,
            'command_execute_code' => $code
        ]);
    }
}