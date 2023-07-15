<?php

namespace App\Service\ThirdParty\Google;

use App\Repository\GoogleAccessTokenRepository;
use App\Service\DataMapping;
use Google\Client;
use Google\Exception;
use Google\Service\Gmail;
use phpseclib3\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\KernelInterface;

class OAuthControlService
{
    private const CREDENTIALS_FILENAME = 'gmail_credentials.json';
    private $credentialsFilePath;
    private $accessTokenFilepath;

    public function __construct(private KernelInterface $kernel, DataMapping $dataMapping)
    {
        $this->credentialsFilePath = $this->kernel->getProjectDir().'/config/secrets/'.
            $dataMapping->getValueByKey('google_services_credentials_filenames', 'gmail');
        $this->accessTokenFilepath = $this->kernel->getProjectDir().'/config/secrets/'.
            $dataMapping->getValueByKey('google_services_accesstoken_filenames', 'gmail');
    }

    /**
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function saveAccessTokenByAuthCode(string $authCode)
    {
        if (!file_exists($this->credentialsFilePath))
            throw new FileNotFoundException('Credentials file not found ('.$this->credentialsFilePath.')');

        $googleClient = new Client();
        $googleClient->setAuthConfig($this->credentialsFilePath);
        $googleClient->addScope(Gmail::GMAIL_SEND);

        $accessToken = $googleClient->fetchAccessTokenWithAuthCode($authCode);
        if (isset($accessToken['error']))
            throw new Exception($accessToken['error']);

        file_put_contents($this->accessTokenFilepath, json_encode($accessToken));
    }
}