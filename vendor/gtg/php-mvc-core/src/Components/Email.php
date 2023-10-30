<?php

namespace GTG\MVC\Components;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use GTG\MVC\Application;
use GTG\MVC\Exceptions\AppException;
use stdClass;

class Email 
{
    /** @var PHPMailer */
    private $mail;

    /** @var stdClass */
    private $data;

    /** @var AppException */
    private $error;

    public function __construct() 
    {
        $this->mail = new PHPMailer(true);
        $this->data = new stdClass();

        $this->mail->isSMTP();
        $this->mail->isHTML();
        $this->mail->setLanguage('br');

        $this->mail->SMTPAuth = true;
        $this->mail->SMTPSecure = 'ssl';
        $this->mail->CharSet = 'utf-8';

        $this->mail->Host = Application::$SMTP_INFO['host'];
        $this->mail->Port = Application::$SMTP_INFO['port'];
        $this->mail->Username = Application::$SMTP_INFO['username'];
        $this->mail->Password = Application::$SMTP_INFO['password'];
    }

    public function add(
        string $subject, 
        string $body, 
        string $recipientName, 
        string $recipientEmail
    ): self 
    {
        $this->data->subject = $subject;
        $this->data->body = $body;
        $this->data->recipient_name = $recipientName;
        $this->data->recipient_email = $recipientEmail;

        return $this;
    }

    public function attach(string $filePath, string $fileName): self 
    {
        $this->data->attach[$filePath] = $fileName;
        return $this;
    }

    public function bcc(string $email): self 
    {
        $this->data->bcc = $email;
        return $this;
    }

    public function send(?string $fromName = null, ?string $fromEmail = null): bool 
    {
        try {
            $fromName = $fromName ?? Application::$SMTP_INFO['name'];
            $fromEmail = $fromEmail ?? Application::$SMTP_INFO['email'];
            $this->mail->Subject = $this->data->subject;
            $this->mail->msgHTML($this->data->body);
            $this->mail->addAddress($this->data->recipient_email, $this->data->recipient_name);
            $this->mail->setFrom($fromEmail, $fromName);

            if(!empty($this->data->bcc)) {
                $this->mail->addBCC($this->data->bcc);
            }

            if(!empty($this->data->attach)) {
                foreach($this->data->attach as $path => $name) {
                    $this->mail->addAttachment($path, $name);
                }
            }

            $this->mail->send();
            return true;
        } catch(Exception $e) {
            $this->error = new AppException($e->getMessage());
            return false;
        }
    }

    public function error(): ?AppException 
    {
        return $this->error;
    }
}