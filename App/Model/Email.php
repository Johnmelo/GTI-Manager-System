<?php
namespace App\Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Sunra\PhpSimple\HtmlDomParser;

class Email {

    protected $mail;

    // Server settings
    const IS_HTML        = true;
    const IS_SMTP        = true;
    const SMTP_DEBUG     = false;
    const SMTP_AUTH      = true;
    const SMTP_SECURE    = 'ssl';
    const SMTP_HOST      = '';
    const PORT           = 465;
    const ERROR_MSG_LANG = 'pt';
    const CHARSET        = 'UTF-8';
    const PASSWORD       = '';
    const FROM_EMAIL     = '';
    const FROM_NAME      = '';

    // Defaults
    const ACCESS_GRANTED_NOTIFICATION_SUBJECT = 'Sua conta está pronta!';
    const ACCESS_REFUSED_NOTIFICATION_SUBJECT = 'Solicitação de conta rejeitada';
    const ACCESS_REQUEST_NOTIFICATION_SUBJECT = 'Solicitação de conta no GTI Chamados';



    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->mail->SMTPDebug   = self::SMTP_DEBUG;
        $this->mail->Host        = self::SMTP_HOST;
        $this->mail->SMTPAuth    = self::SMTP_AUTH;
        $this->mail->Username    = self::FROM_EMAIL;
        $this->mail->Password    = self::PASSWORD;
        $this->mail->SMTPSecure  = self::SMTP_SECURE;
        $this->mail->Port        = self::PORT;
        $this->mail->setLanguage = self::ERROR_MSG_LANG;
        $this->mail->CharSet     = self::CHARSET;
        $this->mail->isHTML(self::IS_HTML);
        if (self::IS_SMTP)
            $this->mail->isSMTP();
    }

    public function sendEmail($recipientEmail, $recipientName, $subject, $body, $altBody = "") {
        try {
            $this->mail->setFrom(self::FROM_EMAIL, self::FROM_NAME);
            $this->mail->addAddress($recipientEmail, $recipientName);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = $altBody;
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $this->mail->ErrorInfo;
            return false;
        }
    }

    public function accessRequestNotification($name, $lastname, $username, $email, $location, $registrationNumber) {
        try {
            // Get email template
            $email_header = file_get_contents('../App/View/emailcontroller/email_header.phtml');
            $email_body = file_get_contents('../App/View/emailcontroller/request_notification_email.phtml');
            $email_footer = file_get_contents('../App/View/emailcontroller/email_footer.phtml');
            $email_content = $email_header . $email_body . $email_footer;

            // Parse file to include the user request info
            $dom = HtmlDomParser::str_get_html($email_content);
            $full_name_field = $dom->find('span[id=full-name-field]', 0);
            $full_name_field->innertext = $name . " " . $lastname;
            $name_field = $dom->find('span[id=name-field]', 0);
            $name_field->innertext = $name;
            $lastname_field = $dom->find('span[id=lastname-field]', 0);
            $lastname_field->innertext = $lastname;
            $username_field = $dom->find('span[id=username-field]', 0);
            $username_field->innertext = $username;
            $email_field = $dom->find('span[id=email-field]', 0);
            $email_field->innertext = $email;
            $location_field = $dom->find('span[id=location-field]', 0);
            $location_field->innertext = $location;
            $registration_number_field = $dom->find('span[id=registration-number-field]', 0);
            $registration_number_field->innertext = $registrationNumber;
            $email_content = $dom->save();

            $this->sendEmail($email, $name." ".$lastname, self::ACCESS_REQUEST_NOTIFICATION_SUBJECT, $email_content);
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $this->mail->ErrorInfo;
            return false;
        }
    }

    public function requestGrantedNotification($name, $email) {
        try {
            // Get email template
            $email_header = file_get_contents('../App/View/emailcontroller/email_header.phtml');
            $email_body = file_get_contents('../App/View/emailcontroller/request_granted_notification_email.phtml');
            $email_footer = file_get_contents('../App/View/emailcontroller/email_footer.phtml');
            $email_content = $email_header . $email_body . $email_footer;

            // Parse file to include the user info
            $dom = HtmlDomParser::str_get_html($email_content);
            $full_name_field = $dom->find('span[id=full-name-field]', 0);
            $full_name_field->innertext = $name;
            $email_content = $dom->save();

            $this->sendEmail($email, $name, self::ACCESS_GRANTED_NOTIFICATION_SUBJECT, $email_content);
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $this->mail->ErrorInfo;
            return false;
        }
    }

    public function requestRefusedNotification($name, $email, $message) {
        try {
            // Get email template
            $email_header = file_get_contents('../App/View/emailcontroller/email_header.phtml');
            $email_body = file_get_contents('../App/View/emailcontroller/request_refused_notification_email.phtml');
            $email_footer = file_get_contents('../App/View/emailcontroller/email_footer.phtml');
            $email_content = $email_header . $email_body . $email_footer;

            // Parse file to include the user info
            $dom = HtmlDomParser::str_get_html($email_content);
            $full_name_field = $dom->find('span[id=full-name-field]', 0);
            $full_name_field->innertext = $name;
            $message_field = $dom->find('p[id=refusal-reason-field]', 0);
            $message_field->innertext = $message;
            $email_content = $dom->save();

            $this->sendEmail($email, $name, self::ACCESS_REFUSED_NOTIFICATION_SUBJECT, $email_content);
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $this->mail->ErrorInfo;
            return false;
        }
    }
}


?>
