<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;
 
 
require_once 'vendor/autoload.php';
require_once 'class-db.php';


class Mail {

    private $mail;
    private $email;
    private $clientId;
    private $clientSecret;
    private $provider;
    private $refreshToken;

    public function __construct()
    {
        $datosEmail = $this->datosEmail();
        // echo "<pre>";
        // print_r($datosEmail);
        // echo "</pre>";
        $this->mail = new PHPMailer();
        $this->mail->isSMTP();
        $this->mail->SMTPAuth = true;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->isHTML(true);
        $this->mail->CharSet = 'UTF-8';
        foreach ($datosEmail as $value) {
            $this->mail->Host = $value['Host'];
            $this->mail->Port = $value['Port'];
            $this->mail->AuthType = $value['AuthType'];

            $this->email = $value['email'];
            $this->clientId = $value['clientId'];
            $this->clientSecret = $value['clientSecret'];

            
        }
        $this->provider = new Google(
            [
                'clientId' => $this->clientId,
                'clientSecret' => $this->clientSecret,
            ]
        );
        $db = new DB();
        $this->refreshToken = $db->get_refersh_token();
        $this->mail->setOAuth(
            new OAuth(
                [
                    'provider' => $this->provider,
                    'clientId' => $this->clientId,
                    'clientSecret' => $this->clientSecret,
                    'refreshToken' => $this->refreshToken,
                    'userName' => $this->email,
                ]
            )
        );
        $this->mail->setFrom($this->email, 'VirtualENV');
    }

    // create private function datosEmail()
    private function datosEmail()
    {
        $dirname = dirname(__FILE__);
        $json = file_get_contents($dirname . '/config.json');
        $datosEmail = json_decode($json, true);
        return ["datosPHPMailer" => $datosEmail['datosPHPMailer']];
    }

    // crear funcion publica para enviar correo 
    public function sendMail($email, $subject, $body)
    {
        $this->mail->addAddress($email);
        $this->mail->Subject = $subject;
        $this->mail->msgHTML($body);
        if (!$this->mail->send()) {
            // echo 'Mailer Error: ' . $this->mail->ErrorInfo;
            return false;
        } else {
            // echo 'Message has been sent';
            return true;
        }
    }
}
