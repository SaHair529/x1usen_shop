<?php

namespace App\Service\ThirdParty\Google;

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

        $this->updateAccessTokenIfNeeded($accessTokenFilepath);
    }

    public function sendEmailByIGG(string $recipient)
    {
        $service = new Google_Service_Gmail($this->client);

        $message = new Google_Service_Gmail_Message();
        $raw = "From: $this->senderEmail\r\nTo: $recipient\r\nSubject\r\nTest Subject\r\n\r\nHello, this is a test email!";
        $message->setRaw(base64_encode($raw));

        $service->users_messages->send('me', $message);
    }

    public function updateAccessTokenIfNeeded(string $accessTokenFilepath)
    {
        if (file_exists($accessTokenFilepath)) {
            $accessToken = json_decode(file_get_contents($accessTokenFilepath), true);
            $this->client->setAccessToken($accessToken);
        }

        if (!$this->client->getAccessToken() || $this->client->isAccessTokenExpired()) {
            if ($this->client->getRefreshToken()) {
                $accessToken = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                file_put_contents($accessTokenFilepath, json_encode($accessToken));
            }
            else {
                $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth/oauthchooseaccount?response_type=code&access_type=offline&client_id=435398559701-9blpn76kukvrd6jhhj6vhdf3r0qg61um.apps.googleusercontent.com&redirect_uri=http%3A%2F%2Flocalhost%3A8000%2Fthirdparty%2Fgmail%2Foauth%2Fauth&state&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fgmail.send&approval_prompt=auto&service=lso&o2v=2&flowName=GeneralOAuthFlow';
                header('Location: '.$authUrl);
                die;
            }
        }
    }
}