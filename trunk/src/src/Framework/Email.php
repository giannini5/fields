<?php

namespace DAG\Framework;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// require 'vendor/autoload.php';

/**
 * Class Email
 *
 * @brief Send HTML Email
 */
class Email {
    public function __construct() {
    }

    public function send($subject, $body, $to, $to_friendly, $cc=null, $bcc=null) {
        /**
         * Send an email w/ optional CC and BCC
         *
         * @param string $subject
         * @param string $body
         * @param string $to
         * @param string $to_friendly
         * @param string | null $cc
         * @param string | null $bcc
         */
        $mail = new PHPMailer(true); // Passing `true` enables exceptions
        $mail->isSMTP();
        $mail->Host = EMAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_USER;
        $mail->Password = EMAIL_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port = EMAIL_PORT;

        // Sender and recipient settings
        $mail->setFrom(EMAIL_USER, EMAIL_NAME);
        $mail->addAddress($to, $to_friendly);

        // CC and BCC
        if ($cc) {
            $mail->addCC($cc);
        }
        if ($bcc) {
            $mail->addBCC($bcc);
        }

        // Email content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = 'This is an HTML email only';

        if(!$mail->send()) {
            throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
        }
    }
}
