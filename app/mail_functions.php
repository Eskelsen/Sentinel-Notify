<?php

# Mail Functions

	# ========================================================== Alterar conforme o usado no Unotify

define('PHPMAILER', __DIR__ . '/phpmailer/');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require PHPMAILER . 'src/Exception.php';
require PHPMAILER . 'src/PHPMailer.php';
require PHPMAILER . 'src/SMTP.php';

define('SMTP',	'smtp.hostinger.com');
define('EMAIL',	'contato@microframeworks.com');
define('PSWD',	'Dan2103Gi$');

function sendMail($email,$title,$html){

    $mail = new PHPMailer();

    //Server settings
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = SMTP;                                   //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = EMAIL;                                   //SMTP username
    $mail->Password   = PSWD;                                //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom(EMAIL, 'Microframeworks.com');
    // $mail->addAddress('eskelsen@yahoo.com', 'Eskelsen');     //Add a recipient
    $mail->addAddress($email);               //Name is optional
    // $mail->addReplyTo('info@example.com', 'Information');
    // $mail->addCC('cc@example.com');
    // $mail->addBCC('bcc@example.com');

    //Attachments
    // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = $title;
    $mail->CharSet = 'UTF-8';
    $mail->Body    = $html;
    // $mail->AltBody = $text;

    return $mail->send();
}
