<?php

# Mail Functions

require_once WEB . 'vendor/autoload.php';

include PACKS . 'forms.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

define('SMTP',	'smtp.hostinger.com');
define('EMAIL',	'contato@microframeworks.com');
define('PSWD',	'Dan2103Gi$');

function sendMail($email,$title,$html,$others = false){
	
	$email = filterEmail($email);
	
	if (!validEmail($email)) {
		microlog('[Infra/Mail] validEmail fail: ' . $email);
		return false;
	}
	
	[$smtp, $port, $username, $useremail, $password, $userlabel] = [SMTP,465,EMAIL,EMAIL,PSWD,'Sentinel Notify'];
	
    $mail = new PHPMailer(true);

    //Server settings
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    // $mail->SMTPDebug = 4;
    $mail->isSMTP();
    $mail->Host       = $smtp;
    $mail->SMTPAuth   = true;
    $mail->Username   = $username;
    $mail->Password   = $password;

    $mail->SMTPSecure = ($port==465) ? PHPMailer::ENCRYPTION_SMTPS : true;
	
    $mail->Port = $port;

    $mail->setFrom($useremail, $userlabel);
	
	if (empty($others['name'])) {
		$mail->addAddress($email);
	} else {
		include_once PACKS . 'stringfy.php';
		$mail->addAddress($email, formatName($others['name']));
	}
	
    $mail->addReplyTo($useremail, $userlabel);

	if (!empty($others['attachment']) AND is_array($others['attachment'])) {
		[$filepath, $filename] = array_values($others['attachment']);
		$mail->addAttachment($filepath, $filename); // Name is optional
	}
	
	if (!empty($others['unsubscribe'])) {
		$mail->addCustomHeader('List-Unsubscribe', '<' . $useremail . '>, <' . $others['unsubscribe'] . '>');
	}
	
	# Unotify Mark
	$html = unotifyMark($html);

	# Content
    $mail->isHTML(true);
    $mail->Subject = $title;
    $mail->CharSet = 'UTF-8';
    $mail->Body    = $html;
	
	try {
		$sent = $mail->send();
		microlog('[Infra/Mail] E-mail ' . $title. ' enviado para ' . $email . ' com sucesso!');
		return $sent;
	} catch (Exception $e) {
		microlog('[Infra/Mail] Erro ao enviar o e-mail via: ' . $useremail . '. Retorno: ' . $e->getMessage());
		return false;
	}
}

function unotifyMark($html){
	$html = (stripos($html,'<!DOCTYPE html>')!==false) ? $html : '<!DOCTYPE html>' . "\n" . $html;
	$search = '</body>';
	$replace = '
		<div style="background-color: #f7f7f7; padding: 20px; text-align: center; font-family: Arial, sans-serif; color: #666;">
			<div style="display: inline-block; vertical-align: middle;">
				<a style="margin: 0; color: #777; text-decoration: none" href="https://unotify.mfwks.com/?utm_source=via_unotify" class="app-brand-link">
					<span style="vertical-align: middle;">Entregue via Sentinel Notify</span>
				</a>
			</div>
		</div>
	</body>';
		
	$marked = replaceLastOcurrence($html, $search, $replace);

	return ($marked) ? $marked : $html . str_replace($search,'',$replace);
}

function replaceLastOcurrence($string, $search, $replace){
    $posicao = strripos($string, $search);
    if($posicao !== false) {
        return substr_replace($string, $replace, $posicao, strlen($search));
    }
	return false;
}
