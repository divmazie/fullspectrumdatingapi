<?php
/**
 * Created by PhpStorm.
 * User: davidamackenzie
 * Date: 9/18/19
 * Time: 5:48 PM
 */

class EmailSenderFactory {

    public static function Instance() {
        static $inst = null;
        if ($inst===null) {
            $inst = new EmailSenderFactory();
        }
        return $inst;
    }

    private $sendgrid, $fromAddres, $fromName;

    function __construct() {
        require("thirdparty/sendgrid-php/sendgrid-php.php");
        $configs = require("config.php");
        $this->sendgrid = new \SendGrid($configs['sendgrid_key']);
        $this->fromAddres = "noreply@humanforhuman.net";
        $this->fromName = "Robot For Human";
    }

    public function sendEmail($toAddress, $subject, $content) {
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($this->fromAddres, $this->fromName);
        $email->setSubject($subject);
        $email->addTo($toAddress);
        $email->addContent("text/html", $content);
        // $email->addContent("text/html", $content);
        // $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        try {
            $response = $this->sendgrid->send($email);
            error_log("Email send status: ".$response->statusCode());
            error_log(json_encode($response->headers()));
            error_log($response->body());
            if ($response->statusCode() == 202) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            error_log('Caught exception: '. $e->getMessage());
            return false;
        }
    }

    public function sendInviteEmail($signupEmail) {
        $subject = "Welcome to Human For Human alpha!";
        $invite_link = "http://humanforhuman.net/newuser/".str_replace('*','',$signupEmail->getValue('invite_code'));
        $content = file_get_contents(__DIR__ . '/email_templates/invite_email.html');
        $content = str_replace("{{invite_link}}", $invite_link, $content);
        return $this->sendEmail($signupEmail->getValue('email'), $subject, $content);
    }

    public function sendPasswordResetEmail($signupEmail) {
        $subject = "Human For Human password reset";
        $invite_link = "http://humanforhuman.net/newuser/".str_replace('*','',$signupEmail->getValue('invite_code'));
        $content = file_get_contents(__DIR__ . '/email_templates/reset_password_email.html');
        $content = str_replace("{{invite_link}}", $invite_link, $content);
        return $this->sendEmail($signupEmail->getValue('email'), $subject, $content);
    }

}