<?php

namespace App\Controller\ThirdParty\Google;

use App\Repository\GoogleAccessTokenRepository;
use App\Service\DataMapping;
use Google\Client;
use Google\Service\Gmail;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Symfony\Component\HttpKernel\KernelInterface;

class EmailSender
{
    private Client $client;
    private string $senderEmail;

    public function __construct(private KernelInterface $kernel, DataMapping $dataMapping)
    {
        $credentialsFilePath = $this->kernel->getProjectDir().'/config/secrets/'.
            $dataMapping->getValueByKey('google_services_credentials_filenames', 'gmail');

        $accessTokenFilepath = $this->kernel->getProjectDir().'/config/secrets/'.
            $dataMapping->getValueByKey('google_services_accesstoken_filenames', 'gmail');

        $this->senderEmail = $dataMapping->getValueByKey('igg_thirdparty_data', 'email');

        $this->client = new Client();
        $this->client->setAuthConfig($credentialsFilePath);
        $this->client->addScope(Gmail::GMAIL_SEND);

        if (file_exists($accessTokenFilepath)) {
            $accessToken = json_decode(file_get_contents($accessTokenFilepath), true);
            $this->client->setAccessToken($accessToken);
        }

        if (!$this->client->getAccessToken() || $this->client->isAccessTokenExpired()) {
            $accessToken = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            file_put_contents($accessTokenFilepath, json_encode($accessToken));
        }
    }

    public function sendEmailByIGG(string $recipient)
    {
        $service = new Google_Service_Gmail($this->client);

        $message = new Google_Service_Gmail_Message();
        $raw = "From: $this->senderEmail\r\nTo: $recipient\r\nSubject\r\nTest Subject\r\n\r\nHello, this is a test email!";
        $message->setRaw(base64_encode($raw));

        $service->users_messages->send('me', $message);
    }
}