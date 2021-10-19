<?php

namespace Netric\Mail;

use Aereus\Config\Config;
use Netric\Log\LogInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Service used for sending email messages
 */
class SenderService
{
    /**
     * Log
     *
     * @var Log
     */
    private LogInterface $log;

    private Config $mailConfig;
    /**
     * Construct the sender service
     *
     * @param Log $log
     * @param Confit $mailConfig Is the [mail] section of our config
     */
    public function __construct(LogInterface $log, Config $mailConfig)
    {
        $this->log = $log;
        $this->mailConfig = $mailConfig;
    }

    /**
     * Send a single email with raw data
     *
     * NOTE: Under the hood this uses PHPs mail() function so make sure we
     * have sendmail and/or the right SMTP settings in php.ini
     *
     * @param string $toAddress The address (or addresses) to send to
     * @param string $subject Message subject
     * @param string $body The raw body of the message
     * @param array $headers Any additional headers
     * @return bool true if successs, false on failure with details written to the log
     */
    public function send(string $toAddress, string $subject, string $body, array $headers = []): bool
    {
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        $from = isset($headers['from']) ? $headers['from'] : $this->mailConfig->noreply;

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $this->mailConfig->server;                     //Set the SMTP server to send through
            $mail->SMTPAuth   = false;                                   //Enable SMTP authentication
            // $mail->Username   = 'user@example.com';                     //SMTP username
            // $mail->Password   = 'secret';                               //SMTP password
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = $this->mailConfig->port;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($from);
            $mail->addAddress($toAddress, 'Test User');     //Add a recipient
            //$mail->addAddress('ellen@example.com');               //Name is optional
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            // //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            //$mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            return $mail->send();
        } catch (PHPMailerException $e) {
            $this->log->error("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Handle sending a bulk email message
     * @return bool true on success, false on failure with $this->getLastError set
     */
    public function sendFromTemplate(string $contactId, string $emailTemplateId)
    {
        $this->log->error("sendFromTemplate was called and it should not have been");
        return false;
    }
}
